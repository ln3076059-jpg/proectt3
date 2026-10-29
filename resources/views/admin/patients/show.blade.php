@extends('layouts.admin')

@section('title', 'Hồ sơ bệnh nhân - ' . $patient->name)

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-1 text-gray-800 font-weight-bold">
            <i class="fas fa-user-injured text-primary mr-2"></i>Hồ sơ bệnh nhân: {{ $patient->name }}
        </h1>
        <p class="text-muted small mb-0">Mã hồ sơ: #PAT-{{ str_pad($patient->id, 4, '0', STR_PAD_LEFT) }} | Thông tin hành chính, thẻ BHYT và lịch sử khám bệnh</p>
    </div>
    <div class="mt-2 mt-sm-0">
        <button type="button" onclick="window.print()" class="btn btn-sm btn-outline-primary shadow-sm mr-2">
            <i class="fas fa-print fa-sm mr-1"></i> In hồ sơ hành chính
        </button>
        <a href="{{ route('admin.patients.edit', $patient->id) }}" class="btn btn-sm btn-warning shadow-sm mr-2">
            <i class="fas fa-edit fa-sm mr-1"></i> Chỉnh sửa hồ sơ
        </a>
        <a href="{{ route('admin.patients.index') }}" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50 mr-1"></i> Quay lại danh sách
        </a>
    </div>
</div>

@php
    $totalAppts = $patient->appointments->count();
    $completedAppts = $patient->appointments->where('status', 'completed')->count();
    $totalPaidAmount = $patient->appointments->where('payment_status', 'success')->sum(fn($a) => $a->total_amount ?? $a->total ?? ($a->service->price ?? 0));
@endphp
<div class="row mb-2">
    <div class="col-md-4 mb-3">
        <div class="card border-left-primary shadow-sm h-100 py-2">
            <div class="card-body py-2 d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Tổng số lượt đặt khám</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalAppts }} lượt</div>
                </div>
                <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card border-left-success shadow-sm h-100 py-2">
            <div class="card-body py-2 d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Số ca khám hoàn tất</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $completedAppts }} ca</div>
                </div>
                <i class="fas fa-check-circle fa-2x text-gray-300"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card border-left-info shadow-sm h-100 py-2">
            <div class="card-body py-2 d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Tổng viện phí đã thanh toán</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalPaidAmount, 0, ',', '.') }} VNĐ</div>
                </div>
                <i class="fas fa-wallet fa-2x text-gray-300"></i>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Patient Info Card -->
    <div class="col-lg-4 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Thông tin cá nhân & BHYT</h6>
            </div>
            <div class="card-body text-center">
                <div class="bg-info text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm" style="width: 80px; height: 80px;">
                    <i class="fas fa-user-injured fa-3x"></i>
                </div>
                <h5 class="font-weight-bold text-gray-800 mb-1">{{ $patient->name }}</h5>
                <p class="text-muted small mb-3">Mã hồ sơ: #PAT-{{ str_pad($patient->id, 4, '0', STR_PAD_LEFT) }}</p>

                <hr>

                <div class="text-left small text-gray-700">
                    @php
                        $gDisplay = in_array($patient->gender, ['male', 'Nam']) ? 'Nam' : (in_array($patient->gender, ['female', 'Nữ']) ? 'Nữ' : ($patient->gender ? 'Khác' : 'Chưa cập nhật'));
                    @endphp
                    <p class="mb-2"><i class="fas fa-venus-mars text-primary mr-2"></i><strong>Giới tính:</strong> {{ $gDisplay }}</p>
                    <p class="mb-2"><i class="far fa-calendar-alt text-primary mr-2"></i><strong>Ngày sinh:</strong> {{ $patient->birthdate ? \Carbon\Carbon::parse($patient->birthdate)->format('d/m/Y') : 'Chưa cập nhật' }}</p>
                    <p class="mb-2"><i class="fas fa-id-card text-success mr-2"></i><strong>Mã BHYT:</strong> <span class="badge badge-success">{{ $patient->insurance_number ?: 'Không có BHYT' }}</span></p>
                    <p class="mb-2"><i class="fas fa-phone text-success mr-2"></i><strong>Số điện thoại:</strong> {{ $patient->phone ?: ($patient->user->phone ?? 'N/A') }}</p>
                    <p class="mb-2"><i class="fas fa-envelope text-primary mr-2"></i><strong>Email tài khoản:</strong> {{ $patient->user->email ?? $patient->email ?? 'N/A' }}</p>
                    <p class="mb-0"><i class="fas fa-map-marker-alt text-danger mr-2"></i><strong>Địa chỉ:</strong> {{ $patient->address ?: 'Chưa cập nhật' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Appointments History Card (5 COLUMNS) -->
    <div class="col-lg-8 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-history mr-1"></i> Lịch sử khám bệnh tại Thái Thịnh ({{ $patient->appointments->count() }} lượt)
                </h6>
                <a href="{{ route('admin.medical-records.index', ['patient_name' => $patient->name]) }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-file-medical-alt mr-1"></i> Xem tất cả bệnh án EMR
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover mb-0" width="100%" cellspacing="0">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-center" style="width: 50px;">STT</th>
                                <th>Mã lịch & Ngày khám</th>
                                <th>Bác sĩ & Dịch vụ</th>
                                <th class="text-center">Trạng thái khám</th>
                                <th class="text-center" style="width: 130px;">Chức năng</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($patient->appointments as $aIdx => $app)
                                <tr>
                                    <td class="text-center align-middle">{{ $aIdx + 1 }}</td>
                                    <td class="align-middle">
                                        <div class="font-weight-bold text-primary">#{{ $app->appointment_code ?? $app->id }}</div>
                                        <small class="text-gray-600">{{ \Carbon\Carbon::parse($app->appointment_date)->format('d/m/Y') }} {{ $app->appointment_time ? substr($app->appointment_time, 0, 5) : '' }}</small>
                                    </td>
                                    <td class="align-middle">
                                        <div class="font-weight-bold text-gray-800">BS. {{ $app->doctor->user->name ?? 'N/A' }}</div>
                                        <small class="text-muted">{{ $app->service->name ?? 'Khám chuyên khoa' }}</small>
                                    </td>
                                    <td class="text-center align-middle">
                                        @if($app->status === 'completed')
                                            <span class="badge badge-success">Đã hoàn thành</span>
                                        @elseif($app->status === 'confirmed')
                                            <span class="badge badge-primary">Đã xác nhận</span>
                                        @elseif($app->status === 'checked_in')
                                            <span class="badge badge-info">Đã tiếp nhận (QR)</span>
                                        @elseif($app->status === 'pending')
                                            <span class="badge badge-warning text-dark">Chờ duyệt</span>
                                        @else
                                            <span class="badge badge-danger">Đã hủy</span>
                                        @endif
                                    </td>
                                    <td class="text-center align-middle">
                                        <a href="{{ route('admin.appointments.show', $app->id) }}" class="btn btn-info btn-circle btn-sm mr-1" title="Xem chi tiết lịch hẹn">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($app->medicalRecord)
                                            <a href="{{ route('admin.medical-records.show', $app->medicalRecord->id) }}" class="btn btn-primary btn-circle btn-sm" title="Xem Bệnh án EMR">
                                                <i class="fas fa-file-medical-alt"></i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Bệnh nhân chưa có lịch sử khám bệnh.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
