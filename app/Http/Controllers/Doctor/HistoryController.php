<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\MedicalRecord;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    // Hiển thị lịch sử khám: danh sách hồ sơ bệnh án của bác sĩ hiện tại
    public function history(Request $request)
    {
        $user = $request->user();
        $doctorId = optional(optional($user)->doctor)->id;

        // Lấy danh sách hồ sơ bệnh án của bác sĩ hiện tại
        $query = MedicalRecord::with(['appointment.patient', 'appointment.service', 'prescriptionItems.medicine'])
            ->when($doctorId, function ($q) use ($doctorId) {
                $q->whereHas('appointment', function ($sq) use ($doctorId) {
                    $sq->where('doctor_id', $doctorId);
                });
            }, function ($q) {
                $q->whereRaw('1 = 0');
            });

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('diagnosis', 'like', "%{$search}%")
                    ->orWhereHas('appointment.patient', function ($pq) use ($search) {
                        $pq->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    })
                    ->orWhereHas('appointment', function ($aq) use ($search) {
                        $aq->where('appointment_code', 'like', "%{$search}%")
                            ->orWhere('relative_name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('date')) {
            $date = $request->date;
            $query->whereHas('appointment', function ($aq) use ($date) {
                $aq->whereDate('appointment_date', $date);
            });
        }

        $medicalRecords = $query->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('doctor.patient_history', compact('medicalRecords'));

    }
}
