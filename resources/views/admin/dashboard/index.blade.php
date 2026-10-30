@extends('layouts.admin')

@section('title', 'Bảng điều khiển & Thống kê')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-3">
    <h1 class="h3 mb-0 text-gray-800 font-weight-bold">
        <i class="fas fa-tachometer-alt text-primary mr-2"></i>Tổng quan Phòng khám Thái Thịnh
    </h1>
    <div class="d-none d-sm-inline-block btn btn-sm btn-light shadow-sm text-gray-600 border">
        <i class="far fa-clock text-primary mr-1"></i> Cập nhật: {{ now()->format('H:i - d/m/Y') }}
    </div>
</div>

<!-- Quick Reception & Operations Toolbar (Hỗ trợ Check-in QR 1 chạm ngay từ Dashboard) -->
<div class="card shadow-sm border-0 mb-3 bg-white">
    <div class="card-body py-3">
        <div class="row align-items-center">
            <div class="col-lg-5 mb-2 mb-lg-0">
                <form action="{{ route('admin.appointments.quick_checkin') }}" method="POST" class="input-group input-group-sm">
                    @csrf
                    <input type="text" name="qr_payload" class="form-control border-primary" placeholder="Quét QR Kiosk hoặc nhập mã ca khám..." required>
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-primary font-weight-bold px-3">
                            <i class="fas fa-qrcode mr-1"></i> Check-in QR 1 Chạm
                        </button>
                    </div>
                </form>
            </div>
            <div class="col-lg-7 d-flex flex-wrap justify-content-lg-end" style="gap: 8px;">
                <a href="{{ route('admin.appointments.index', ['status' => 'pending']) }}" class="btn btn-sm btn-warning text-dark font-weight-bold shadow-sm">
                    <i class="fas fa-hourglass-half mr-1"></i> Chờ duyệt ({{ $stats['pending_appointments'] ?? 0 }})
                </a>
                <a href="{{ route('admin.appointments.index', ['date' => now()->format('Y-m-d')]) }}" class="btn btn-sm btn-info font-weight-bold shadow-sm">
                    <i class="fas fa-calendar-day mr-1"></i> Khám hôm nay ({{ $stats['today_appointments'] ?? 0 }})
                </a>
                <a href="{{ route('admin.lab_tests.index', ['status' => 'requested']) }}" class="btn btn-sm btn-outline-primary font-weight-bold">
                    <i class="fas fa-vials mr-1"></i> Chờ KQ Xét nghiệm ({{ $stats['pending_lab_tests'] ?? 0 }})
                </a>
                <a href="{{ route('admin.schedules.create') }}" class="btn btn-sm btn-outline-success font-weight-bold">
                    <i class="fas fa-calendar-plus mr-1"></i> Phân ca trực BS
                </a>
                <a href="{{ route('admin.medicines.index', ['stock_status' => 'low']) }}" class="btn btn-sm btn-outline-danger font-weight-bold">
                    <i class="fas fa-pills mr-1"></i> Thuốc sắp hết ({{ $stats['low_stock_medicines'] ?? 0 }})
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Trung tâm Cảnh báo Vận hành thông minh (Actionable Operational Alerts) --}}
@if(($stats['pending_refunds'] ?? 0) > 0 || ($stats['low_stock_medicines'] ?? 0) > 0 || ($stats['pending_lab_tests'] ?? 0) > 0)
    <div class="row mb-3">
        @if(($stats['pending_refunds'] ?? 0) > 0)
            <div class="col-md-4 mb-2">
                <div class="alert alert-danger mb-0 py-2 px-3 d-flex align-items-center justify-content-between shadow-sm">
                    <div class="small">
                        <i class="fas fa-undo-alt mr-1"></i>
                        Có <strong>{{ $stats['pending_refunds'] }} lịch hủy</strong> đang chờ hoàn tiền VNPay
                    </div>
                    <a href="{{ route('admin.appointments.index', ['status' => 'cancelled']) }}" class="btn btn-xs btn-danger py-0 px-2 font-weight-bold" style="font-size: 11.5px;">Xử lý ngay</a>
                </div>
            </div>
        @endif
        @if(($stats['low_stock_medicines'] ?? 0) > 0)
            <div class="col-md-4 mb-2">
                <div class="alert alert-warning text-dark mb-0 py-2 px-3 d-flex align-items-center justify-content-between shadow-sm">
                    <div class="small">
                        <i class="fas fa-Capsules mr-1"></i>
                        Có <strong>{{ $stats['low_stock_medicines'] }} mặt hàng thuốc</strong> tồn kho dưới ngưỡng an toàn
                    </div>
                    <a href="{{ route('admin.medicines.index') }}" class="btn btn-xs btn-warning text-dark border py-0 px-2 font-weight-bold" style="font-size: 11.5px;">Nhập kho</a>
                </div>
            </div>
        @endif
        @if(($stats['pending_lab_tests'] ?? 0) > 0)
            <div class="col-md-4 mb-2">
                <div class="alert alert-info mb-0 py-2 px-3 d-flex align-items-center justify-content-between shadow-sm">
                    <div class="small">
                        <i class="fas fa-microscope mr-1"></i>
                        Có <strong>{{ $stats['pending_lab_tests'] }} chỉ định xét nghiệm</strong> đang chờ trả kết quả
                    </div>
                    <a href="{{ route('admin.lab_tests.index', ['status' => 'requested']) }}" class="btn btn-xs btn-info py-0 px-2 font-weight-bold" style="font-size: 11.5px;">Trả KQ</a>
                </div>
            </div>
        @endif
    </div>
