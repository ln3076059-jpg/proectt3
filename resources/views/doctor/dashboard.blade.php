@extends('layouts.doctor')

@section('title', 'Bảng điều khiển Bác sĩ')

@section('content')
@php
    $activeDoctorTab = request('tab', 'overview');
@endphp
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        @php
            $docDisplayName = $doctor->user->name ?? 'Bác sĩ';
            if (!\Illuminate\Support\Str::startsWith($docDisplayName, 'BS.')) {
                $docDisplayName = 'BS. ' . $docDisplayName;
            }
        @endphp
        <h1 class="h3 mb-1 text-gray-800 font-weight-bold">
            @if($activeDoctorTab === 'queue')
                <i class="fas fa-user-check text-primary mr-2"></i>Tiếp nhận &amp; Điều phối Hàng đợi Bệnh nhân hôm nay
            @else
                <i class="fas fa-stethoscope text-primary mr-2"></i>Bảng điều khiển Bác sĩ: {{ $docDisplayName }}
            @endif
        </h1>
        <p class="text-muted small mb-0">
            Chuyên khoa: <span class="font-weight-bold text-primary">{{ $doctor->department->name ?? 'Đa khoa' }}</span>
            | Buồng khám: <span class="font-weight-bold text-info">{{ $doctor->room_number ?? 'P.201 - Tầng 2' }}</span>
            | CCHN: <span class="font-weight-bold">{{ $doctor->license_number ?? '012456/HN-CCHN' }}</span>
        </p>
    </div>
    <div class="mt-2 mt-sm-0 d-flex align-items-center">
        <a href="{{ route('doctor.dashboard') }}" class="btn btn-sm {{ $activeDoctorTab !== 'queue' ? 'btn-primary' : 'btn-outline-primary' }} shadow-sm mr-2">
            <i class="fas fa-tachometer-alt fa-sm mr-1"></i> Theo dõi ca trực
        </a>
        <a href="{{ route('doctor.dashboard', ['tab' => 'queue']) }}" class="btn btn-sm {{ $activeDoctorTab === 'queue' ? 'btn-primary' : 'btn-outline-primary' }} shadow-sm mr-2">
            <i class="fas fa-clipboard-list fa-sm mr-1"></i> Tiếp nhận hàng đợi ({{ $total }})
        </a>
        <button type="button" id="toggleAutoRefreshBtn" class="btn btn-sm btn-outline-success shadow-sm mr-2" title="Tự động làm mới danh sách chờ mỗi 30 giây">
            <i class="fas fa-sync-alt fa-sm mr-1"></i> Tự động đồng bộ: <span id="autoRefreshState">Bật (30s)</span>
        </button>
    </div>
</div>

@if(!empty($error))
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
        <i class="fas fa-exclamation-triangle mr-2"></i>{{ $error }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

<!-- 4 Thẻ KPI chính chuẩn SB Admin 2 -->
<div class="row {{ $activeDoctorTab === 'queue' ? 'd-none' : '' }}">
    <!-- Tổng lịch hẹn hôm nay -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Lịch hẹn trong ngày
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $total }} lịch hẹn</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Đã tiếp nhận (Check-in QR) -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Đã tiếp nhận (Check-in)
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $checkedIn }} bệnh nhân</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-qrcode fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Đã khám hoàn thành -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Đã khám xong
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $completed }} ca</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-double fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Đang chờ khám -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Chờ khám tiếp theo
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $confirmed }} người</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-clock fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@php
    $nextCheckedInAppt = $appointments->where('status', 'checked_in')->sortBy('checked_in_at')->first();
