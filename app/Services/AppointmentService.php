<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\AppointmentStatusHistory;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Service;
use App\Models\TimeSlot;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class AppointmentService
{
    /**
     * Thời hạn hủy lịch hẹn tối thiểu trước giờ khám (tính bằng giờ)
     */
    const CANCELLATION_DEADLINE_HOURS = 2;

    /**
     * Thời gian giữ chỗ tối đa cho lịch hẹn thanh toán trực tuyến VNPay chưa hoàn tất (tính bằng phút)
     */
    const VNPAY_PAYMENT_TTL_MINUTES = 15;

    /**
     * Tự động giải phóng các lịch hẹn giữ chỗ trực tuyến (VNPay) quá hạn chưa thanh toán (Ghost Booking Cleanup)
     */
    public function releaseExpiredUnpaidOnlineBookings(?int $timeSlotId = null, int $ttlMinutes = self::VNPAY_PAYMENT_TTL_MINUTES): int
    {
        $cutoff = now()->subMinutes($ttlMinutes);

        $query = Appointment::where('status', Appointment::STATUS_PENDING)
            ->where('payment_status', Appointment::PAYMENT_FAILED)
            ->whereNotNull('transaction_ref')
            ->where('created_at', '<=', $cutoff);

        if ($timeSlotId) {
            $query->where('time_slot_id', $timeSlotId);
        }

        $expiredAppointments = $query->get();
        $releasedCount = 0;

        foreach ($expiredAppointments as $expired) {
            DB::transaction(function () use ($expired, $ttlMinutes, &$releasedCount) {
                $locked = Appointment::where('id', $expired->id)->lockForUpdate()->first();
                if (!$locked || $locked->status !== Appointment::STATUS_PENDING || $locked->payment_status === Appointment::PAYMENT_SUCCESS) {
                    return;
                }

                if ($locked->time_slot_id) {
                    $slot = TimeSlot::where('id', $locked->time_slot_id)->lockForUpdate()->first();
                    if ($slot) {
                        $slot->booked_count = max(0, $slot->booked_count - 1);
                        if ($slot->status === TimeSlot::STATUS_LOCKED && $slot->booked_count < $slot->capacity) {
                            $slot->status = TimeSlot::STATUS_AVAILABLE;
                        }
                        $slot->save();
                    }
                }

                $oldStatus = $locked->status;
                $reason = "Tự động giải phóng chỗ do quá hạn thanh toán trực tuyến VNPay ({$ttlMinutes} phút)";
                $locked->status = Appointment::STATUS_CANCELLED;
                $locked->cancellation_reason = $reason;
                $locked->cancelled_at = now();
                $locked->save();

                AppointmentStatusHistory::create([
                    'appointment_id' => $locked->id,
                    'from_status' => $oldStatus,
                    'to_status' => Appointment::STATUS_CANCELLED,
                    'changed_by_user_id' => null,
                    'action_role' => 'system',
                    'note' => $reason,
                ]);

                $releasedCount++;
            });
        }

        return $releasedCount;
    }

    /**
     * Kiểm tra thông minh chống đặt trùng lịch theo đúng đối tượng khám (Bản thân vs Người thân)
     * và chống trùng khung giờ giữa các chuyên khoa khác nhau.
     */
    protected function assertNoTimeConflictForExaminee(
        int $patientId,
        TimeSlot $targetSlot,
        string $bookingFor = 'self',
        ?string $relativeName = null,
        ?int $excludeAppointmentId = null
    ): void {
        $isRelative = ($bookingFor === 'relative') && !empty(trim((string) $relativeName));
        $normalizedTargetRel = $isRelative ? Appointment::normalizePersonName($relativeName) : '';

        $candidates = Appointment::with(['timeSlot', 'doctor.user'])
            ->where('patient_id', $patientId)
            ->whereIn('status', [
                Appointment::STATUS_PENDING,
                Appointment::STATUS_CONFIRMED,
                Appointment::STATUS_CHECKED_IN,
            ])
            ->when($excludeAppointmentId, fn ($q) => $q->where('id', '!=', $excludeAppointmentId))
            ->where(function ($q) use ($targetSlot) {
                $slotDate = $targetSlot->date instanceof Carbon ? $targetSlot->date->toDateString() : (string) $targetSlot->date;
                $q->where('time_slot_id', $targetSlot->id)
                    ->orWhereHas('timeSlot', function ($sq) use ($slotDate, $targetSlot) {
                        $sq->whereDate('date', $slotDate)
                            ->where('start_time', '<', $targetSlot->end_time)
                            ->where('end_time', '>', $targetSlot->start_time);
                    });
            })
            ->get();

        foreach ($candidates as $existing) {
            $existingIsRelative = ($existing->booking_for === 'relative') && !empty(trim((string) $existing->relative_name));

            if ($isRelative && $existingIsRelative) {
                if (Appointment::normalizePersonName($existing->relative_name) === $normalizedTargetRel) {
                    throw ValidationException::withMessages([
                        'time_slot_id' => "Người thân \"{$relativeName}\" đã có lịch hẹn (#{$existing->appointment_code}) trong khung giờ {$targetSlot->formatted_time}. Vui lòng không đặt trùng giờ cho cùng một người khám.",
                    ]);
                }
            } elseif (!$isRelative && !$existingIsRelative) {
                $sameSlot = (int) $existing->time_slot_id === (int) $targetSlot->id;
                $msg = $sameSlot
                    ? 'Bạn đã có một lịch hẹn trong khung giờ này. Vui lòng không đặt trùng.'
                    : "Bạn đã có lịch hẹn (#{$existing->appointment_code}) trùng khung giờ {$targetSlot->formatted_time} tại phòng khám. Một người không thể khám ở 2 nơi cùng lúc.";

                throw ValidationException::withMessages([
                    'time_slot_id' => $msg,
                ]);
            }
        }
    }

    /**
     * Tạo lịch hẹn khám bệnh mới với cơ chế Transaction + Row-Level Locking chống trùng lịch tuyệt đối.
     *
     * @param array $data Dữ liệu đầu vào
     * @param User $authUser Người dùng đã đăng nhập (bệnh nhân)
     * @return Appointment Lịch hẹn được tạo
     * @throws ValidationException
     */
    public function bookAppointment(array $data, User $authUser): Appointment
    {
        // 1. Xác thực hồ sơ bệnh nhân từ tài khoản đã đăng nhập (Không tin tưởng patient_id từ form)
        $patient = $authUser->patient;
        if (!$patient) {
            $patient = Patient::firstOrCreate(
                ['user_id' => $authUser->id],
                [
                    'name' => $authUser->name,
                    'email' => $authUser->email,
                    'phone' => $authUser->phone ?? null,
                    'birthdate' => $authUser->birthdate ?? null,
                    'gender' => $authUser->gender ?? null,
                    'address' => $authUser->address ?? null,
                ]
            );
        }

        // 2. Kiểm tra Idempotency key nếu có (Chống nhấn đúp hoặc gửi yêu cầu lặp)
        if (!empty($data['idempotency_key'])) {
            $existing = Appointment::where('idempotency_key', $data['idempotency_key'])->first();
            if ($existing) {
                return $existing;
            }
        }

        // 3. Kiểm tra bác sĩ và dịch vụ
        $doctorId = (int) $data['doctor_id'];
        $doctor = Doctor::with(['user', 'department'])->findOrFail($doctorId);
        if (!$doctor->is_active) {
            throw ValidationException::withMessages([
                'doctor_id' => 'Bác sĩ hiện đang tạm ngưng tiếp nhận bệnh nhân.',
            ]);
        }

        $serviceId = (int) $data['service_id'];
        $service = Service::findOrFail($serviceId);

        if ($service->department_id && $doctor->department_id && (int) $service->department_id !== (int) $doctor->department_id) {
            throw ValidationException::withMessages([
                'service_id' => 'Dịch vụ khám đã chọn không thuộc chuyên khoa của bác sĩ.',
            ]);
        }

        // 4. Tự động dọn dẹp các giao dịch VNPay treo quá hạn (15 phút) trên khung giờ này trước khi khóa hàng
        if (!empty($data['time_slot_id'])) {
            $this->releaseExpiredUnpaidOnlineBookings((int) $data['time_slot_id']);
        }

        // 5. Thực thi Transaction với số lần thử lại nếu deadlock (retry on deadlock)
        return DB::transaction(function () use ($data, $authUser, $patient, $doctor, $service) {
            $timeSlot = null;
            $appointmentDate = null;
            $shiftName = null;

            // TH1: Đặt lịch theo TimeSlot cụ thể (chuẩn mới F04/F05)
            if (!empty($data['time_slot_id'])) {
                $timeSlotId = (int) $data['time_slot_id'];

                // 🔒 Khóa bản ghi TimeSlot bằng lockForUpdate()
                $timeSlot = TimeSlot::where('id', $timeSlotId)->lockForUpdate()->first();

                if (!$timeSlot) {
                    throw ValidationException::withMessages([
                        'time_slot_id' => 'Khung giờ khám không tồn tại.',
                    ]);
                }

                if ($timeSlot->doctor_id !== $doctor->id) {
                    throw ValidationException::withMessages([
                        'time_slot_id' => 'Khung giờ khám không thuộc bác sĩ đã chọn.',
                    ]);
                }

                // BR01: Kiểm tra thời điểm bắt đầu khung giờ không ở quá khứ
                if ($timeSlot->start_date_time->isPast()) {
                    throw ValidationException::withMessages([
                        'time_slot_id' => 'Khung giờ khám này đã qua thời gian tiếp nhận. Vui lòng chọn khung giờ khác.',
                    ]);
                }

                // BR02: Kiểm tra trạng thái khung giờ
                if (!$timeSlot->is_active || $timeSlot->status !== TimeSlot::STATUS_AVAILABLE) {
                    throw ValidationException::withMessages([
                        'time_slot_id' => 'Khung giờ khám hiện đang bị khóa hoặc ngừng tiếp nhận.',
                    ]);
                }

                // Đếm toàn bộ lịch hẹn hợp lệ (pending, confirmed, checked_in, completed) đã chiếm chỗ trong khung giờ này
                $currentActiveCount = Appointment::where('time_slot_id', $timeSlot->id)
                    ->whereIn('status', Appointment::ACTIVE_SLOT_STATUSES)
                    ->lockForUpdate()
                    ->count();

                // BR03, BR04: Kiểm tra dung lượng (capacity) bên trong giao dịch sau khi khóa hàng
                if ($currentActiveCount >= $timeSlot->capacity || $timeSlot->booked_count >= $timeSlot->capacity) {
                    throw ValidationException::withMessages([
                        'time_slot_id' => "Khung giờ {$timeSlot->formatted_time} ngày " . Carbon::parse($timeSlot->date)->format('d/m/Y') . " đã đủ số lượng tiếp nhận ({$timeSlot->capacity}/{$timeSlot->capacity}).",
                    ]);
                }

                // Kiểm tra trùng lịch thông minh: phân biệt khám Bản thân vs Người thân & chặn trùng giờ giữa các chuyên khoa
                $this->assertNoTimeConflictForExaminee(
                    $patient->id,
                    $timeSlot,
                    (string) ($data['booking_for'] ?? 'self'),
                    $data['relative_name'] ?? null
                );

                // Cập nhật booked_count cho TimeSlot
                $timeSlot->booked_count = $currentActiveCount + 1;
                if ($timeSlot->booked_count >= $timeSlot->capacity) {
                    $timeSlot->status = TimeSlot::STATUS_LOCKED; // Tự động khóa khi đủ số lượng
                }
                $timeSlot->save();

                $appointmentDate = $timeSlot->date->format('Y-m-d') . ' ' . $timeSlot->start_time;
                $startHour = (int) substr($timeSlot->start_time, 0, 2);
                $shiftName = $startHour < 12 ? 'Ca sáng (07:30 - 11:30)' : 'Ca chiều (13:00 - 17:00)';
            } else {
                // TH2: Tương thích ngược với ca sáng / chiều (legacy shift fallback)
                $reqDate = Carbon::parse($data['appointment_date'] ?? $data['date'])->toDateString();
                $shiftName = $data['medical_examination'] ?? (($data['appointment_time'] ?? '') === 'morning' ? 'Ca sáng (07:30 - 11:30)' : 'Ca chiều (13:00 - 17:00)');

                // Khóa cấp độ bác sĩ + ngày để chống race condition
                $doctorRow = Doctor::where('id', $doctor->id)->lockForUpdate()->first();

                $existingShiftCount = Appointment::where('doctor_id', $doctor->id)
                    ->whereDate('appointment_date', $reqDate)
                    ->where('medical_examination', $shiftName)
                    ->whereIn('status', Appointment::ACTIVE_SLOT_STATUSES)
                    ->lockForUpdate()
                    ->count();

                $maxShiftCapacity = 25;
                if ($existingShiftCount >= $maxShiftCapacity) {
                    throw ValidationException::withMessages([
                        'appointment_date' => "Buổi {$shiftName} ngày " . Carbon::parse($reqDate)->format('d/m/Y') . " đã đủ {$maxShiftCapacity} ca khám.",
                    ]);
                }

                $appointmentDate = $reqDate . (strpos($shiftName, 'chiều') !== false ? ' 13:00:00' : ' 07:30:00');
            }

            // 5. Tạo bản ghi lịch hẹn
            $price = $service->price ?? 0;
            $insertData = [
                'appointment_code' => 'TEMP-' . uniqid(),
                'idempotency_key' => $data['idempotency_key'] ?? null,
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'service_id' => $service->id,
                'time_slot_id' => $timeSlot ? $timeSlot->id : null,
                'appointment_date' => $appointmentDate,
                'medical_examination' => $shiftName,
                'status' => Appointment::STATUS_PENDING,
                'payment_status' => $data['payment_status'] ?? Appointment::PAYMENT_FAILED,
                'note' => $data['note'] ?? null,
                'booking_for' => $data['booking_for'] ?? 'self',
                'relative_name' => ($data['booking_for'] ?? 'self') === 'relative' ? ($data['relative_name'] ?? null) : null,
                'relative_dob' => ($data['booking_for'] ?? 'self') === 'relative' ? ($data['relative_dob'] ?? null) : null,
                'relative_gender' => ($data['booking_for'] ?? 'self') === 'relative' ? ($data['relative_gender'] ?? null) : null,
                'relative_relationship' => ($data['booking_for'] ?? 'self') === 'relative' ? ($data['relative_relationship'] ?? null) : null,
                'total' => $price,
                'total_amount' => $price,
            ];

            $appointment = Appointment::create($insertData);

            // Cập nhật mã lịch hẹn chuẩn
            $appointment->appointment_code = Appointment::generateCode($appointment->id);
            $appointment->save();

            // 6. Ghi nhật ký kiểm vết (Audit trail)
            $bookingNote = 'Bệnh nhân tạo lịch hẹn mới';
            if (($data['booking_for'] ?? 'self') === 'relative' && !empty($data['relative_name'])) {
                $bookingNote .= " (Đặt hộ: {$data['relative_name']} - {$data['relative_relationship']})";
            }

            AppointmentStatusHistory::create([
                'appointment_id' => $appointment->id,
                'from_status' => null,
                'to_status' => Appointment::STATUS_PENDING,
                'changed_by_user_id' => $authUser->id,
                'action_role' => 'patient',
                'note' => $bookingNote,
            ]);

            return $appointment;
        }, 5); // Thử lại tối đa 5 lần nếu gặp Deadlock của MySQL InnoDB
    }

    /**
     * Hủy lịch hẹn với kiểm tra quyền, thời hạn và cập nhật số chỗ trống an toàn.
     *
     * @param Appointment $appointment
     * @param User $user
     * @param string|null $reason
     * @return Appointment
     * @throws ValidationException
     */
    public function cancelAppointment(Appointment $appointment, User $user, ?string $reason = null): Appointment
    {
        // 1. Kiểm tra quyền sở hữu (BR05)
        $isAdmin = $user->hasRole('admin');
        $isDoctor = $user->hasRole('doctor') && $user->doctor && $user->doctor->id === $appointment->doctor_id;
        $isOwnerPatient = $user->patient && $user->patient->id === $appointment->patient_id;

        if (!$isAdmin && !$isDoctor && !$isOwnerPatient) {
            throw ValidationException::withMessages([
                'authorization' => 'Bạn không có quyền thực hiện thao tác hủy trên lịch hẹn này.',
            ]);
        }

        // 2. Bệnh nhân hủy phải tuân thủ điều kiện thời hạn (BR06)
        if ($isOwnerPatient && !$isAdmin) {
            $check = $appointment->canBeCancelledByPatient(self::CANCELLATION_DEADLINE_HOURS);
            if (!$check['can']) {
                throw ValidationException::withMessages([
                    'status' => $check['reason'],
                ]);
            }
        } elseif (!in_array($appointment->status, [Appointment::STATUS_PENDING, Appointment::STATUS_CONFIRMED])) {
            throw ValidationException::withMessages([
                'status' => 'Lịch hẹn đã kết thúc hoặc đã hủy, không thể thay đổi.',
            ]);
        }

        // 3. Thực thi transaction an toàn
        return DB::transaction(function () use ($appointment, $user, $reason, $isAdmin, $isDoctor) {
            // Khóa bản ghi lịch hẹn
            $lockedAppt = Appointment::where('id', $appointment->id)->lockForUpdate()->firstOrFail();
            $oldStatus = $lockedAppt->status;

            // Nếu lịch hẹn có gắn khung giờ TimeSlot, giải phóng chỗ
            if ($lockedAppt->time_slot_id) {
                $slot = TimeSlot::where('id', $lockedAppt->time_slot_id)->lockForUpdate()->first();
                if ($slot) {
                    $slot->booked_count = max(0, $slot->booked_count - 1);
                    // Nếu slot từng bị locked do đủ chỗ, nay mở lại available
                    if ($slot->status === TimeSlot::STATUS_LOCKED && $slot->booked_count < $slot->capacity) {
                        $slot->status = TimeSlot::STATUS_AVAILABLE;
                    }
                    $slot->save();
                }
            }

            // Xác định vai trò tác nhân
            $actionRole = 'patient';
            if ($isAdmin) {
                $actionRole = 'admin';
            } elseif ($isDoctor) {
                $actionRole = 'doctor';
            }

            // Xử lý trạng thái hoàn tiền (Phân biệt rõ Hủy lịch và Hoàn tiền)
            $refundStatus = Appointment::REFUND_NONE;
            $refundNote = null;
            if ($lockedAppt->payment_status === Appointment::PAYMENT_SUCCESS) {
                $refundStatus = Appointment::REFUND_PENDING;
                $refundNote = 'Lịch hẹn đã thanh toán trước khi hủy. Yêu cầu chuyển bộ phận kế toán xét duyệt hoàn tiền.';
            }

            $lockedAppt->status = Appointment::STATUS_CANCELLED;
            $lockedAppt->cancellation_reason = $reason ?: 'Người dùng yêu cầu hủy lịch';
            $lockedAppt->cancelled_at = now();
            $lockedAppt->cancelled_by = $user->id;
            $lockedAppt->refund_status = $refundStatus;
            if ($refundNote) {
                $lockedAppt->refund_note = $refundNote;
            }
            $lockedAppt->save();

            // Ghi nhật ký kiểm vết
            AppointmentStatusHistory::create([
                'appointment_id' => $lockedAppt->id,
                'from_status' => $oldStatus,
                'to_status' => Appointment::STATUS_CANCELLED,
                'changed_by_user_id' => $user->id,
                'action_role' => $actionRole,
                'note' => $reason ?: 'Hủy lịch hẹn',
            ]);

            return $lockedAppt;
        }, 5);
    }

    /**
     * Cập nhật trạng thái lịch hẹn bởi Admin hoặc Bác sĩ (BR09)
     *
     * @param Appointment $appointment
     * @param string $newStatus
     * @param User $user
     * @param string|null $note
     * @return Appointment
     * @throws ValidationException
     */
    public function updateStatus(Appointment $appointment, string $newStatus, User $user, ?string $note = null): Appointment
    {
        $validStatuses = [
            Appointment::STATUS_PENDING,
            Appointment::STATUS_CONFIRMED,
            Appointment::STATUS_CHECKED_IN,
            Appointment::STATUS_COMPLETED,
            Appointment::STATUS_CANCELLED,
            Appointment::STATUS_REJECTED,
            Appointment::STATUS_NO_SHOW,
        ];

        if (!in_array($newStatus, $validStatuses, true)) {
            throw ValidationException::withMessages([
                'status' => 'Trạng thái không hợp lệ.',
            ]);
        }

        return DB::transaction(function () use ($appointment, $newStatus, $user, $note) {
            $lockedAppt = Appointment::where('id', $appointment->id)->lockForUpdate()->firstOrFail();
            $oldStatus = $lockedAppt->status;

            if ($oldStatus === $newStatus) {
                return $lockedAppt;
            }

            // State Machine Guard: Không cho phép nhảy trực tiếp từ trạng thái Đã hủy / Từ chối / Vắng mặt sang Đã tiếp đón hoặc Hoàn thành
            $terminalClosedStatuses = [
                Appointment::STATUS_CANCELLED,
                Appointment::STATUS_REJECTED,
                Appointment::STATUS_NO_SHOW,
            ];
            if (in_array($oldStatus, $terminalClosedStatuses, true) && in_array($newStatus, [Appointment::STATUS_CHECKED_IN, Appointment::STATUS_COMPLETED], true)) {
                throw ValidationException::withMessages([
                    'status' => 'Không thể chuyển trực tiếp lịch hẹn đã hủy/từ chối/vắng mặt sang trạng thái Đã tiếp đón hoặc Hoàn thành. Vui lòng chuyển về Đã xác nhận trước.',
                ]);
            }

            // Nếu trạng thái mới là hủy, từ chối hoặc vắng mặt (no_show), giải phóng số chỗ của TimeSlot
            if (in_array($newStatus, $terminalClosedStatuses, true)) {
                if ($lockedAppt->time_slot_id && in_array($oldStatus, [Appointment::STATUS_PENDING, Appointment::STATUS_CONFIRMED, Appointment::STATUS_CHECKED_IN], true)) {
                    $slot = TimeSlot::where('id', $lockedAppt->time_slot_id)->lockForUpdate()->first();
                    if ($slot) {
                        $slot->booked_count = max(0, $slot->booked_count - 1);
                        if ($slot->status === TimeSlot::STATUS_LOCKED && $slot->booked_count < $slot->capacity) {
                            $slot->status = TimeSlot::STATUS_AVAILABLE;
                        }
                        $slot->save();
                    }
                }
            }

            // Nếu khôi phục từ trạng thái đóng (cancelled/rejected/no_show) sang trạng thái hoạt động (pending/confirmed), kiểm tra và cộng lại slot
            if (in_array($oldStatus, $terminalClosedStatuses, true) && in_array($newStatus, [Appointment::STATUS_PENDING, Appointment::STATUS_CONFIRMED], true)) {
                if ($lockedAppt->time_slot_id) {
                    $slot = TimeSlot::where('id', $lockedAppt->time_slot_id)->lockForUpdate()->first();
                    if ($slot) {
                        if ($slot->booked_count >= $slot->capacity) {
                            throw ValidationException::withMessages([
                                'status' => 'Khung giờ của lịch hẹn này hiện đã đầy, không thể khôi phục lịch hẹn đã hủy.',
                            ]);
                        }
                        $slot->booked_count += 1;
                        if ($slot->booked_count >= $slot->capacity) {
                            $slot->status = TimeSlot::STATUS_LOCKED;
                        }
                        $slot->save();
                    }
                }
            }

            // Quản lý tồn kho thuốc khi chuyển sang hoặc rời khỏi trạng thái completed (gộp theo medicine_id & sắp xếp tăng dần chống deadlock)
            $record = $lockedAppt->medicalRecord;
            if ($record) {
                $groupedItems = $record->prescriptionItems()->get()
                    ->filter(fn ($row) => !empty($row->medicine_id) && $row->quantity > 0)
                    ->groupBy('medicine_id')
                    ->map(fn ($rows) => (int) $rows->sum('quantity'))
                    ->sortKeys();

                if ($newStatus === Appointment::STATUS_COMPLETED && $oldStatus !== Appointment::STATUS_COMPLETED) {
                    foreach ($groupedItems as $medId => $totalQty) {
                        $med = \App\Models\Medicine::where('id', $medId)->lockForUpdate()->first();
                        if ($med) {
                            if ($med->stock < $totalQty) {
                                throw ValidationException::withMessages([
                                    'status' => "Thuốc \"{$med->name}\" trong đơn chỉ còn {$med->stock} đơn vị (cần {$totalQty}).",
                                ]);
                            }
                            $med->decrement('stock', $totalQty);
                        }
                    }
                } elseif ($oldStatus === Appointment::STATUS_COMPLETED && $newStatus !== Appointment::STATUS_COMPLETED) {
                    foreach ($groupedItems as $medId => $totalQty) {
                        $med = \App\Models\Medicine::where('id', $medId)->lockForUpdate()->first();
                        if ($med && $totalQty > 0) {
                            $med->increment('stock', $totalQty);
                        }
                    }
                }
            }

            $actionRole = $user->role ? strtolower(trim($user->role->name)) : 'admin';

            $lockedAppt->status = $newStatus;
            if (in_array($newStatus, $terminalClosedStatuses, true)) {
                $lockedAppt->cancelled_at = now();
                $lockedAppt->cancelled_by = $user->id;
                $lockedAppt->cancellation_reason = $note ?: ($newStatus === Appointment::STATUS_NO_SHOW ? 'Bệnh nhân vắng mặt quá giờ hẹn (No-Show)' : null);

                if ($newStatus !== Appointment::STATUS_NO_SHOW && in_array($lockedAppt->payment_status, [Appointment::PAYMENT_SUCCESS, 'paid']) && ($lockedAppt->refund_status ?? Appointment::REFUND_NONE) === Appointment::REFUND_NONE) {
                    $lockedAppt->refund_status = Appointment::REFUND_PENDING;
                    $lockedAppt->refund_note = 'Lịch hẹn đã thanh toán trước khi hủy. Chờ bộ phận kế toán xác nhận hoàn tiền.';
                }
            }
            if ($newStatus === Appointment::STATUS_CHECKED_IN) {
                $lockedAppt->checked_in_at = now();
            }
            $lockedAppt->save();

            // Ghi audit history
            AppointmentStatusHistory::create([
                'appointment_id' => $lockedAppt->id,
                'from_status' => $oldStatus,
                'to_status' => $newStatus,
                'changed_by_user_id' => $user->id,
                'action_role' => $actionRole,
                'note' => $note ?: "Chuyển trạng thái từ {$oldStatus} sang {$newStatus}",
            ]);

            return $lockedAppt;
        }, 5);
    }

    /**
     * Xác nhận đã hoàn tiền viện phí cho lịch hẹn đã thanh toán (Admin / Kế toán)
     *
     * @param Appointment $appointment
     * @param User $user
     * @param string|null $refundNote
     * @param float|null $refundAmount
     * @return Appointment
     * @throws ValidationException
     */
    public function confirmRefund(Appointment $appointment, User $user, ?string $refundNote = null, ?float $refundAmount = null): Appointment
    {
        return DB::transaction(function () use ($appointment, $user, $refundNote, $refundAmount) {
            $lockedAppt = Appointment::where('id', $appointment->id)->lockForUpdate()->firstOrFail();

            if (!in_array($lockedAppt->payment_status, [Appointment::PAYMENT_SUCCESS, 'paid'])) {
                throw ValidationException::withMessages([
                    'payment_status' => 'Lịch hẹn này chưa thu viện phí nên không thể thực hiện hoàn tiền.',
                ]);
            }

            if ($lockedAppt->refund_status === Appointment::REFUND_REFUNDED) {
                throw ValidationException::withMessages([
                    'refund_status' => 'Lịch hẹn này đã được xác nhận hoàn tiền trước đó.',
                ]);
            }

            $amount = $refundAmount ?? (float) ($lockedAppt->total_amount ?? $lockedAppt->total ?? optional($lockedAppt->service)->price ?? 0);
            $formattedAmount = number_format($amount, 0, ',', '.');
            $finalNote = $refundNote ?: "Đã hoàn trả viện phí {$formattedAmount} VNĐ cho bệnh nhân vào lúc " . now()->format('H:i - d/m/Y');

            $lockedAppt->refund_status = Appointment::REFUND_REFUNDED;
            $lockedAppt->refund_amount = $amount;
            $lockedAppt->refund_note = $finalNote;
            $lockedAppt->save();

            $actionRole = $user->role ? strtolower(trim($user->role->name)) : 'admin';

            AppointmentStatusHistory::create([
                'appointment_id' => $lockedAppt->id,
                'from_status' => $lockedAppt->status,
                'to_status' => $lockedAppt->status,
                'changed_by_user_id' => $user->id,
                'action_role' => $actionRole,
                'note' => "Xác nhận hoàn tiền viện phí ({$formattedAmount} VNĐ). {$finalNote}",
            ]);

            return $lockedAppt;
        }, 5);
    }

    /**
     * Dời lịch khám bệnh sang khung giờ mới an toàn (Reschedule)
     *
     * @param Appointment $appointment
     * @param int $newTimeSlotId
     * @param User $user
     * @param string|null $reason
     * @return Appointment
     * @throws ValidationException
     */
    public function rescheduleAppointment(Appointment $appointment, int $newTimeSlotId, User $user, ?string $reason = null): Appointment
    {
        // 1. Kiểm tra quyền sở hữu
        $isAdmin = $user->hasRole('admin');
        $isDoctor = $user->hasRole('doctor') && $user->doctor && $user->doctor->id === $appointment->doctor_id;
        $isOwnerPatient = $user->patient && $user->patient->id === $appointment->patient_id;

        if (!$isAdmin && !$isDoctor && !$isOwnerPatient) {
            throw ValidationException::withMessages([
                'authorization' => 'Bạn không có quyền dời lịch hẹn này.',
            ]);
        }

        // 2. Kiểm tra điều kiện dời lịch
        if ($isOwnerPatient && !$isAdmin) {
            $check = $appointment->canBeRescheduledByPatient(self::CANCELLATION_DEADLINE_HOURS);
            if (!$check['can']) {
                throw ValidationException::withMessages([
                    'status' => $check['reason'],
                ]);
            }
        } elseif (!in_array($appointment->status, [Appointment::STATUS_PENDING, Appointment::STATUS_CONFIRMED])) {
            throw ValidationException::withMessages([
                'status' => 'Lịch hẹn đã kết thúc hoặc đã hủy, không thể dời lịch.',
            ]);
        }

        if ($appointment->time_slot_id === $newTimeSlotId) {
            throw ValidationException::withMessages([
                'new_time_slot_id' => 'Khung giờ mới trùng với khung giờ hiện tại của lịch hẹn.',
            ]);
        }

        // 3. Thực thi Transaction kép với Row-Level Locking
        return DB::transaction(function () use ($appointment, $newTimeSlotId, $user, $reason) {
            $lockedAppt = Appointment::where('id', $appointment->id)->lockForUpdate()->firstOrFail();
            $oldTimeSlot = $lockedAppt->timeSlot;
            $oldTimeText = $oldTimeSlot ? $oldTimeSlot->formatted_time . ' ngày ' . Carbon::parse($oldTimeSlot->date)->format('d/m/Y') : Carbon::parse($lockedAppt->appointment_date)->format('d/m/Y H:i');

            // Giải phóng chỗ cũ
            if ($lockedAppt->time_slot_id) {
                $oldSlot = TimeSlot::where('id', $lockedAppt->time_slot_id)->lockForUpdate()->first();
                if ($oldSlot) {
                    $oldSlot->booked_count = max(0, $oldSlot->booked_count - 1);
                    if ($oldSlot->status === TimeSlot::STATUS_LOCKED && $oldSlot->booked_count < $oldSlot->capacity) {
                        $oldSlot->status = TimeSlot::STATUS_AVAILABLE;
                    }
                    $oldSlot->save();
                }
            }

            // Chiếm chỗ mới
            $newSlot = TimeSlot::where('id', $newTimeSlotId)->lockForUpdate()->first();
            if (!$newSlot) {
                throw ValidationException::withMessages([
                    'new_time_slot_id' => 'Khung giờ mới không tồn tại.',
                ]);
            }
            if ((int) $newSlot->doctor_id !== (int) $lockedAppt->doctor_id) {
                throw ValidationException::withMessages([
                    'new_time_slot_id' => 'Khung giờ mới không thuộc bác sĩ của lịch hẹn này.',
                ]);
            }
            if (!$newSlot->is_active || $newSlot->status !== TimeSlot::STATUS_AVAILABLE) {
                throw ValidationException::withMessages([
                    'new_time_slot_id' => 'Khung giờ mới hiện đang bị khóa hoặc ngừng tiếp nhận.',
                ]);
            }
            if ($newSlot->start_date_time->isPast()) {
                throw ValidationException::withMessages([
                    'new_time_slot_id' => 'Khung giờ mới đã qua thời gian tiếp nhận.',
                ]);
            }
            if ($newSlot->booked_count >= $newSlot->capacity) {
                throw ValidationException::withMessages([
                    'new_time_slot_id' => 'Khung giờ mới đã đủ số lượng tiếp nhận.',
                ]);
            }

            try {
                $this->assertNoTimeConflictForExaminee(
                    $lockedAppt->patient_id,
                    $newSlot,
                    (string) ($lockedAppt->booking_for ?? 'self'),
                    $lockedAppt->relative_name,
                    $lockedAppt->id
                );
            } catch (ValidationException $e) {
                throw ValidationException::withMessages([
                    'new_time_slot_id' => collect($e->errors())->flatten()->first() ?: 'Bạn đã có một lịch hẹn khác trong khung giờ mới này.',
                ]);
            }

            $newSlot->booked_count += 1;
            if ($newSlot->booked_count >= $newSlot->capacity) {
                $newSlot->status = TimeSlot::STATUS_LOCKED;
            }
            $newSlot->save();

            // Cập nhật lịch hẹn
            $startHour = (int) substr($newSlot->start_time, 0, 2);
            $shiftName = $startHour < 12 ? 'Ca sáng (07:30 - 11:30)' : 'Ca chiều (13:00 - 17:00)';

            $lockedAppt->time_slot_id = $newSlot->id;
            $lockedAppt->appointment_date = $newSlot->date->format('Y-m-d') . ' ' . $newSlot->start_time;
            $lockedAppt->medical_examination = $shiftName;
            $lockedAppt->save();

            // Ghi audit history
            $newTimeText = $newSlot->formatted_time . ' ngày ' . Carbon::parse($newSlot->date)->format('d/m/Y');
            AppointmentStatusHistory::create([
                'appointment_id' => $lockedAppt->id,
                'from_status' => $lockedAppt->status,
                'to_status' => $lockedAppt->status,
                'changed_by_user_id' => $user->id,
                'action_role' => $user->role ? strtolower(trim($user->role->name)) : 'patient',
                'note' => "Dời lịch khám từ [{$oldTimeText}] sang [{$newTimeText}]" . ($reason ? ". Lý do: {$reason}" : ''),
            ]);

            return $lockedAppt;
        }, 5);
    }
}
