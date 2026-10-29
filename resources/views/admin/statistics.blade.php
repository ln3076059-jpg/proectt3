@extends('layouts.admin')

@section('title', 'Báo cáo & Thống kê Y tế')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-1 text-gray-800 font-weight-bold">
            <i class="fas fa-chart-line text-primary mr-2"></i>Báo cáo & Thống kê Y tế Thái Thịnh (F12)
        </h1>
        <p class="text-muted small mb-0">
            Thống kê dữ liệu từ ngày <strong>{{ \Carbon\Carbon::parse($fromDate)->format('d/m/Y') }}</strong> đến ngày <strong>{{ \Carbon\Carbon::parse($toDate)->format('d/m/Y') }}</strong>
        </p>
    </div>
    <div class="mt-2 mt-sm-0 d-flex align-items-center" style="gap: 8px;">
        <button type="button" id="exportStatsCsvBtn" class="btn btn-sm btn-outline-success shadow-sm">
            <i class="fas fa-file-csv fa-sm mr-1"></i> Xuất CSV thống kê
        </button>
        <button onclick="window.print()" class="btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-download fa-sm text-white-50 mr-1"></i> Xuất báo cáo (In PDF)
        </button>
    </div>
</div>

<!-- Date Filter Card -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-wrap align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-calendar-alt mr-1"></i> Khoảng thời gian thống kê
        </h6>
        <div class="mt-2 mt-sm-0">
            @php
                $todayStr = now()->toDateString();
                $last7Str = now()->subDays(6)->toDateString();
                $monthStartStr = now()->startOfMonth()->toDateString();
                $yearStartStr = now()->startOfYear()->toDateString();
            @endphp
            <a href="{{ route('admin.statistics.index', ['from_date' => $todayStr, 'to_date' => $todayStr]) }}"
               class="btn btn-sm {{ $fromDate === $todayStr && $toDate === $todayStr ? 'btn-primary' : 'btn-outline-secondary' }} mr-1">
                Hôm nay
            </a>
            <a href="{{ route('admin.statistics.index', ['from_date' => $last7Str, 'to_date' => $todayStr]) }}"
               class="btn btn-sm {{ $fromDate === $last7Str && $toDate === $todayStr ? 'btn-primary' : 'btn-outline-secondary' }} mr-1">
                7 ngày qua
            </a>
            <a href="{{ route('admin.statistics.index', ['from_date' => $monthStartStr, 'to_date' => $todayStr]) }}"
               class="btn btn-sm {{ $fromDate === $monthStartStr && $toDate === $todayStr ? 'btn-primary' : 'btn-outline-secondary' }} mr-1">
                Tháng này
            </a>
            <a href="{{ route('admin.statistics.index', ['from_date' => $yearStartStr, 'to_date' => $todayStr]) }}"
               class="btn btn-sm {{ $fromDate === $yearStartStr && $toDate === $todayStr ? 'btn-primary' : 'btn-outline-secondary' }}">
                Năm nay
            </a>
        </div>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.statistics.index') }}" method="GET" class="form-row align-items-end">
            <div class="col-md-5 mb-2">
                <label class="font-weight-bold text-gray-700 small">TỪ NGÀY</label>
                <input type="date" name="from_date" class="form-control" value="{{ $fromDate }}">
            </div>
            <div class="col-md-5 mb-2">
                <label class="font-weight-bold text-gray-700 small">ĐẾN NGÀY</label>
                <input type="date" name="to_date" class="form-control" value="{{ $toDate }}">
            </div>
            <div class="col-md-2 mb-2">
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-filter mr-1"></i> Áp dụng
                </button>
            </div>
        </form>
    </div>
</div>

<!-- 4 Thẻ KPI chính -->
<div class="row">
    <!-- Doanh thu tổng -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Doanh thu trong kỳ</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalRevenue, 0, ',', '.') }} VNĐ</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tỷ lệ khám thành công -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Lượt khám hoàn thành</div>
                        @php
                            $completionRate = $totalAppointments > 0 ? round(($completedCount / $totalAppointments) * 100, 1) : 0;
                        @endphp
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ $completedCount }} / {{ $totalAppointments }} ca
                            <span class="badge badge-success ml-1" style="font-size: 11px;">{{ $completionRate }}%</span>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-double fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tổng số bệnh nhân -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Hồ sơ bệnh nhân</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $patientCount }} người</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-injured fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tổng số bác sĩ trực -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Đội ngũ bác sĩ trực</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $doctorCount }} Bác sĩ</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-md fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Biểu đồ phân bổ chuyên khoa & trạng thái -->
