@extends('layouts.doctor')

@section('title', 'Hồ sơ bệnh nhân - ' . $patient->name)

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-1 text-gray-800 font-weight-bold">
            <i class="fas fa-user-injured text-primary mr-2"></i>Hồ sơ bệnh nhân: {{ $patient->name }}
        </h1>
        <p class="text-muted small mb-0">Mã bệnh nhân: #PAT-{{ str_pad($patient->id, 4, '0', STR_PAD_LEFT) }} | Thông tin chi tiết và lịch sử thăm khám</p>
    </div>
    <div class="mt-2 mt-sm-0">
        <button type="button" onclick="window.print()" class="btn btn-sm btn-outline-primary shadow-sm mr-2">
            <i class="fas fa-print fa-sm mr-1"></i> In tóm tắt bệnh sử
        </button>
        <a href="{{ route('doctor.patients.index') }}" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50 mr-1"></i> Quay lại danh sách
        </a>
    </div>
</div>

@php
    $docAppts = $patient->appointments ?? collect();
    $completedCount = $docAppts->where('status', 'completed')->count();
    $latestRecordAppt = $docAppts->first(fn($a) => $a->medicalRecord && !empty($a->medicalRecord->diagnosis));
    $latestDiagnosis = $latestRecordAppt ? $latestRecordAppt->medicalRecord->diagnosis : 'Chưa có chẩn đoán trước đây';
@endphp
<div class="row mb-2">
    <div class="col-md-4 mb-3">
        <div class="card border-left-primary shadow-sm h-100 py-2">
            <div class="card-body py-2 d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Tổng số lượt thăm khám</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $docAppts->count() }} lượt</div>
                </div>
                <i class="fas fa-notes-medical fa-2x text-gray-300"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card border-left-success shadow-sm h-100 py-2">
            <div class="card-body py-2 d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Số ca đã hoàn tất EMR</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $completedCount }} ca</div>
                </div>
                <i class="fas fa-check-circle fa-2x text-gray-300"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card border-left-danger shadow-sm h-100 py-2">
            <div class="card-body py-2 d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Chẩn đoán gần nhất</div>
                    <div class="small font-weight-bold text-gray-800">{{ \Illuminate\Support\Str::limit($latestDiagnosis, 45) }}</div>
                </div>
                <i class="fas fa-heartbeat fa-2x text-gray-300"></i>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Patient Info Card -->
    <div class="col-lg-4 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-light">
                <h6 class="m-0 font-weight-bold text-primary">Thông tin cá nhân & BHYT</h6>
            </div>
            <div class="card-body text-center">
                <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm" style="width: 85px; height: 85px;">
                    <i class="fas fa-user-injured fa-3x"></i>
                </div>
                <h5 class="font-weight-bold text-gray-800 mb-1">{{ $patient->name }}</h5>
                <p class="text-muted small mb-3">Mã hồ sơ: #PAT-{{ str_pad($patient->id, 4, '0', STR_PAD_LEFT) }}</p>

                <hr>

                <div class="text-left small text-gray-700">
                    <p class="mb-2"><i class="fas fa-venus-mars text-primary mr-2"></i><strong>Giới tính:</strong> {{ $patient->gender == 'male' ? 'Nam' : ($patient->gender == 'female' ? 'Nữ' : 'Khác') }}</p>
                    <p class="mb-2"><i class="far fa-calendar-alt text-primary mr-2"></i><strong>Ngày sinh:</strong> {{ $patient->birthdate ? \Carbon\Carbon::parse($patient->birthdate)->format('d/m/Y') : 'Chưa cập nhật' }}</p>
                    <p class="mb-2"><i class="fas fa-id-card text-success mr-2"></i><strong>Mã BHYT:</strong> {{ $patient->insurance_number ?? 'Không có BHYT' }}</p>
                    <p class="mb-2"><i class="fas fa-phone text-primary mr-2"></i><strong>Điện thoại:</strong> {{ $patient->phone ?? $patient->user->phone ?? 'N/A' }}</p>
                    <p class="mb-0"><i class="fas fa-map-marker-alt text-danger mr-2"></i><strong>Địa chỉ:</strong> {{ $patient->address ?? 'Hà Nội' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Appointments History Card (ĐÚNG 5 CỘT BAO GỒM 1 CỘT CHỨC NĂNG) -->
    <div class="col-lg-8 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-history mr-1"></i> Lịch sử khám bệnh của bệnh nhân ({{ $patient->appointments->count() }} lượt)
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0" width="100%" cellspacing="0">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-center" style="width: 50px;">STT</th>
                                <th>Mã lịch & Ngày khám</th>
                                <th>Bác sĩ & Khoa phụ trách</th>
                                <th style="width: 140px;" class="text-center">Trạng thái</th>
                                <th class="text-center" style="width: 130px;">Chức năng</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($patient->appointments as $apt)
                                <tr>
                                    <td class="text-center font-weight-bold text-gray-600 align-middle">{{ $loop->iteration }}</td>
                                    <td class="align-middle">
                                        <div class="font-weight-bold text-primary">{{ $apt->appointment_code ?? '#APT-'.$apt->id }}</div>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($apt->appointment_date)->format('d/m/Y H:i') }}</small>
                                    </td>
                                    <td class="align-middle">
                                        <div class="font-weight-bold text-gray-800">{{ $apt->doctor->formatted_name ?? $apt->doctor->user->name ?? 'N/A' }}</div>
                                        <small class="text-info d-block">{{ $apt->doctor->department->name ?? 'Đa khoa' }}</small>
                                        @if($apt->medicalRecord && $apt->medicalRecord->diagnosis)
                                            <small class="text-danger font-weight-bold d-block mt-1">
                                                <i class="fas fa-stethoscope mr-1"></i>{{ \Illuminate\Support\Str::limit($apt->medicalRecord->diagnosis, 55) }}
                                            </small>
                                        @endif
                                    </td>
                                    <td class="text-center align-middle">
                                        @if($apt->status === 'completed')
                                            <span class="badge badge-success">Đã khám</span>
                                        @elseif($apt->status === 'checked_in')
                                            <span class="badge badge-info">Đã tiếp nhận</span>
                                        @elseif($apt->status === 'confirmed')
                                            <span class="badge badge-warning text-dark">Chờ khám</span>
                                        @elseif($apt->status === 'cancelled')
                                            <span class="badge badge-danger">Đã hủy</span>
                                        @else
                                            <span class="badge badge-secondary">{{ $apt->status }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center align-middle">
                                        @php
                                            $docUser = auth('web_doctor')->user() ?? auth()->user();
                                        @endphp
                                        @if($apt->doctor_id === ($docUser && $docUser->doctor ? $docUser->doctor->id : null))
                                            <a href="{{ route('doctor.patient.record', $apt->id) }}" class="btn btn-primary btn-sm shadow-sm" title="Khám bệnh & Bệnh án EMR">
                                                <i class="fas fa-stethoscope mr-1"></i> Khám EMR
                                            </a>
                                        @else
                                            <a href="{{ route('doctor.patient.record', $apt->id) }}" class="btn btn-outline-info btn-sm shadow-sm" title="Xem tiền sử bệnh án (Chế độ chỉ xem)">
                                                <i class="fas fa-file-medical-alt mr-1"></i> Xem tiền sử
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
