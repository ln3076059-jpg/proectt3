<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    // Hiển thị trang Dashboard dành cho Admin với thống kê tổng quan và doanh thu
    public function index()
    {
        // Lấy ngày hiện tại (chỉ phần ngày, không bao gồm giờ)
        $today = Carbon::today();

        // =====================================================
        // 📊 PHẦN 1: THỐNG KÊ CHÍNH TRÊN DASHBOARD (F12)
        // =====================================================
        $pendingCount = Appointment::where('status', Appointment::STATUS_PENDING)->count();
        $confirmedCount = Appointment::where('status', Appointment::STATUS_CONFIRMED)->count();
        $completedCount = Appointment::where('status', Appointment::STATUS_COMPLETED)->count();
        $cancelledCount = Appointment::where('status', Appointment::STATUS_CANCELLED)->count();

        $stats = [
            'total_doctors' => Doctor::where('is_active', true)->count(),
            'total_patients' => \App\Models\Patient::count(),
            'total_departments' => Department::count(),
            'total_services' => Service::count(),
            'total_appointments' => Appointment::count(),
            'today_appointments' => Appointment::whereDate('appointment_date', $today)->count(),
            'pending_appointments' => $pendingCount,
            'confirmed_appointments' => $confirmedCount,
            'completed_appointments' => $completedCount,
            'cancelled_appointments' => $cancelledCount,
            'paid_appointments' => Appointment::where('payment_status', Appointment::PAYMENT_SUCCESS)->count(),
            'pending_refunds' => Appointment::where('refund_status', Appointment::REFUND_PENDING)->count(),
            'low_stock_medicines' => \App\Models\Medicine::whereRaw('stock <= COALESCE(NULLIF(min_stock, 0), 15)')->count(),
            'pending_lab_tests' => \App\Models\LabTest::where('status', '!=', 'completed')->whereNull('image')->count(),
        ];

        // Thống kê phân bố trạng thái lịch hẹn
        $statusBreakdown = [
            'labels' => ['Chờ xác nhận', 'Đã xác nhận', 'Đã hoàn thành', 'Đã hủy'],
            'data' => [$pendingCount, $confirmedCount, $completedCount, $cancelledCount],
        ];

        // Thống kê lịch hẹn theo Chuyên khoa (F12) - eager load count cho cả doctors và appointments
        $departmentStats = Department::withCount(['appointments', 'doctors'])->get();

        // Thống kê Top 5 Bác sĩ có nhiều lịch hẹn nhất (F12)
        $topDoctors = Doctor::with(['user', 'department'])
            ->withCount('appointments')
            ->orderByDesc('appointments_count')
            ->limit(5)
            ->get();

        // =====================================================
        // 📅 PHẦN 2: DANH SÁCH LỊCH HẸN GẦN NHẤT (ƯU TIÊN ĐẶT MỚI NHẤT)
        // =====================================================
        $recentAppointments = Appointment::with([
            'patient',      // Quan hệ với bệnh nhân
            'doctor.user',  // Quan hệ với bác sĩ và thông tin user của bác sĩ
            'service',      // Quan hệ với dịch vụ khám bệnh
            'timeSlot',
        ])
            ->orderByDesc('id') // Ưu tiên lịch hẹn vừa đặt mới nhất lên đầu
            ->limit(10)
            ->get();

        // =====================================================
        // 💰 PHẦN 3: THỐNG KÊ DOANH THU
        // =====================================================
        // Tính mốc thời gian để lọc dữ liệu thống kê
        $start7Days = Carbon::now()->subDays(6)->startOfDay();     // 7 ngày gần nhất
        $start12Months = Carbon::now()->subMonths(11)->startOfMonth(); // 12 tháng gần nhất

        // ===== 💵 Tổng doanh thu toàn hệ thống =====
        $hasTotalAmount = Schema::hasColumn('appointments', 'total_amount');
        $hasTotal = Schema::hasColumn('appointments', 'total');
        if ($hasTotalAmount && $hasTotal) {
            $priceExpr = 'COALESCE(appointments.total_amount, appointments.total, services.price)';
        } elseif ($hasTotalAmount) {
            $priceExpr = 'COALESCE(appointments.total_amount, services.price)';
        } elseif ($hasTotal) {
            $priceExpr = 'COALESCE(appointments.total, services.price)';
        } else {
            $priceExpr = 'services.price';
        }

        $dateExpr = Schema::hasColumn('appointments', 'paid_at')
            ? 'COALESCE(appointments.paid_at, appointments.appointment_date)'
            : 'appointments.appointment_date';

        $baseRevenueQuery = Appointment::where('appointments.payment_status', Appointment::PAYMENT_SUCCESS)
            ->when(Schema::hasColumn('appointments', 'refund_status'), function ($q) {
                $q->where(function ($sub) {
                    $sub->whereNull('appointments.refund_status')
                        ->orWhere('appointments.refund_status', '!=', Appointment::REFUND_REFUNDED);
                });
            })
            ->join('services', 'appointments.service_id', '=', 'services.id');

        $totalRevenue = (clone $baseRevenueQuery)
            ->selectRaw('SUM(' . $priceExpr . ') as total')
            ->value('total') ?: 0;

        // ===== 📈 Doanh thu theo NGÀY (7 ngày gần nhất) =====
        $daily = (clone $baseRevenueQuery)
            ->whereRaw($dateExpr . ' >= ?', [$start7Days])
            ->selectRaw('DATE(' . $dateExpr . ') as date, SUM(' . $priceExpr . ') as total')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // ===== 📊 Doanh thu theo THÁNG (12 tháng gần nhất) =====
        $monthly = (clone $baseRevenueQuery)
            ->whereRaw($dateExpr . ' >= ?', [$start12Months])
            ->selectRaw('DATE_FORMAT(' . $dateExpr . ', "%Y-%m") as month, SUM(' . $priceExpr . ') as total')
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        // ===== 📆 Doanh thu theo NĂM =====
        $yearly = (clone $baseRevenueQuery)
            ->selectRaw('YEAR(' . $dateExpr . ') as year, SUM(' . $priceExpr . ') as total')
            ->groupBy('year')
            ->orderBy('year', 'asc')
            ->get();

        // =====================================================
        // 📤 PHẦN 4: TRẢ DỮ LIỆU RA VIEW
        // =====================================================
        // Truyền toàn bộ dữ liệu (thống kê + lịch hẹn + doanh thu)
        // sang view "admin.dashboard.index" để hiển thị biểu đồ & bảng thống kê
        return view('admin.dashboard.index', compact(
            'stats',             // Thống kê tổng quan (F12)
            'statusBreakdown',   // Phân bố trạng thái đặt lịch (F12)
            'departmentStats',   // Thống kê theo chuyên khoa (F12)
            'topDoctors',        // Thống kê theo bác sĩ (F12)
            'recentAppointments',// Danh sách lịch hẹn mới nhất
            'totalRevenue',      // Tổng doanh thu
            'daily',             // Doanh thu theo ngày
            'monthly',           // Doanh thu theo tháng
            'yearly'             // Doanh thu theo năm
        ));
    }

    /**
     * Báo cáo thống kê y tế chuyên sâu (F12) có bộ lọc thời gian
     */
    public function statistics(\Illuminate\Http\Request $request)
    {
        $fromDate = $request->query('from_date', Carbon::now()->startOfMonth()->toDateString());
        $toDate = $request->query('to_date', Carbon::now()->toDateString());

        $appointmentQuery = Appointment::whereBetween('appointment_date', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59']);

        $totalAppointments = (clone $appointmentQuery)->count();
        $pendingCount = (clone $appointmentQuery)->where('status', Appointment::STATUS_PENDING)->count();
        $completedCount = (clone $appointmentQuery)->where('status', Appointment::STATUS_COMPLETED)->count();
        $confirmedCount = (clone $appointmentQuery)->whereIn('status', [Appointment::STATUS_CONFIRMED, Appointment::STATUS_CHECKED_IN])->count();
        $cancelledCount = (clone $appointmentQuery)->where('status', Appointment::STATUS_CANCELLED)->count();

        $hasTotalAmount = Schema::hasColumn('appointments', 'total_amount');
        $hasTotal = Schema::hasColumn('appointments', 'total');
        if ($hasTotalAmount && $hasTotal) {
            $priceExpr = 'COALESCE(appointments.total_amount, appointments.total, services.price)';
        } elseif ($hasTotalAmount) {
            $priceExpr = 'COALESCE(appointments.total_amount, services.price)';
        } elseif ($hasTotal) {
            $priceExpr = 'COALESCE(appointments.total, services.price)';
        } else {
            $priceExpr = 'services.price';
        }

        $totalRevenue = (clone $appointmentQuery)
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->where('appointments.payment_status', Appointment::PAYMENT_SUCCESS)
            ->when(Schema::hasColumn('appointments', 'refund_status'), function ($q) {
                $q->where(function ($sub) {
                    $sub->whereNull('appointments.refund_status')
                        ->orWhere('appointments.refund_status', '!=', Appointment::REFUND_REFUNDED);
                });
            })
            ->sum(\Illuminate\Support\Facades\DB::raw($priceExpr)) ?: 0;

        $patientCount = \App\Models\Patient::count();
        $doctorCount = Doctor::where('is_active', true)->count();
        $deptStats = Department::withCount([
            'doctors',
            'appointments' => function ($q) use ($fromDate, $toDate) {
                $q->whereBetween('appointment_date', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59']);
            },
        ])->get();

        return view('admin.statistics', compact(
            'totalAppointments',
            'pendingCount',
            'completedCount',
            'confirmedCount',
            'cancelledCount',
            'totalRevenue',
            'patientCount',
            'doctorCount',
            'deptStats',
            'fromDate',
            'toDate'
        ));
    }
}

