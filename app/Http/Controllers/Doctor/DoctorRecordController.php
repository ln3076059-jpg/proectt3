<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\PrescriptionItem;
use App\Models\Service;
use App\Services\AppointmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class DoctorRecordController extends Controller
{
    protected AppointmentService $appointmentService;

    public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;
    }

    /**
     * Hiển thị hồ sơ khám bệnh của bệnh nhân (tạo mới nếu chưa có)
     * Cho phép bác sĩ phụ trách chỉnh sửa, và bác sĩ chuyên khoa khác xem tiền sử (Read-only)
     */
    public function showPatientRecord($appointmentId)
    {
        // Lấy thông tin lịch hẹn
        $appointment = Appointment::with(['patient', 'doctor.user', 'doctor.department', 'service'])->findOrFail($appointmentId);

        // Kiểm tra quyền: Chỉ Bác sĩ được phân công phụ trách lịch hẹn mới được truy cập
        $user = auth('web_doctor')->user() ?? auth()->user();
        $doctor = $user ? $user->doctor : null;
        if (!$doctor || (int) $doctor->id !== (int) $appointment->doctor_id) {
            abort(403, 'Bạn không có quyền truy cập hồ sơ bệnh án này.');
        }

        $isReadOnly = false;

        // Lấy danh sách dịch vụ y tế và thuốc trong kho dược
        $services = Service::with('department')->orderBy('name')->get();
        $allMeds = Medicine::orderBy('name')->get();

        // Tạo hoặc lấy hồ sơ bệnh án
        $record = MedicalRecord::firstOrCreate(
            ['appointment_id' => $appointmentId],
            [
                'patient_id' => $appointment->patient_id,
                'description' => null,
                'diagnosis' => null,
                'doctor_conclusion' => null,
                'prescription' => [],
            ]
        );

        // Eager load các quan hệ cần thiết
        $record->loadMissing([
            'prescriptionItems.medicine',
            'labTests.department',
            'labTests.uploader',
        ]);

        // Lấy tiền sử các lần khám trước của đúng người khám (Tách biệt hồ sơ Người thân vs Chủ tài khoản)
        // Sử dụng chuẩn hóa tên (normalizePersonName) để "Bé Nguyễn Bảo An" và "Nguyễn Bảo An" vẫn khớp đúng tiền sử
        $isRelativeBooking = $appointment->booking_for === 'relative' && !empty($appointment->relative_name);
        $normalizedTargetRel = $isRelativeBooking ? Appointment::normalizePersonName($appointment->relative_name) : '';

        $candidateRecords = MedicalRecord::with([
            'appointment.doctor.user',
            'appointment.doctor.department',
            'appointment.service',
            'prescriptionItems.medicine',
        ])
            ->where('patient_id', $appointment->patient_id)
            ->where('id', '!=', $record->id)
            ->whereNotNull('diagnosis')
            ->whereHas('appointment', function ($q) use ($isRelativeBooking) {
                if ($isRelativeBooking) {
                    $q->where('booking_for', 'relative')
                      ->whereNotNull('relative_name');
                } else {
                    $q->where(function ($sub) {
                        $sub->whereNull('booking_for')
                            ->orWhere('booking_for', 'self');
                    });
                }
            })
            ->latest()
            ->limit(15)
            ->get();

        if ($isRelativeBooking) {
            $targetDob = $appointment->relative_dob ? \Carbon\Carbon::parse($appointment->relative_dob)->toDateString() : null;
            $targetRel = trim((string) $appointment->relative_relationship);

            $previousRecords = $candidateRecords
                ->filter(function ($rec) use ($normalizedTargetRel, $targetDob, $targetRel) {
                    $recAppt = $rec->appointment;
                    if (!$recAppt) {
                        return false;
                    }
                    if (Appointment::normalizePersonName($recAppt->relative_name) !== $normalizedTargetRel) {
                        return false;
                    }
                    // Tách biệt hồ sơ người thân nếu có ngày sinh khác nhau hoặc mối quan hệ khác nhau
                    $recDob = $recAppt->relative_dob ? \Carbon\Carbon::parse($recAppt->relative_dob)->toDateString() : null;
                    if ($targetDob && $recDob && $targetDob !== $recDob) {
                        return false;
                    }
                    $recRel = trim((string) $recAppt->relative_relationship);
                    if ($targetRel && $recRel && strcasecmp($targetRel, $recRel) !== 0) {
                        return false;
                    }
                    return true;
                })
                ->take(5)
                ->values();
        } else {
            $previousRecords = $candidateRecords->take(5)->values();
        }

        // Gợi ý nhanh các chẩn đoán thường gặp theo chuyên khoa của bác sĩ
        $deptId = optional($appointment->doctor)->department_id;
        $commonDiseases = \App\Models\Disease::when($deptId, function ($q) use ($deptId) {
            $q->where('department_id', $deptId);
        })->limit(8)->pluck('name');

        if ($commonDiseases->isEmpty()) {
            $commonDiseases = \App\Models\Disease::limit(8)->pluck('name');
        }

        return view('doctor.patient_record', compact(
            'appointment',
            'record',
            'services',
            'allMeds',
            'previousRecords',
            'commonDiseases',
            'isReadOnly'
        ))->with('patient', $appointment->patient);
    }

    /**
     * Cập nhật thông tin chẩn đoán, kết luận, toa thuốc và ảnh cận lâm sàng
     */
    public function update(Request $request, MedicalRecord $record)
    {
        // Kiểm tra quyền: Bác sĩ chỉ được sửa hồ sơ của lịch hẹn do mình phụ trách
        $user = auth('web_doctor')->user() ?? auth()->user();
        $doctor = $user ? $user->doctor : null;
        $appointment = $record->appointment;

        if (!$doctor || ($appointment && (int) $doctor->id !== (int) $appointment->doctor_id)) {
            abort(403, 'Bạn không có quyền cập nhật hồ sơ bệnh án này.');
        }

        // Validate dữ liệu đầu vào
        $request->validate([
            'diagnosis' => 'required|string|max:1000',
            'description' => 'nullable|string|max:5000',
            'doctor_conclusion' => 'nullable|string|max:2000',
            'image' => 'nullable|image|max:5120',
            'images.*' => 'nullable|image|max:5120',
            'status' => 'nullable|in:pending,confirmed,checked_in,completed',
            'prescription_items' => 'nullable|array',
            'prescription_items.*.medicine_id' => 'nullable|integer|exists:medicines,id',
            'prescription_items.*.quantity' => 'nullable|integer|min:1|max:9999',
            'prescription_items.*.unit' => 'nullable|string|max:255',
            'prescription_items.*.usage' => 'nullable|string|max:500',
            'prescription_items.*.note' => 'nullable|string|max:500',
        ], [
            'diagnosis.required' => 'Vui lòng nhập chẩn đoán bệnh lý chính cho bệnh nhân.',
        ]);

        try {
            // Lọc các dòng thuốc hợp lệ (bắt buộc phải có medicine_id)
            $rawItems = $request->input('prescription_items', []);
            $validItems = [];

            if (is_array($rawItems)) {
                foreach ($rawItems as $item) {
                    if (!empty($item['medicine_id'])) {
                        $validItems[] = [
                            'medicine_id' => (int) $item['medicine_id'],
                            'dosage' => $item['dosage'] ?? null,
                            'frequency' => $item['frequency'] ?? null,
                            'duration' => $item['duration'] ?? null,
                            'quantity' => max(1, (int) ($item['quantity'] ?? 1)),
                            'unit' => !empty($item['unit']) ? trim($item['unit']) : 'Viên',
                            'usage' => $item['usage'] ?? 'Uống theo chỉ định',
                            'note' => $item['note'] ?? null,
                        ];
                    }
                }
            }

            // Kiểm tra tồn kho thuốc đối với các thuốc đã chọn (gộp tổng số lượng theo từng medicine_id & cộng hoàn tạm đơn cũ nếu ca khám đã completed)
            if (!empty($validItems)) {
                $medIds = collect($validItems)->pluck('medicine_id')->unique()->all();
                $medicines = Medicine::whereIn('id', $medIds)->get()->keyBy('id');

                $appointmentForStock = $record->appointment;
                $wasAlreadyCompleted = $appointmentForStock && $appointmentForStock->status === Appointment::STATUS_COMPLETED;
                $oldQtyByMed = $wasAlreadyCompleted
                    ? $record->prescriptionItems()->get()->groupBy('medicine_id')->map(fn ($rows) => (int) $rows->sum('quantity'))
                    : collect();

                $requestedByMed = collect($validItems)
                    ->groupBy('medicine_id')
                    ->map(fn ($rows) => (int) $rows->sum('quantity'));

                foreach ($requestedByMed as $mid => $totalRequestedQty) {
                    if (isset($medicines[$mid])) {
                        $currentWarehouseStock = (int) ($medicines[$mid]->stock ?? 0);
                        $heldByCurrentRecord = (int) ($oldQtyByMed[$mid] ?? 0);
                        $availableStock = $currentWarehouseStock + $heldByCurrentRecord;

                        if ($totalRequestedQty > $availableStock) {
                            return redirect()
                                ->back()
                                ->withErrors([
                                    'prescription_items' => "Thuốc \"{$medicines[$mid]->name}\" chỉ còn {$availableStock} đơn vị khả dụng trong kho (tổng kê: {$totalRequestedQty}). Vui lòng điều chỉnh số lượng."
                                ])
                                ->withInput();
                        }
                    }
                }
            }

            DB::transaction(function () use ($record, $request, $validItems, $user) {
                $appointment = $record->appointment;
                $wasCompleted = $appointment && $appointment->status === Appointment::STATUS_COMPLETED;
                $oldDiagnosis = $record->diagnosis;
                $oldMedCount = $record->prescriptionItems()->count();
                $targetStatus = $request->filled('status') ? $request->input('status') : ($appointment ? $appointment->status : null);
                $allowed = ['pending', 'confirmed', 'checked_in', 'completed'];

                // TH1: Nếu ca khám trước đó đã completed và nay chuyển về trạng thái chưa completed -> Ủy quyền cho AppointmentService hoàn lại kho đơn cũ trước khi sửa đơn
                if ($appointment && $wasCompleted && in_array($targetStatus, $allowed, true) && $targetStatus !== Appointment::STATUS_COMPLETED) {
                    $this->appointmentService->updateStatus(
                        $appointment,
                        $targetStatus,
                        $user,
                        'Cập nhật trạng thái từ hồ sơ bệnh án EMR'
                    );
                } elseif ($wasCompleted) {
                    // Nếu ca khám đang completed và tiếp tục giữ completed khi sửa đơn -> Hoàn lại kho đơn cũ có khóa hàng (lockForUpdate) theo thứ tự medicine_id trước khi áp đơn mới
                    $oldGrouped = $record->prescriptionItems()->get()
                        ->filter(fn ($row) => !empty($row->medicine_id) && $row->quantity > 0)
                        ->groupBy('medicine_id')
                        ->map(fn ($rows) => (int) $rows->sum('quantity'))
                        ->sortKeys();

                    foreach ($oldGrouped as $oldMedId => $oldTotalQty) {
                        $med = Medicine::where('id', $oldMedId)->lockForUpdate()->first();
                        if ($med && $oldTotalQty > 0) {
                            $med->increment('stock', $oldTotalQty);
                        }
                    }
                }

                // 1. Cập nhật chẩn đoán và triệu chứng (bảo toàn mô tả lâm sàng)
                $record->diagnosis = $request->input('diagnosis');
                $record->description = $request->input('description');
                $record->doctor_conclusion = $request->input('doctor_conclusion');

                // 2. Cập nhật các dòng đơn thuốc vào bảng prescription_items
                $record->prescriptionItems()->delete();

                foreach ($validItems as $item) {
                    PrescriptionItem::create([
                        'prescription_id' => $record->id,
                        'medicine_id' => $item['medicine_id'],
                        'dosage' => $item['dosage'] ?? null,
                        'frequency' => $item['frequency'] ?? null,
                        'duration' => $item['duration'] ?? null,
                        'quantity' => $item['quantity'],
                        'unit' => $item['unit'],
                        'usage' => $item['usage'],
                        'note' => $item['note'],
                    ]);
                }

                // 3. Đồng bộ vào JSON prescription để tương thích ngược
                $record->prescription = $validItems;

                // 4. Xử lý upload ảnh đơn
                if ($request->hasFile('image')) {
                    $path = $request->file('image')->store('medical_records', 'public');
                    $record->image = $path;
                }

                // 5. Xử lý upload nhiều ảnh
                if ($request->hasFile('images')) {
                    $stored = [];
                    foreach ($request->file('images') as $file) {
                        if ($file) {
                            $stored[] = $file->store('medical_records', 'public');
                        }
                    }
                    $current = is_array($record->images) ? $record->images : [];
                    $record->images = array_values(array_filter(array_merge($current, $stored)));
                }

                $record->save();

                // 6. Hợp nhất cập nhật trạng thái và trừ kho thuốc qua AppointmentService::updateStatus()
                if ($appointment && in_array($targetStatus, $allowed, true)) {
                    $pendingLabCount = $record->labTests()->where('status', 'requested')->count();

                    if (!$wasCompleted && $appointment->status !== $targetStatus) {
                        $statusNote = 'Cập nhật từ hồ sơ bệnh án EMR';
                        if ($targetStatus === Appointment::STATUS_COMPLETED && $pendingLabCount > 0) {
                            $statusNote .= " (Lưu ý lâm sàng: Hoàn tất ca khám khi còn {$pendingLabCount} chỉ định xét nghiệm đang chờ kết quả)";
                        }
                        // Chuyển trạng thái (bao gồm chuyển sang completed -> tự động lockForUpdate & trừ kho trong AppointmentService)
                        $this->appointmentService->updateStatus(
                            $appointment,
                            $targetStatus,
                            $user,
                            $statusNote
                        );
                    } elseif ($wasCompleted && $targetStatus === Appointment::STATUS_COMPLETED) {
                        // Giữ nguyên trạng thái completed sau khi sửa đơn -> Gộp theo medicine_id (sắp xếp tăng dần chống deadlock), khóa hàng & trừ kho đơn mới
                        $newGrouped = collect($validItems)
                            ->filter(fn ($row) => !empty($row['medicine_id']) && ($row['quantity'] ?? 0) > 0)
                            ->groupBy('medicine_id')
                            ->map(fn ($rows) => (int) $rows->sum('quantity'))
                            ->sortKeys();

                        foreach ($newGrouped as $newMedId => $newTotalQty) {
                            $med = Medicine::where('id', $newMedId)->lockForUpdate()->first();
                            if ($med) {
                                if ($med->stock < $newTotalQty) {
                                    throw ValidationException::withMessages([
                                        'prescription_items' => "Thuốc \"{$med->name}\" trong đơn chỉ còn {$med->stock} đơn vị (cần {$newTotalQty}).",
                                    ]);
                                }
                                $med->decrement('stock', $newTotalQty);
                            }
                        }

                        // Lưu vết kiểm toán y khoa (EMR Revision Audit Log theo Thông tư 46/2018/TT-BYT) khi chỉnh sửa bệnh án đã hoàn tất
                        $newMedCount = count($validItems);
                        \App\Models\AppointmentStatusHistory::create([
                            'appointment_id' => $appointment->id,
                            'from_status' => Appointment::STATUS_COMPLETED,
                            'to_status' => Appointment::STATUS_COMPLETED,
                            'changed_by_user_id' => $user ? $user->id : null,
                            'action_role' => 'doctor',
                            'note' => "Chỉnh sửa bổ sung Bệnh án điện tử EMR (Sau khi hoàn tất): Chẩn đoán [" . ($oldDiagnosis ?: 'Trống') . "] -> [{$record->diagnosis}]; Đơn thuốc [{$oldMedCount} -> {$newMedCount} khoản thuốc]",
                        ]);
                    }
                }
            });

            return redirect()
                ->back()
                ->with('success', 'Cập nhật hồ sơ bệnh án và đơn thuốc thành công!');
        } catch (ValidationException $e) {
            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Lỗi khi cập nhật hồ sơ bệnh án', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            return redirect()
                ->back()
                ->with('error', 'Không thể cập nhật hồ sơ bệnh án: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Danh sách bệnh án của một bệnh nhân
     */
    public function index($patientId)
    {
        $patient = Patient::findOrFail($patientId);
        return redirect()->route('doctor.patients.show', $patient->id);
    }

    /**
     * Lưu bệnh án từ route patients.records.store (nếu có)
     */
    public function store(Request $request, $patientId)
    {
        $patient = Patient::findOrFail($patientId);
        return redirect()->route('doctor.patients.show', $patient->id)
            ->with('info', 'Vui lòng chọn ca khám cụ thể để ghi nhận bệnh án EMR.');
    }
}