@endphp
@if($nextCheckedInAppt)
    @php
        $nextIsFam = ($nextCheckedInAppt->booking_for === 'relative' && !empty($nextCheckedInAppt->relative_name));
        $nextPatientName = $nextIsFam ? $nextCheckedInAppt->relative_name : ($nextCheckedInAppt->patient->name ?? optional($nextCheckedInAppt->patient->user)->name ?? 'Bệnh nhân');
        $nextCode = $nextCheckedInAppt->appointment_code ?: ('TT-' . str_pad($nextCheckedInAppt->id, 5, '0', STR_PAD_LEFT));
    @endphp
    <div class="card border-left-info shadow mb-4" style="background: linear-gradient(90deg, #F0F9FF 0%, #FFFFFF 100%);">
        <div class="card-body py-3 d-flex align-items-center justify-content-between flex-wrap">
            <div class="d-flex align-items-center mb-2 mb-md-0">
                <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center mr-3 shadow-sm" style="width: 46px; height: 46px;">
                    <i class="fas fa-bell fa-lg"></i>
                </div>
                <div>
                    <div class="text-xs font-weight-bold text-info text-uppercase">
                        Hàng đợi ưu tiên — Bệnh nhân đã Check-in QR đang chờ trước buồng khám ({{ $checkedIn }} người)
                    </div>
                    <div class="h6 mb-0 font-weight-bold text-gray-900 mt-1">
                        Ca kế tiếp: <span class="text-primary">{{ $nextPatientName }}</span>
                        <span class="badge badge-info ml-1">#{{ $nextCode }}</span>
                        <small class="text-muted ml-2">• Dịch vụ: {{ $nextCheckedInAppt->service->name ?? 'Khám chuyên khoa' }}</small>
                    </div>
                </div>
            </div>
            <div>
                <a href="{{ route('doctor.patient.record', $nextCheckedInAppt) }}" class="btn btn-info font-weight-bold shadow-sm">
                    <i class="fas fa-stethoscope mr-1"></i> Gọi vào khám &amp; Mở EMR ngay
                </a>
            </div>
        </div>
    </div>
@endif