<div class="row">
    <!-- Bar Chart -->
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-chart-bar mr-1"></i> Tương quan Số lượng Bác sĩ & Lượt khám theo Chuyên khoa
                </h6>
            </div>
            <div class="card-body">
                <div class="chart-bar" style="height: 320px;">
                    <canvas id="myBarChart"></canvas>
                </div>
                <hr>
                <div class="small text-muted">So sánh năng lực nhân sự bác sĩ và lưu lượng ca khám thực tế theo từng chuyên khoa trong kỳ.</div>
            </div>
        </div>
    </div>

    <!-- Donut Chart -->
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-chart-pie mr-1"></i> Cơ cấu trạng thái lịch hẹn
                </h6>
            </div>
            <div class="card-body">
                <div class="chart-pie pt-4 pb-2" style="height: 250px;">
                    <canvas id="myPieChart"></canvas>
                </div>
                <div class="mt-4 text-center small">
                    <span class="mr-2"><i class="fas fa-circle text-warning"></i> Chờ duyệt ({{ $pendingCount ?? 0 }})</span>
                    <span class="mr-2"><i class="fas fa-circle text-primary"></i> Đã xác nhận ({{ $confirmedCount }})</span>
                    <span class="mr-2"><i class="fas fa-circle text-success"></i> Hoàn thành ({{ $completedCount }})</span>
                    <span class="mr-2"><i class="fas fa-circle text-danger"></i> Đã hủy ({{ $cancelledCount }})</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bảng chi tiết 5 cột thống kê chuyên khoa -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-hospital mr-1"></i> Chi tiết quy mô hoạt động theo từng Chuyên khoa
        </h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                <thead class="bg-light">
                    <tr>
                        <th class="text-center" style="width: 60px;">STT</th>
                        <th>Tên chuyên khoa</th>
                        <th class="text-center">Số lượng Bác sĩ</th>
                        <th class="text-center">Số ca khám trong kỳ</th>
                        <th class="text-center" style="width: 140px;">Chức năng</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($deptStats as $dept)
                        <tr>
                            <td class="text-center font-weight-bold text-gray-600 align-middle">{{ $loop->iteration }}</td>
                            <td class="font-weight-bold text-primary align-middle">
                                <i class="fas fa-clinic-medical mr-2 text-info"></i>{{ $dept->name }}
                                <span class="badge badge-light border text-muted ml-1">#KHOA-{{ str_pad($dept->id, 3, '0', STR_PAD_LEFT) }}</span>
                            </td>
                            <td class="text-center align-middle font-weight-bold text-gray-800">
                                <span class="badge badge-info px-2 py-1">{{ $dept->doctors_count }} Bác sĩ</span>
                            </td>
                            <td class="text-center align-middle font-weight-bold text-primary">
                                {{ $dept->appointments_count ?? 0 }} ca hẹn
                            </td>
                            <td class="text-center align-middle">
                                <a href="{{ route('admin.departments.edit', $dept) }}" class="btn btn-sm btn-info btn-circle mr-1" title="Chi tiết khoa">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.schedules.index', ['department_id' => $dept->id]) }}" class="btn btn-sm btn-primary btn-circle" title="Xem lịch ca">
                                    <i class="fas fa-calendar-alt"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        var ctxBar = document.getElementById("myBarChart");
        if (ctxBar) {
            new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($deptStats->pluck('name')) !!},
                    datasets: [
                        {
                            label: "Số bác sĩ",
                            backgroundColor: "#4e73df",
                            hoverBackgroundColor: "#2e59d9",
                            borderColor: "#4e73df",
                            data: {!! json_encode($deptStats->pluck('doctors_count')) !!},
                        },
                        {
                            label: "Lượt hẹn trong kỳ",
                            backgroundColor: "#1cc88a",
                            hoverBackgroundColor: "#17a673",
                            borderColor: "#1cc88a",
                            data: {!! json_encode($deptStats->pluck('appointments_count')) !!},
                        }
                    ],
                },
                options: {
                    maintainAspectRatio: false,
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                stepSize: 1
                            }
                        }]
                    }
                }
            });
        }

        var ctxPie = document.getElementById("myPieChart");
        if (ctxPie) {
            new Chart(ctxPie, {
                type: 'doughnut',
                data: {
                    labels: ["Chờ duyệt", "Đã xác nhận", "Hoàn thành", "Đã hủy"],
                    datasets: [{
                        data: [{{ $pendingCount ?? 0 }}, {{ $confirmedCount }}, {{ $completedCount }}, {{ $cancelledCount }}],
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
                    },
                    legend: {
                        display: false
                    },
                    cutoutPercentage: 70,
                },
            });
        }

        document.getElementById('exportStatsCsvBtn')?.addEventListener('click', function () {
            var rows = [['STT', 'Tên chuyên khoa', 'Số lượng Bác sĩ', 'Số ca khám trong kỳ']];
            document.querySelectorAll('table.table tbody tr').forEach(function (tr) {
                var cols = tr.querySelectorAll('td');
                if (cols.length >= 4) {
                    rows.push([
                        cols[0].innerText.trim(),
                        cols[1].innerText.replace(/\s+/g, ' ').trim(),
                        cols[2].innerText.replace(/\s+/g, ' ').trim(),
                        cols[3].innerText.replace(/\s+/g, ' ').trim()
                    ]);
                }
            });
            var csvContent = '\uFEFF' + rows.map(function (r) {
                return r.map(function (c) { return '"' + String(c).replace(/"/g, '""') + '"'; }).join(',');
            }).join('\n');
            var blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            var link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'bao-cao-thong-ke-y-te-{{ $fromDate }}-den-{{ $toDate }}.csv';
            link.click();
        });
    });
</script>
@endpush
