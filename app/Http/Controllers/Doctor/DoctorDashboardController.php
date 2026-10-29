<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DoctorDashboardController extends Controller
{
    // Hiển thị trang dashboard của bác sĩ với danh sách lịch hẹn và thống kê
    public function index(Request $request)
    {
        $user = Auth::guard('web_doctor')->user() ?? Auth::user();
        $doctor = $user ? $user->doctor : null;

        // Kiểm tra nếu không tìm thấy thông tin bác sĩ
        if (!$doctor) {
            return view('doctor.dashboard', [
                'error' => 'Không tìm thấy thông tin bác sĩ!',
                'appointments' => collect(),
                'pending' => 0,
                'confirmed' => 0,
                'checkedIn' => 0,
                'completed' => 0,
                'total' => 0,
                'selectedDate' => now()->format('Y-m-d'),
                'statusFilter' => null,
            ]);
        }

        // Ngày được chọn (mặc định hôm nay), bộ lọc trạng thái và từ khóa tìm kiếm / mã QR
        $selectedDate = $request->query('date', now()->format('Y-m-d'));
        $statusFilter = $request->query('status');
        $rawSearch = trim((string) $request->query('search', ''));
        $search = $rawSearch;
        if (preg_match('/MA:([^|\s]+)/i', $rawSearch, $m)) {
            $search = trim($m[1]);
        }
        $search = ltrim($search, '#');

        // Giải phóng các lịch hẹn thanh toán trực tuyến VNPay quá 15 phút chưa hoàn tất của bác sĩ này
        app(\App\Services\AppointmentService::class)->releaseExpiredUnpaidOnlineBookings($doctor->id);

        // Lấy toàn bộ lịch hẹn trong ngày được chọn để tính KPI
        $allDayAppointments = Appointment::with(['patient.user', 'service', 'doctor.user', 'timeSlot'])
            ->where('doctor_id', $doctor->id)
            ->whereDate('appointment_date', $selectedDate)
            ->orderBy('appointment_date')
            ->get();

        // Thống kê theo trạng thái trong ngày
        $pending = $allDayAppointments->where('status', 'pending')->count();
        $confirmed = $allDayAppointments->where('status', 'confirmed')->count();
        $checkedIn = $allDayAppointments->where('status', 'checked_in')->count();
        $completed = $allDayAppointments->where('status', 'completed')->count();
        $total = $allDayAppointments->count();

        // Áp dụng lọc nhanh theo trạng thái và từ khóa tìm kiếm / mã QR
        $appointments = $statusFilter
            ? $allDayAppointments->where('status', $statusFilter)->values()
            : $allDayAppointments;

        if ($search !== '') {
            $needle = mb_strtolower($search, 'UTF-8');
            $appointments = $appointments->filter(function ($appt) use ($needle) {
                $code = mb_strtolower((string) $appt->appointment_code, 'UTF-8');
                $displayName = mb_strtolower((string) $appt->patient_display_name, 'UTF-8');
                $phone = mb_strtolower((string) ($appt->patient->phone ?? optional($appt->patient->user)->phone ?? ''), 'UTF-8');
                return str_contains($code, $needle)
                    || str_contains($displayName, $needle)
                    || str_contains($phone, $needle);
            })->values();
        }

        return view('doctor.dashboard', [
            'appointments' => $appointments,
            'doctor' => $doctor,
            'confirmed' => $confirmed,
            'checkedIn' => $checkedIn,
            'pending' => $pending,
            'completed' => $completed,
            'total' => $total,
            'selectedDate' => $selectedDate,
            'statusFilter' => $statusFilter,
            'search' => $rawSearch,
            'error' => null,
        ]);
    }
}