<!-- Search & Date Filter Card -->
<div class="card shadow mb-4 {{ $activeDoctorTab === 'queue' ? '' : 'd-none' }}">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between flex-wrap">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-calendar-day mr-1"></i> Điều phối & Tra cứu lịch khám theo ngày
        </h6>
        <div class="mt-2 mt-sm-0">
            @php
                $yesterdayStr = \Carbon\Carbon::parse($selectedDate)->subDay()->format('Y-m-d');
                $todayStr = now()->format('Y-m-d');
                $tomorrowStr = \Carbon\Carbon::parse($selectedDate)->addDay()->format('Y-m-d');
            @endphp
            <div class="btn-group btn-group-sm">
                <a href="{{ route('doctor.dashboard', ['date' => $yesterdayStr, 'status' => request('status')]) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-chevron-left mr-1"></i> Hôm trước
                </a>
                <a href="{{ route('doctor.dashboard', ['date' => $todayStr, 'status' => request('status')]) }}" class="btn {{ $selectedDate === $todayStr ? 'btn-primary' : 'btn-outline-primary' }}">
                    Hôm nay ({{ now()->format('d/m') }})
                </a>
                <a href="{{ route('doctor.dashboard', ['date' => $tomorrowStr, 'status' => request('status')]) }}" class="btn btn-outline-secondary">
                    Ngày kế <i class="fas fa-chevron-right ml-1"></i>
                </a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('doctor.dashboard') }}" class="form-row align-items-end">
            <div class="col-md-3 mb-2">
                <label class="font-weight-bold text-gray-700 small">CHỌN NGÀY TRỰC KHÁM</label>
                <input type="date" name="date" value="{{ $selectedDate }}" class="form-control">
            </div>
            <div class="col-md-3 mb-2">
                <label class="font-weight-bold text-gray-700 small">QUÉT MÃ QR / TÌM BỆNH NHÂN</label>
                <input type="text" name="search" value="{{ $search ?? request('search') }}" class="form-control" placeholder="Mã phiếu TT-..., tên BN, SĐT...">
            </div>
            <div class="col-md-4 mb-2">
                <label class="font-weight-bold text-gray-700 small">LỌC NHANH TRẠNG THÁI CA KHÁM</label>
                <div class="d-flex flex-wrap">
                    <a href="{{ route('doctor.dashboard', ['date' => $selectedDate]) }}" class="btn btn-sm mr-1 mb-1 {{ empty($statusFilter) ? 'btn-primary' : 'btn-light border' }}">
                        Tất cả ({{ $total }})
                    </a>
                    <a href="{{ route('doctor.dashboard', ['date' => $selectedDate, 'status' => 'confirmed']) }}" class="btn btn-sm mr-1 mb-1 {{ ($statusFilter ?? '') === 'confirmed' ? 'btn-warning text-dark font-weight-bold' : 'btn-light border' }}">
                        Chờ tiếp đón ({{ $confirmed }})
                    </a>
                    <a href="{{ route('doctor.dashboard', ['date' => $selectedDate, 'status' => 'checked_in']) }}" class="btn btn-sm mr-1 mb-1 {{ ($statusFilter ?? '') === 'checked_in' ? 'btn-info font-weight-bold' : 'btn-light border' }}">
                        Đã Check-in QR ({{ $checkedIn }})
                    </a>
                    <a href="{{ route('doctor.dashboard', ['date' => $selectedDate, 'status' => 'completed']) }}" class="btn btn-sm mb-1 {{ ($statusFilter ?? '') === 'completed' ? 'btn-success font-weight-bold' : 'btn-light border' }}">
                        Đã khám ({{ $completed }})
                    </a>
                </div>
            </div>
            <div class="col-md-2 mb-2">
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-search mr-1"></i> Tra cứu
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Thẻ Tổng quan Ca trực Lâm sàng (Hiển thị trên màn hình Bảng điều khiển Tổng quan) -->
<div class="row {{ $activeDoctorTab === 'queue' ? 'd-none' : '' }}">
    <div class="col-lg-6 mb-4">
        <div class="card shadow h-100">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-clinic-medical mr-1"></i> Thông tin Ca trực &amp; Buồng khám Chuyên khoa
                </h6>
                <span class="badge badge-success px-2 py-1">Đang trực khám</span>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between py-2 border-bottom small">
                    <span class="text-muted">Bác sĩ phụ trách:</span>
                    <strong class="text-dark">{{ $docDisplayName }} ({{ $doctor->specialization ?? 'Chuyên khoa' }})</strong>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom small">
                    <span class="text-muted">Vị trí buồng khám:</span>
                    <strong class="text-primary">{{ $doctor->room_number ?? 'P.201 - Tầng 2' }} ({{ $doctor->department->name ?? 'Khoa Khám bệnh' }})</strong>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom small">
                    <span class="text-muted">Khung giờ làm việc:</span>
                    <strong class="text-dark">Ca sáng (07:30 - 11:30) &amp; Ca chiều (13:30 - 17:00)</strong>
                </div>
                <div class="d-flex justify-content-between pt-2 small">
                    <span class="text-muted">Tiến độ hoàn thành trong ngày:</span>
                    <strong class="text-success">{{ $completed }}/{{ max(1, $total) }} ca đã kết luận EMR</strong>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6 mb-4">
        <div class="card shadow h-100">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-bolt mr-1 text-warning"></i> Lối tắt Nghiệp vụ Lâm sàng
                </h6>
            </div>
            <div class="card-body d-flex flex-column justify-content-between">
                <p class="small text-muted mb-3">
                    Hệ thống tự động ưu tiên bệnh nhân đã quét mã QR Check-in tại Kiosk tầng 1 lên đầu hàng đợi tiếp đón. Bác sĩ có thể chuyển sang màn hình <strong>Hàng đợi tiếp đón</strong> để lọc và gọi bệnh nhân vào phòng.
                </p>
                <div class="d-flex flex-wrap" style="gap: 10px;">
                    <a href="{{ route('doctor.dashboard', ['tab' => 'queue']) }}" class="btn btn-primary btn-sm font-weight-bold shadow-sm px-3 py-2">
                        <i class="fas fa-clipboard-list mr-1"></i> Mở Hàng đợi Tiếp đón ({{ $total }} ca)
                    </a>
                    <a href="{{ route('doctor.patient.history') }}" class="btn btn-outline-primary btn-sm font-weight-bold px-3 py-2">
                        <i class="fas fa-file-medical-alt mr-1"></i> Lịch sử Bệnh án EMR
                    </a>
                    <a href="{{ route('doctor.patients.index') }}" class="btn btn-outline-secondary btn-sm font-weight-bold px-3 py-2">
                        <i class="fas fa-users mr-1"></i> Danh sách Bệnh nhân
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- DataTales (ĐÚNG 5 CỘT BAO GỒM 1 CỘT CHỨC NĂNG) -->
<div class="card shadow mb-4 {{ $activeDoctorTab === 'queue' ? '' : 'd-none' }}">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-clipboard-list mr-1"></i> Danh sách bệnh nhân tiếp nhận khám ngày {{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}
        </h6>
        <span class="badge badge-primary px-3 py-2">Tổng số: {{ $appointments->count() }} ca khám</span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 70px;">STT & Mã</th>
                        <th>Bệnh nhân & Thông tin liên lạc</th>
                        <th>Ca khám & Dịch vụ y tế</th>
                        <th style="width: 170px;" class="text-center">Trạng thái & Tiếp nhận</th>
                        <th class="text-center" style="width: 170px;">Chức năng</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($appointments as $appointment)
                        @php
                            $isFamily = ($appointment->booking_for === 'relative' && !empty($appointment->relative_name));
                            $patientDisplay = $isFamily ? $appointment->relative_name : ($appointment->patient->name ?? optional($appointment->patient->user)->name ?? 'Bệnh nhân');
                            $rawGender = $isFamily ? $appointment->relative_gender : ($appointment->patient->gender ?? optional($appointment->patient->user)->gender ?? 'N/A');
                            $genderLower = mb_strtolower(trim((string) $rawGender), 'UTF-8');
                            $genderLabel = in_array($genderLower, ['male', 'nam']) ? 'Nam' : (in_array($genderLower, ['female', 'nữ', 'nu']) ? 'Nữ' : 'Khác');
                            $dob = $isFamily ? $appointment->relative_dob : ($appointment->patient->birthdate ?? optional($appointment->patient->user)->birthdate ?? null);
                            $age = $dob ? \Carbon\Carbon::parse($dob)->age : null;
                            $phoneDisplay = $appointment->patient->phone ?? optional($appointment->patient->user)->phone ?? 'Chưa cập nhật';
                        @endphp
                        <tr>
                            <!-- Cột 1: STT & Mã lịch hẹn -->
                            <td class="text-center align-middle">
                                <span class="font-weight-bold text-gray-700">#{{ $loop->iteration }}</span>
                                <div class="mt-1">
                                    <span class="badge badge-secondary">
                                        {{ $appointment->appointment_code ?? '#'.str_pad($appointment->id, 5, '0', STR_PAD_LEFT) }}
                                    </span>
                                </div>
                            </td>

                            <!-- Cột 2: Bệnh nhân & Liên lạc -->
                            <td class="align-middle">
                                <div class="font-weight-bold text-primary">
                                    <i class="fas fa-user mr-1 text-info"></i>{{ $patientDisplay }}
                                    @if($isFamily)
                                        <span class="badge badge-warning text-dark ml-1">Người thân ({{ $appointment->relative_relationship ?? 'Khám hộ' }})</span>
                                    @endif
                                </div>
                                <div class="small text-gray-600 mt-1">
                                    <span>Giới tính: <strong>{{ $genderLabel }}</strong></span>
                                    @if($age !== null)
                                        | <span>Tuổi: <strong>{{ $age }}</strong></span>
                                    @endif
                                    | <span>SĐT: <strong class="text-gray-800">{{ $phoneDisplay }}</strong></span>
                                </div>
                            </td>

                            <!-- Cột 3: Ca khám & Dịch vụ -->
                            <td class="align-middle">
                                <div class="font-weight-bold text-gray-800">
                                    <i class="fas fa-stethoscope text-primary mr-1"></i>{{ $appointment->service->name ?? 'Khám chuyên khoa' }}
                                </div>
                                <div class="small text-muted mt-1">
                                    <i class="far fa-clock mr-1 text-info"></i>
                                    {{ $appointment->medical_examination ?? \Carbon\Carbon::parse($appointment->appointment_date)->format('H:i') }}
                                </div>
                            </td>

                            <!-- Cột 4: Trạng thái & Check-in -->
                            <td class="text-center align-middle">
                                @if($appointment->status === 'checked_in')
                                    <span class="badge badge-info px-2 py-1">
                                        <i class="fas fa-check-circle mr-1"></i>Đã tiếp nhận (QR)
                                    </span>
                                    @if($appointment->checked_in_at)
                                        <div class="small text-muted mt-1">{{ \Carbon\Carbon::parse($appointment->checked_in_at)->format('H:i') }}</div>
                                    @endif
                                @elseif($appointment->status === 'completed')
                                    <span class="badge badge-success px-2 py-1">
                                        <i class="fas fa-check-double mr-1"></i>Đã khám xong
                                    </span>
                                @elseif($appointment->status === 'confirmed')
                                    <span class="badge badge-warning text-dark px-2 py-1">
                                        <i class="fas fa-user-clock mr-1"></i>Chờ vào khám
                                    </span>
                                @elseif($appointment->status === 'cancelled')
                                    <span class="badge badge-danger px-2 py-1">
                                        <i class="fas fa-times-circle mr-1"></i>Đã hủy
                                    </span>
                                @else
                                    <span class="badge badge-secondary px-2 py-1">
                                        {{ $appointment->status }}
                                    </span>
                                @endif
                            </td>

                            <!-- Cột 5: Chức năng -->
                            <td class="text-center align-middle">
                                @if($appointment->status === 'confirmed')
                                    <!-- Nút Check-in QR Tiếp nhận -->
                                    <form action="{{ route('doctor.appointments.checkin', $appointment) }}" method="POST" class="d-inline mr-1" onsubmit="return confirm('Xác nhận tiếp nhận bệnh nhân này vào phòng chờ khám?');">
                                        @csrf
                                        <button type="submit" class="btn btn-info btn-sm px-2 shadow-sm" title="Tiếp nhận bệnh nhân (Check-in QR)">
                                            <i class="fas fa-qrcode mr-1"></i> Check-in
                                        </button>
                                    </form>
                                @endif

                                <!-- Nút Khám bệnh / Ghi EMR -->
                                <a href="{{ route('doctor.patient.record', $appointment) }}" class="btn btn-primary btn-sm px-2 shadow-sm mr-1" title="Khám bệnh & Bệnh án điện tử EMR">
                                    <i class="fas fa-notes-medical mr-1"></i> {{ $appointment->status === 'completed' ? 'Xem EMR' : 'Khám' }}
                                </a>

                                @if($appointment->status === 'checked_in')
                                    <!-- Nút Hoàn thành nhanh -->
                                    <form action="{{ route('doctor.appointments.complete', $appointment) }}" method="POST" class="d-inline" onsubmit="return confirm('Đánh dấu hoàn thành ca khám này?');">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-circle btn-sm" title="Hoàn thành nhanh ca khám">
                                            <i class="fas fa-check-double"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="fas fa-calendar-times fa-2x mb-2 text-gray-400 d-block"></i>
                                Không có lịch hẹn khám nào trong ngày {{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}.
                            </td>
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
document.addEventListener('DOMContentLoaded', function () {
    const toggleBtn = document.getElementById('toggleAutoRefreshBtn');
    const stateSpan = document.getElementById('autoRefreshState');
    let isAutoRefresh = localStorage.getItem('doc_dashboard_autorefresh') !== 'off';
    let timer = null;

    function applyState() {
        if (timer) clearInterval(timer);
        if (isAutoRefresh) {
            if (stateSpan) stateSpan.textContent = 'Bật (30s)';
            if (toggleBtn) {
                toggleBtn.classList.remove('btn-outline-secondary');
                toggleBtn.classList.add('btn-outline-success');
            }
            timer = setInterval(function () {
                if (document.activeElement && document.activeElement.tagName === 'INPUT') return;
                window.location.reload();
            }, 30000);
        } else {
            if (stateSpan) stateSpan.textContent = 'Tắt';
            if (toggleBtn) {
                toggleBtn.classList.remove('btn-outline-success');
                toggleBtn.classList.add('btn-outline-secondary');
            }
        }
    }

    if (toggleBtn) {
        toggleBtn.addEventListener('click', function () {
            isAutoRefresh = !isAutoRefresh;
            localStorage.setItem('doc_dashboard_autorefresh', isAutoRefresh ? 'on' : 'off');
            applyState();
        });
        applyState();
    }
});
</script>
@endpush
