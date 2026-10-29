<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\LabTest;
use App\Models\MedicalRecord;
use Illuminate\Http\Request;

class LabTestController extends Controller
{
    // Hiển thị form yêu cầu xét nghiệm
    public function create($recordId)
    {
        $record = MedicalRecord::with('appointment')->findOrFail($recordId);
        $user = auth('web_doctor')->user() ?? auth()->user();

        if (!$user || !$user->doctor || ($record->appointment && (int) $record->appointment->doctor_id !== (int) $user->doctor->id)) {
            abort(403, 'Bạn không có quyền chỉ định xét nghiệm cho hồ sơ này.');
        }

        $departments = Department::all();

        return view('doctor.lab_tests.create', compact('record', 'departments'));
    }

    // Lưu yêu cầu xét nghiệm mới vào database
    public function store(Request $request, $recordId)
    {
        $record = MedicalRecord::with('appointment')->findOrFail($recordId);
        $user = auth('web_doctor')->user() ?? auth()->user();

        if (!$user || !$user->doctor || ($record->appointment && (int) $record->appointment->doctor_id !== (int) $user->doctor->id)) {
            abort(403, 'Bạn không có quyền chỉ định xét nghiệm cho hồ sơ này.');
        }

        // Validate dữ liệu đầu vào
        $request->validate([
            'test_name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'test_type_id' => 'nullable|exists:test_types,id',
            'note' => 'nullable|string|max:2000',
        ]);

        $testTypeId = $request->input('test_type_id');
        if (!$testTypeId) {
            $testTypeId = \App\Models\TestType::where('name', trim($request->test_name))->value('id');
        }

        // Tạo yêu cầu xét nghiệm mới (sử dụng status = 'requested' khớp với schema DB)
        $labTest = LabTest::create([
            'medical_record_id' => $recordId,
            'department_id' => $request->department_id,
            'test_type_id' => $testTypeId,
            'test_name' => $request->test_name,
            'note' => $request->note,
            'requested_by' => $user->id,
            'status' => 'requested',
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Yêu cầu xét nghiệm đã được gửi thành công.',
                'lab_test' => $labTest->load('department'),
            ]);
        }

        return redirect()->route('doctor.patient.record', $record->appointment_id)
            ->with('success', 'Yêu cầu xét nghiệm đã được tạo thành công.');
    }
}