@endif

<!-- 4 Core Metrics Cards -->
<div class="row">
    <!-- Card 1: Bác sĩ hoạt động -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Bác sĩ hoạt động</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_doctors'] }} chuyên gia</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-md fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 2: Hồ sơ bệnh nhân -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Hồ sơ bệnh nhân</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_patients'] }} người</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-injured fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 3: Lịch khám hôm nay -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Lịch khám hôm nay</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['today_appointments'] }} ca khám</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 4: Tổng doanh thu -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Tổng doanh thu thực tế</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalRevenue ?? 0, 0, ',', '.') }} đ</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Trạng thái Lịch hẹn Row -->
<div class="row mb-2">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Chờ xác nhận (Pending)</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['pending_appointments'] }} lịch</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Đã xác nhận (Confirmed)</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['confirmed_appointments'] }} lịch</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Đã hoàn thành (Completed)</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['completed_appointments'] }} lịch</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Đã hủy / Từ chối</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['cancelled_appointments'] }} lịch</div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row">
    <!-- Area / Bar Chart: Doanh thu -->
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <!-- Card Header - Dropdown -->
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-chart-bar mr-1"></i> Biểu đồ doanh thu viện phí
                </h6>
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-primary active chart-filter-btn" data-type="daily">7 ngày</button>
                    <button type="button" class="btn btn-outline-primary chart-filter-btn" data-type="monthly">12 tháng</button>
                    <button type="button" class="btn btn-outline-primary chart-filter-btn" data-type="yearly">Theo năm</button>
                </div>
            </div>
            <!-- Card Body -->
            <div class="card-body">
                <div class="chart-area" style="position: relative; height: 320px;">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Pie / Donut Chart: Phân bố trạng thái -->
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4">
            <!-- Card Header -->
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-chart-pie mr-1"></i> Tỷ lệ trạng thái lịch hẹn
                </h6>
            </div>
            <!-- Card Body -->
            <div class="card-body">
                <div class="chart-pie pt-2 pb-2" style="position: relative; height: 260px;">
                    <canvas id="statusChart"></canvas>
                </div>
                <div class="mt-4 text-center small text-gray-600">
                    <span class="mr-2"><i class="fas fa-circle text-warning"></i> Chờ duyệt</span>
                    <span class="mr-2"><i class="fas fa-circle text-primary"></i> Đã duyệt</span>
                    <span class="mr-2"><i class="fas fa-circle text-success"></i> Đã khám</span>
                    <span class="mr-2"><i class="fas fa-circle text-danger"></i> Đã hủy</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 2 Bảng Thống kê Phân khoa & Top Bác sĩ -->
