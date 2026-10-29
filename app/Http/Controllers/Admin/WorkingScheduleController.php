<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\TimeSlot;
use App\Models\WorkingSchedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkingScheduleController extends Controller
{
    /**
     * F10: Danh sách lịch làm việc và khung giờ của bác sĩ
     */
    public function index(Request $request)
    {
        $doctors = Doctor::where('is_active', true)->with('user', 'department')->get();
        $selectedDoctorId = $request->get('doctor_id');
        $selectedDate = $request->get('date', now()->format('Y-m-d'));

        $query = WorkingSchedule::with(['doctor.user', 'doctor.department', 'timeSlots.activeAppointments'])
            ->orderBy('date', 'desc')
            ->orderBy('start_time', 'asc');

        if ($selectedDoctorId) {
            $query->where('doctor_id', $selectedDoctorId);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('date', '<=', $request->to_date);
        }

        $schedules = $query->paginate(10)->withQueryString();

        return view('admin.schedules.index', compact(
            'doctors',
            'selectedDoctorId',
            'selectedDate',
            'schedules'
        ));
    }

    /**
     * Form tạo lịch làm việc mới cho bác sĩ
     */
    public function create()
    {
        $doctors = Doctor::where('is_active', true)->with('user', 'department')->get();
        return view('admin.schedules.create', compact('doctors'));
    }

    /**
     * Lưu lịch làm việc và tự động sinh các khung giờ TimeSlot
     */
    public function store(Request $request)
    {
        // Đồng bộ tên trường giữa form và controller (slot_duration_minutes / slot_duration, default_capacity / capacity)
        $request->merge([
            'slot_duration' => $request->input('slot_duration', $request->input('slot_duration_minutes', 30)),
            'capacity' => $request->input('capacity', $request->input('default_capacity', 5)),
        ]);

        $validated = $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'schedule_mode' => 'nullable|in:single,range',
            'date' => 'required|date|after_or_equal:today',
            'end_date' => 'nullable|date|after_or_equal:date',
            'days_of_week' => 'nullable|array',
            'days_of_week.*' => 'integer|between:0,6',
            'shift_preset' => 'nullable|string|in:custom,morning,afternoon,fullday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'slot_duration' => 'required|integer|in:15,20,30,45,60',
            'capacity' => 'required|integer|min:1|max:50',
            'note' => 'nullable|string|max:500',
        ], [
            'doctor_id.required' => 'Vui lòng chọn bác sĩ.',
            'date.required' => 'Vui lòng chọn ngày bắt đầu làm việc.',
            'date.after_or_equal' => 'Không thể thiết lập lịch làm việc trong quá khứ.',
            'end_time.after' => 'Giờ kết thúc phải sau giờ bắt đầu.',
            'capacity.min' => 'Số lượng tiếp nhận tối thiểu là 1.',
        ]);

        $doctor = Doctor::with('user')->findOrFail($validated['doctor_id']);
        $startDate = Carbon::parse($validated['date'])->startOfDay();
        $isRangeMode = ($validated['schedule_mode'] ?? 'single') === 'range' && !empty($validated['end_date']);

        // Xác định danh sách các ngày cần tạo lịch
        $datesToProcess = [];
        if ($isRangeMode) {
            $endDate = Carbon::parse($validated['end_date'])->startOfDay();
            if ($startDate->diffInDays($endDate) > 60) {
                return back()->withInput()->with('error', 'Khoảng thời gian tạo lịch hàng loạt tối đa là 60 ngày mỗi lần.');
            }

            $selectedDays = isset($validated['days_of_week']) && is_array($validated['days_of_week'])
                ? array_map('intval', $validated['days_of_week'])
                : [1, 2, 3, 4, 5, 6]; // Mặc định Thứ 2 -> Thứ 7

            $cursor = $startDate->copy();
            while ($cursor->lte($endDate)) {
                if (in_array((int) $cursor->dayOfWeek, $selectedDays, true)) {
                    $datesToProcess[] = $cursor->toDateString();
                }
                $cursor->addDay();
            }

            if (empty($datesToProcess)) {
                return back()->withInput()->with('error', 'Không có ngày nào trong khoảng thời gian đã chọn khớp với các thứ trong tuần được tích chọn.');
            }
        } else {
            $datesToProcess = [$startDate->toDateString()];
        }

        // Xác định danh sách các ca cần tạo (hỗ trợ tạo nhanh Cả ngày: Ca sáng + Ca chiều)
        $shiftsToCreate = [];
        if (($validated['shift_preset'] ?? '') === 'fullday') {
            $shiftsToCreate = [
                ['start' => '07:30:00', 'end' => '11:30:00'],
                ['start' => '13:00:00', 'end' => '17:00:00'],
            ];
        } else {
            $shiftsToCreate = [
                [
                    'start' => $validated['start_time'] . ':00',
                    'end' => $validated['end_time'] . ':00',
                ],
            ];
        }

        // Nếu chế độ 1 ngày đơn lẻ: báo lỗi ngay nếu trùng ca làm việc
        if (!$isRangeMode) {
            $singleDate = $datesToProcess[0];
            $existingSchedules = WorkingSchedule::where('doctor_id', $doctor->id)
                ->whereDate('date', $singleDate)
                ->get();

            foreach ($shiftsToCreate as $shift) {
                foreach ($existingSchedules as $existing) {
                    if ($shift['start'] < $existing->end_time && $shift['end'] > $existing->start_time) {
                        $existRange = substr($existing->start_time, 0, 5) . ' - ' . substr($existing->end_time, 0, 5);
                        return back()->withInput()->with(
                            'error',
                            "Bác sĩ {$doctor->user->name} đã có ca làm việc ({$existRange}) trùng giờ trong ngày " . Carbon::parse($singleDate)->format('d/m/Y') . '.'
                        );
                    }
                }
            }
        }

        $createdCount = 0;
        $skippedCount = 0;

        DB::transaction(function () use ($validated, $doctor, $datesToProcess, $shiftsToCreate, &$createdCount, &$skippedCount) {
            foreach ($datesToProcess as $targetDate) {
                $dayOfWeek = Carbon::parse($targetDate)->dayOfWeek;
                $existingOnDate = WorkingSchedule::where('doctor_id', $doctor->id)
                    ->whereDate('date', $targetDate)
                    ->get();

                foreach ($shiftsToCreate as $shift) {
                    $hasConflict = false;
                    foreach ($existingOnDate as $existing) {
                        if ($shift['start'] < $existing->end_time && $shift['end'] > $existing->start_time) {
                            $hasConflict = true;
                            break;
                        }
                    }

                    if ($hasConflict) {
                        $skippedCount++;
                        continue;
                    }

                    $schedule = WorkingSchedule::create([
                        'doctor_id' => $doctor->id,
                        'date' => $targetDate,
                        'day_of_week' => $dayOfWeek,
                        'start_time' => $shift['start'],
                        'end_time' => $shift['end'],
                        'status' => WorkingSchedule::STATUS_ACTIVE,
                        'note' => $validated['note'] ?? null,
                    ]);

                    // Tự động sinh Time Slots
                    $schedule->generateTimeSlots((int) $validated['slot_duration'], (int) $validated['capacity']);
                    $createdCount++;
                }
            }
        });

        if ($isRangeMode) {
            $msg = "Đã thiết lập hàng loạt {$createdCount} ca làm việc (trên " . count($datesToProcess) . " ngày) và tự động sinh khung giờ khám thành công!";
            if ($skippedCount > 0) {
                $msg .= " (Đã bỏ qua {$skippedCount} ca trùng lịch có sẵn).";
            }
            return redirect()->route('admin.schedules.index', ['doctor_id' => $doctor->id])
                ->with('success', $msg);
        }

        return redirect()->route('admin.schedules.index', ['doctor_id' => $doctor->id])
            ->with('success', 'Thiết lập lịch làm việc và tạo các khung giờ khám thành công!');
    }

    /**
     * Khóa hoặc Mở khóa một khung giờ khám (F04, F10)
     */
    public function toggleSlotStatus(Request $request, $slotId)
    {
        $slot = TimeSlot::findOrFail($slotId);

        if ($slot->status === TimeSlot::STATUS_AVAILABLE) {
            $slot->status = TimeSlot::STATUS_LOCKED;
            $msg = 'Đã khóa khung giờ khám thành công.';
        } else {
            // Chỉ mở lại nếu chưa quá giờ và chưa đủ chỗ
            if ($slot->start_date_time->isPast()) {
                return back()->with('error', 'Khung giờ này đã qua thời gian tiếp nhận, không thể mở lại.');
            }
            $slot->status = TimeSlot::STATUS_AVAILABLE;
            $msg = 'Đã mở khóa khung giờ khám.';
        }

        $slot->save();

        return back()->with('success', $msg);
    }

    /**
     * Cập nhật số lượt khám tối đa (Capacity) của khung giờ
     */
    public function updateSlotCapacity(Request $request, $slotId)
    {
        $request->validate([
            'capacity' => 'required|integer|min:1|max:50',
        ]);

        $slot = TimeSlot::findOrFail($slotId);
        $newCapacity = (int) $request->capacity;

        // Không cho phép giảm capacity xuống thấp hơn số lịch đã đặt
        if ($newCapacity < $slot->booked_count) {
            return back()->with('error', "Không thể giảm số lượng tiếp nhận xuống {$newCapacity} vì đã có {$slot->booked_count} bệnh nhân đặt lịch.");
        }

        $slot->capacity = $newCapacity;
        if ($slot->status === TimeSlot::STATUS_LOCKED && $slot->booked_count < $newCapacity && !$slot->start_date_time->isPast()) {
            $slot->status = TimeSlot::STATUS_AVAILABLE;
        }
        $slot->save();

        return back()->with('success', "Đã cập nhật số lượng tiếp nhận cho khung giờ {$slot->formatted_time} thành {$newCapacity} bệnh nhân.");
    }

    /**
     * Báo nghỉ đột xuất (on_leave) kèm tự động khóa slot & điều chuyển lịch hẹn sang bác sĩ cùng chuyên khoa,
     * hoặc mở lại ca trực (active).
     */
    public function toggleLeaveStatus(Request $request, $id)
    {
        $schedule = WorkingSchedule::with(['doctor.user', 'doctor.department', 'timeSlots'])->findOrFail($id);
        $user = auth('web_admin')->user() ?? auth()->user();

        if ($schedule->status !== WorkingSchedule::STATUS_ON_LEAVE) {
            $reassignedCount = 0;
            $flaggedCount = 0;

            DB::transaction(function () use ($schedule, $user, &$reassignedCount, &$flaggedCount) {
                $schedule->status = WorkingSchedule::STATUS_ON_LEAVE;
                $schedule->note = trim(($schedule->note ? $schedule->note . ' | ' : '') . 'Báo nghỉ đột xuất lúc ' . now()->format('H:i d/m/Y'));
                $schedule->save();

                $slotIds = $schedule->timeSlots->pluck('id')->all();

                // Khóa toàn bộ các khung giờ của ca trực này để ngừng nhận khách mới
                TimeSlot::whereIn('id', $slotIds)->update(['status' => TimeSlot::STATUS_LOCKED]);

                // Tìm các lịch hẹn đang chờ khám (pending, confirmed) trong ca này để điều chuyển hoặc đánh dấu
                $affectedAppointments = Appointment::with('timeSlot')
                    ->whereIn('time_slot_id', $slotIds)
                    ->whereIn('status', [Appointment::STATUS_PENDING, Appointment::STATUS_CONFIRMED])
                    ->lockForUpdate()
                    ->get();

                $deptId = optional($schedule->doctor)->department_id;
                $oldDocName = optional($schedule->doctor->user)->name ?? 'Bác sĩ';

                foreach ($affectedAppointments as $appt) {
                    $oldSlot = $appt->timeSlot;
                    $backupSlot = null;

                    if ($deptId && $oldSlot) {
                        $slotDateStr = $oldSlot->date instanceof Carbon ? $oldSlot->date->toDateString() : (string) $oldSlot->date;
                        $baseBackupQuery = fn () => TimeSlot::with('doctor.user')
                            ->whereDate('date', $slotDateStr)
                            ->where('doctor_id', '!=', $schedule->doctor_id)
                            ->where('is_active', true)
                            ->where('status', TimeSlot::STATUS_AVAILABLE)
                            ->whereColumn('booked_count', '<', 'capacity')
                            ->whereHas('doctor', fn ($dq) => $dq->where('department_id', $deptId)->where('is_active', true));

                        // Ưu tiên 1: Tìm bác sĩ cùng chuyên khoa có khung giờ trùng khớp chính xác start_time
                        $backupSlot = $baseBackupQuery()
                            ->where('start_time', $oldSlot->start_time)
                            ->lockForUpdate()
                            ->first();

                        // Ưu tiên 2: Nếu không có giờ trùng khít, tìm khung giờ lân cận trong biên độ ±60 phút cùng buổi
                        if (!$backupSlot && !empty($oldSlot->start_time)) {
                            $oldStartCarbon = Carbon::parse($slotDateStr . ' ' . $oldSlot->start_time);
                            $minTime = $oldStartCarbon->copy()->subMinutes(60)->format('H:i:s');
                            $maxTime = $oldStartCarbon->copy()->addMinutes(60)->format('H:i:s');

                            $candidates = $baseBackupQuery()
                                ->whereBetween('start_time', [$minTime, $maxTime])
                                ->lockForUpdate()
                                ->get();

                            if ($candidates->isNotEmpty()) {
                                $backupSlot = $candidates->sortBy(function ($cand) use ($slotDateStr, $oldStartCarbon) {
                                    return abs(Carbon::parse($slotDateStr . ' ' . $cand->start_time)->diffInMinutes($oldStartCarbon));
                                })->first();
                            }
                        }
                    }

                    if ($backupSlot) {
                        if ($oldSlot) {
                            $oldSlot->booked_count = max(0, $oldSlot->booked_count - 1);
                            $oldSlot->save();
                        }

                        $backupSlot->booked_count += 1;
                        if ($backupSlot->booked_count >= $backupSlot->capacity) {
                            $backupSlot->status = TimeSlot::STATUS_LOCKED;
                        }
                        $backupSlot->save();

                        $newDocName = optional($backupSlot->doctor->user)->name ?? 'Bác sĩ cùng khoa';
                        $oldTimeStr = $oldSlot ? substr((string) $oldSlot->start_time, 0, 5) : '';
                        $newTimeStr = substr((string) $backupSlot->start_time, 0, 5);
                        $timeShiftNote = ($oldTimeStr && $oldTimeStr !== $newTimeStr)
                            ? " (Khung giờ lân cận: {$oldTimeStr} -> {$newTimeStr} — Lễ tân thông báo xác nhận với bệnh nhân)"
                            : " (Giữ nguyên khung giờ {$newTimeStr})";

                        $appt->doctor_id = $backupSlot->doctor_id;
                        $appt->time_slot_id = $backupSlot->id;
                        $appt->save();

                        \App\Models\AppointmentStatusHistory::create([
                            'appointment_id' => $appt->id,
                            'from_status' => $appt->status,
                            'to_status' => $appt->status,
                            'changed_by_user_id' => $user ? $user->id : null,
                            'action_role' => 'admin',
                            'note' => "Điều chuyển tự động từ BS. {$oldDocName} sang BS. {$newDocName} (cùng chuyên khoa) do bác sĩ nghỉ đột xuất{$timeShiftNote}.",
                        ]);

                        $reassignedCount++;
                    } else {
                        \App\Models\AppointmentStatusHistory::create([
                            'appointment_id' => $appt->id,
                            'from_status' => $appt->status,
                            'to_status' => $appt->status,
                            'changed_by_user_id' => $user ? $user->id : null,
                            'action_role' => 'admin',
                            'note' => "Cảnh báo vận hành: BS. {$oldDocName} báo nghỉ đột xuất. Chưa có bác sĩ cùng khoa trống giờ tương ứng — cần Lễ tân liên hệ dời lịch.",
                        ]);

                        $flaggedCount++;
                    }
                }
            });

            $msg = 'Đã chuyển ca làm việc sang trạng thái Nghỉ phép (On Leave) và khóa các khung giờ trống.';
            if ($reassignedCount > 0 || $flaggedCount > 0) {
                $msg .= " Đã tự động điều chuyển {$reassignedCount} lịch hẹn sang bác sĩ cùng khoa; có {$flaggedCount} lịch hẹn cần Lễ tân hỗ trợ dời lịch.";
            }

            return back()->with('success', $msg);
        } else {
            DB::transaction(function () use ($schedule) {
                $schedule->status = WorkingSchedule::STATUS_ACTIVE;
                $schedule->save();

                foreach ($schedule->timeSlots as $slot) {
                    if ($slot->booked_count < $slot->capacity && !$slot->start_date_time->isPast()) {
                        $slot->status = TimeSlot::STATUS_AVAILABLE;
                        $slot->save();
                    }
                }
            });

            return back()->with('success', 'Đã kích hoạt lại ca làm việc và mở khóa các khung giờ còn chỗ.');
        }
    }

    /**
     * Xóa lịch làm việc (BR07: Không xóa khi có lịch hẹn hiệu lực)
     */
    public function destroy($id)
    {
        $schedule = WorkingSchedule::with('timeSlots')->findOrFail($id);

        $slotIds = $schedule->timeSlots->pluck('id');
        $hasActiveAppointments = Appointment::whereIn('time_slot_id', $slotIds)
            ->whereIn('status', Appointment::ACTIVE_SLOT_STATUSES)
            ->exists();

        if ($hasActiveAppointments) {
            return back()->with('error', 'Không thể xóa lịch làm việc vì đã có lịch hẹn của bệnh nhân trong ca này. Bạn có thể chuyển trạng thái sang nghỉ phép (on leave).');
        }

        $schedule->delete();

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Xóa lịch làm việc thành công.');
    }
}
