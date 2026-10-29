<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\MedicalRecord;
use Illuminate\Http\Request;

class MedicalRecordController extends Controller
{
    // Hiển thị danh sách hồ sơ bệnh án với bộ lọc tìm kiếm
    public function index(Request $request)
    {
        $query = MedicalRecord::with(['patient.user', 'appointment.doctor.user', 'appointment.service'])
            ->withCount(['prescriptionItems', 'labTests']);

        // Lọc theo tên bệnh nhân / SĐT / chẩn đoán
        if ($request->filled('patient_name')) {
            $pName = trim($request->patient_name);
            $query->where(function ($q) use ($pName) {
                $q->where('diagnosis', 'like', "%{$pName}%")
                    ->orWhereHas('patient', fn($sq) => $sq->where('name', 'like', "%{$pName}%")->orWhere('phone', 'like', "%{$pName}%"))
                    ->orWhereHas('patient.user', fn($sq) => $sq->where('name', 'like', "%{$pName}%"))
                    ->orWhereHas('appointment.patient', fn($sq) => $sq->where('name', 'like', "%{$pName}%")->orWhere('phone', 'like', "%{$pName}%"));
            });
        }

        // Lọc theo tên bác sĩ
        if ($request->filled('doctor_name')) {
            $query->whereHas('appointment.doctor.user', function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->doctor_name.'%');
            });
        }

        // Lọc theo ngày khám
        if ($request->filled('appointment_date')) {
            $query->whereHas('appointment', function ($q) use ($request) {
                $q->whereDate('appointment_date', $request->appointment_date);
            });
        }

        // Lấy danh sách hồ sơ bệnh án với phân trang
        $records = $query->orderByDesc('id')->paginate(10)->withQueryString();

        return view('admin.medical-records.index', compact('records'));
    }

    // Lưu hồ sơ bệnh án mới vào database
    public function store(Request $request)
    {
        // Validate dữ liệu đầu vào
        $data = $request->validate([
            'appointment_id' => ['required', 'exists:appointments,id'],
            'patient_id' => ['nullable', 'exists:patients,id'],
            'description' => ['nullable', 'string'],
            'diagnosis' => ['nullable', 'string', 'max:1000'],
            'doctor_conclusion' => ['nullable', 'string'],
            'prescription' => ['nullable'],
        ]);

        // Lấy thông tin lịch hẹn
        $appointment = Appointment::with('patient')->findOrFail($data['appointment_id']);

        // Nếu không có patient_id, lấy từ appointment
        if (empty($data['patient_id'])) {
            $data['patient_id'] = $appointment->patient_id;
        }

        // Xử lý prescription nếu là chuỗi JSON
        if (is_string($data['prescription'] ?? null)) {
            $decoded = json_decode($data['prescription'], true);
            $data['prescription'] = json_last_error() === JSON_ERROR_NONE ? $decoded : null;
        }

        // Tạo hồ sơ bệnh án mới
        $record = MedicalRecord::create($data);

        return redirect()
            ->route('admin.medical-records.show', $record)
            ->with('success', 'Tạo hồ sơ bệnh án thành công.');
    }

    // Hiển thị chi tiết hồ sơ bệnh án
    public function show(MedicalRecord $medicalRecord)
    {
        // Load các quan hệ cần thiết
        $medicalRecord->loadMissing([
            'patient.user',
            'appointment.doctor.user',
            'appointment.doctor.department',
            'appointment.service',
            'prescriptionItems.medicine',
            'labTests.department',
        ]);

        // Lấy danh sách các mục trong đơn thuốc
        $items = $medicalRecord->prescriptionItems ?? collect();

        return view('admin.medical-records.show', compact('medicalRecord', 'items'));
    }
}