<div class="row">
    <!-- Bảng Khoa phòng -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-hospital-alt mr-1"></i> Lịch hẹn phân bổ theo Chuyên khoa
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0" width="100%" cellspacing="0">
                        <thead class="bg-light">
                            <tr>
                                <th>Chuyên khoa</th>
                                <th class="text-center">Số bác sĩ</th>
                                <th class="text-right">Số lịch đã tiếp nhận</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($departmentStats as $dept)
                                <tr>
                                    <td class="font-weight-bold text-gray-800">
                                        <i class="fas fa-stethoscope text-primary mr-2"></i>{{ $dept->name }}
                                    </td>
                                    <td class="text-center">{{ $dept->doctors_count ?? 0 }}</td>
                                    <td class="text-right font-weight-bold text-primary">
                                        {{ $dept->appointments_count ?? 0 }} ca
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">Chưa có dữ liệu</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Bảng Top Bác sĩ -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-award mr-1"></i> Top 5 Bác sĩ có nhiều lịch hẹn nhất
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0" width="100%" cellspacing="0">
                        <thead class="bg-light">
                            <tr>
                                <th>Bác sĩ chuyên khoa</th>
                                <th>Khoa trực thuộc</th>
                                <th class="text-right">Lịch phụ trách</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topDoctors as $doc)
                                <tr>
                                    <td class="font-weight-bold text-gray-800">
                                        <i class="fas fa-user-md text-info mr-2"></i>BS. {{ $doc->user->name ?? 'N/A' }}
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ $doc->department->name ?? 'Chưa phân khoa' }}</span>
                                    </td>
                                    <td class="text-right font-weight-bold text-success">
                                        {{ $doc->appointments_count ?? 0 }} lượt
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">Chưa có dữ liệu</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Appointments Table Card (ĐÚNG 5 CỘT CHUẨN SB ADMIN 2) -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-calendar-check mr-1"></i> Danh sách 10 Lịch hẹn mới nhất
        </h6>
        <a href="{{ route('admin.appointments.index') }}" class="btn btn-sm btn-primary">
            Xem tất cả lịch hẹn <i class="fas fa-arrow-right ml-1"></i>
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead class="bg-light">
                    <tr>
                        <th style="width: 140px;">Mã & Giờ khám</th>
                        <th>Bệnh nhân / Người thân</th>
                        <th>Bác sĩ & Chuyên khoa</th>
                        <th style="width: 170px;">Trạng thái & Viện phí</th>
                        <th class="text-center" style="width: 120px;">Chức năng</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentAppointments as $app)
                        <tr>
                            <!-- Cột 1: Mã & Thời gian -->
                            <td>
                                <span class="font-weight-bold text-primary">#{{ $app->appointment_code ?? $app->id }}</span>
                                <div class="small text-gray-600">
                                    <i class="far fa-calendar-alt mr-1"></i>{{ \Carbon\Carbon::parse($app->appointment_date)->format('d/m/Y') }}
                                </div>
                                <div class="small text-muted">{{ $app->timeSlot ? $app->timeSlot->formatted_time : $app->medical_examination }}</div>
                            </td>

                            <!-- Cột 2: Bệnh nhân -->
                            <td>
                                <div class="font-weight-bold text-gray-800">{{ $app->patient_display_name }}</div>
                                <div class="small text-gray-600">
                                    <i class="fas fa-phone mr-1"></i>{{ $app->patient->phone ?? 'N/A' }}
                                </div>
                                @if($app->booking_for === 'relative')
                                    <span class="badge badge-warning text-dark"><i class="fas fa-child mr-1"></i>Người thân</span>
                                @endif
                            </td>

                            <!-- Cột 3: Bác sĩ & Khoa -->
                            <td>
                                <div class="font-weight-bold text-gray-800">BS. {{ $app->doctor->user->name ?? 'N/A' }}</div>
                                <div class="small text-muted">{{ $app->doctor->department->name ?? '' }}</div>
                                <div class="small text-info">{{ $app->service->name ?? 'Khám chuyên khoa' }}</div>
                            </td>

                            <!-- Cột 4: Trạng thái & Viện phí -->
                            <td>
                                <div>
                                    @if($app->status === 'pending')
                                        <span class="badge badge-warning text-dark">Chờ xác nhận</span>
                                    @elseif($app->status === 'confirmed')
                                        <span class="badge badge-primary">Đã xác nhận</span>
                                    @elseif($app->status === 'checked_in')
                                        <span class="badge badge-info">Đã tiếp nhận (QR)</span>
                                    @elseif($app->status === 'completed')
                                        <span class="badge badge-success">Đã hoàn thành</span>
                                    @elseif($app->status === 'cancelled')
                                        <span class="badge badge-danger">Đã hủy</span>
                                    @else
                                        <span class="badge badge-secondary">{{ $app->status }}</span>
                                    @endif
                                </div>
                                <div class="small font-weight-bold text-success mt-1">
                                    {{ number_format($app->total ?? ($app->service->price ?? 0), 0, ',', '.') }} đ
                                    @if(in_array($app->payment_status, ['paid', 'success']))
                                        <span class="badge badge-success ml-1">Đã thu</span>
                                    @elseif($app->status !== 'cancelled')
                                        <span class="badge badge-light border text-muted ml-1">Chưa thu</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Cột 5: Chức năng -->
                            <td class="text-center">
                                <a href="{{ route('admin.appointments.show', $app->id) }}" class="btn btn-info btn-circle btn-sm mr-1" title="Xem chi tiết">
                                    <i class="fas fa-eye"></i>
                                </a>

                                @if($app->status === 'pending')
                                    <form action="{{ route('admin.appointments.status', $app->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="confirmed">
                                        <button type="submit" class="btn btn-primary btn-circle btn-sm mr-1" title="Duyệt lịch nhanh">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                @elseif($app->status === 'confirmed')
                                    <form action="{{ route('admin.appointments.status', $app->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="checked_in">
                                        <button type="submit" class="btn btn-primary btn-circle btn-sm mr-1" title="Check-in QR tiếp đón">
                                            <i class="fas fa-qrcode"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Chưa có lịch hẹn nào gần đây.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {
    // 1. Doughnut Chart: Trạng thái lịch hẹn
    var statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: @json($statusBreakdown['labels']),
            datasets: [{
                data: @json($statusBreakdown['data']),
                backgroundColor: ['#f6c23e', '#4e73df', '#1cc88a', '#e74a3b'],
                hoverBackgroundColor: ['#dda20a', '#2e59d9', '#17a673', '#be2617'],
                hoverBorderColor: "rgba(234, 236, 244, 1)",
            }],
        },
        options: {
            maintainAspectRatio: false,
            tooltips: {
                backgroundColor: "rgb(255,255,255)",
                bodyFontColor: "#858796",
                borderColor: '#dddfeb',
                borderWidth: 1,
                xPadding: 15,
                yPadding: 15,
                displayColors: false,
                caretPadding: 10,
            },
            legend: {
                display: false
            },
            cutoutPercentage: 70,
        },
    });

    // 2. Bar Chart: Doanh thu
    var revenueDatasets = {
        daily: {
            labels: @json($daily->pluck('date')),
            data: @json($daily->pluck('total')),
            label: 'Doanh thu (VNĐ)'
        },
        monthly: {
            labels: @json($monthly->pluck('month')),
            data: @json($monthly->pluck('total')),
            label: 'Doanh thu (VNĐ)'
        },
        yearly: {
            labels: @json($yearly->pluck('year')),
            data: @json($yearly->pluck('total')),
            label: 'Doanh thu (VNĐ)'
        }
    };

    var revCtx = document.getElementById('revenueChart').getContext('2d');
    var revenueChart = new Chart(revCtx, {
        type: 'bar',
        data: {
            labels: revenueDatasets.daily.labels,
            datasets: [{
                label: revenueDatasets.daily.label,
                backgroundColor: "#4e73df",
                hoverBackgroundColor: "#2e59d9",
                borderColor: "#4e73df",
                data: revenueDatasets.daily.data,
                maxBarThickness: 35,
            }],
        },
        options: {
            maintainAspectRatio: false,
            layout: {
                padding: { left: 10, right: 25, top: 25, bottom: 0 }
            },
            scales: {
                xAxes: [{
                    gridLines: { display: false, drawBorder: false },
                    ticks: { maxTicksLimit: 12 }
                }],
                yAxes: [{
                    ticks: {
                        beginAtZero: true,
                        maxTicksLimit: 5,
                        padding: 10,
                        callback: function(value, index, values) {
                            return value.toLocaleString('vi-VN') + ' đ';
                        }
                    },
                    gridLines: {
                        color: "rgb(234, 236, 244)",
                        zeroLineColor: "rgb(234, 236, 244)",
                        drawBorder: false,
                        borderDash: [2],
                        zeroLineBorderDash: [2]
                    }
                }],
            },
            legend: { display: false },
            tooltips: {
                titleMarginBottom: 10,
                titleFontColor: '#6e707e',
                titleFontSize: 14,
                backgroundColor: "rgb(255,255,255)",
                bodyFontColor: "#858796",
                borderColor: '#dddfeb',
                borderWidth: 1,
                xPadding: 15,
                yPadding: 15,
                displayColors: false,
                caretPadding: 10,
                callbacks: {
                    label: function(tooltipItem, chart) {
                        return 'Doanh thu: ' + Number(tooltipItem.yLabel).toLocaleString('vi-VN') + ' VNĐ';
                    }
                }
            }
        }
    });

    // Toggle thời gian lọc biểu đồ
    $('.chart-filter-btn').on('click', function () {
        $('.chart-filter-btn').removeClass('active');
        $(this).addClass('active');

        var type = $(this).data('type');
        var data = revenueDatasets[type];

        revenueChart.data.labels = data.labels;
        revenueChart.data.datasets[0].data = data.data;
        revenueChart.update();
    });
});
</script>
@endpush
