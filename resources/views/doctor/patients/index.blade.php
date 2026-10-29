@extends('layouts.doctor')

@section('title', 'Danh sách Bệnh nhân')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-1 text-gray-800 font-weight-bold">
            <i class="fas fa-user-injured text-primary mr-2"></i>Danh sách Bệnh nhân theo dõi
        </h1>
        <p class="text-muted small mb-0">Tra cứu nhanh hồ sơ cá nhân, thẻ BHYT và lịch sử bệnh án của người bệnh</p>
    </div>
    <div class="mt-2 mt-sm-0">
        <a href="{{ route('doctor.dashboard') }}" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50 mr-1"></i> Quay lại Dashboard
        </a>
    </div>
</div>

<!-- Search & Filter Card -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-search mr-1"></i> Tìm kiếm nhanh Hồ sơ Bệnh nhân
        </h6>
    </div>
    <div class="card-body">
        <form action="{{ route('doctor.patients.index') }}" method="GET" class="form-row align-items-end">
            <div class="col-md-9 mb-2">
                <label class="font-weight-bold text-gray-700 small">TỪ KHÓA TRA CỨU</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Nhập tên bệnh nhân, số điện thoại, mã thẻ BHYT hoặc địa chỉ...">
            </div>
            <div class="col-md-3 mb-2 d-flex">
                <button type="submit" class="btn btn-primary flex-grow-1 mr-2">
                    <i class="fas fa-search mr-1"></i> Tra cứu
                </button>
                @if(request()->filled('search'))
                    <a href="{{ route('doctor.patients.index') }}" class="btn btn-light border" title="Xóa bộ lọc">
                        <i class="fas fa-redo-alt"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Bảng danh sách bệnh nhân (ĐÚNG 5 CỘT BAO GỒM 1 CỘT CHỨC NĂNG) -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-users mr-1"></i> Danh sách hồ sơ bệnh nhân bác sĩ phụ trách
        </h6>
        <span class="badge badge-primary px-3 py-2">
            Tổng số: {{ method_exists($patients, 'total') ? $patients->total() : $patients->count() }} bệnh nhân
        </span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                <thead class="bg-light">
                    <tr>
                        <th class="text-center" style="width: 75px;">STT & Mã</th>
                        <th>Họ tên Bệnh nhân & Mã BHYT</th>
                        <th>Thông tin cá nhân</th>
                        <th>Thông tin liên lạc</th>
                        <th class="text-center" style="width: 140px;">Chức năng</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($patients as $patient)
                        @php
                            $age = $patient->birthdate ? \Carbon\Carbon::parse($patient->birthdate)->age : 'N/A';
                            $gender = match($patient->gender) {
                                'male', 'Nam' => 'Nam',
                                'female', 'Nữ' => 'Nữ',
                                default => 'Khác',
                            };
                        @endphp
                        <tr>
                            <!-- Cột 1: STT & Mã BN -->
                            <td class="text-center align-middle">
                                <span class="font-weight-bold text-gray-700">
                                    #{{ method_exists($patients, 'firstItem') ? $patients->firstItem() + $loop->index : $loop->iteration }}
                                </span>
                                <div class="mt-1">
                                    <span class="badge badge-secondary">
                                        #PAT-{{ str_pad($patient->id, 4, '0', STR_PAD_LEFT) }}
                                    </span>
                                </div>
                            </td>

                            <!-- Cột 2: Họ tên & BHYT -->
                            <td class="align-middle">
                                <div class="font-weight-bold text-primary">
                                    <i class="fas fa-user mr-1 text-info"></i>{{ $patient->name }}
                                </div>
                                <div class="small text-muted mt-1">
                                    Mã BHYT:
                                    @if($patient->insurance_number)
                                        <span class="badge badge-success">{{ $patient->insurance_number }}</span>
                                    @else
                                        <span class="badge badge-light border text-muted">Không có BHYT</span>
                                    @endif
                                    @if(isset($patient->appointments_count))
                                        <span class="badge badge-info ml-1">{{ $patient->appointments_count }} lần khám</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Cột 3: Cá nhân -->
                            <td class="align-middle">
                                <div class="font-weight-bold text-gray-800">
                                    Giới tính: {{ $gender }} ({{ $age !== 'N/A' ? $age . ' tuổi' : 'Chưa rõ tuổi' }})
                                </div>
                                <div class="small text-muted mt-1">
                                    <i class="fas fa-birthday-cake mr-1 text-info"></i>
                                    {{ $patient->birthdate ? \Carbon\Carbon::parse($patient->birthdate)->format('d/m/Y') : 'Chưa cập nhật' }}
                                </div>
                            </td>

                            <!-- Cột 4: Liên lạc -->
                            <td class="align-middle">
                                <div>
                                    <i class="fas fa-phone mr-1 text-primary"></i>
                                    <strong class="text-gray-900">{{ $patient->phone ?: ($patient->user->phone ?? 'Chưa cập nhật') }}</strong>
                                </div>
                                <div class="small text-muted mt-1 text-truncate" style="max-width: 260px;">
                                    <i class="fas fa-map-marker-alt mr-1 text-danger"></i>{{ $patient->address ?: 'Chưa cập nhật địa chỉ' }}
                                </div>
                            </td>

                            <!-- Cột 5: Chức năng -->
                            <td class="text-center align-middle">
                                <a href="{{ route('doctor.patients.show', $patient) }}" class="btn btn-info btn-circle btn-sm mr-1" title="Xem hồ sơ & bệnh án chi tiết">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('doctor.dashboard', ['patient_name' => $patient->name]) }}" class="btn btn-primary btn-circle btn-sm" title="Tra cứu lịch hẹn của bệnh nhân">
                                    <i class="fas fa-calendar-check"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Không tìm thấy hồ sơ bệnh nhân nào phù hợp.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($patients, 'links'))
            <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap">
                <div class="small text-muted mb-2 mb-sm-0">
                    Hiển thị {{ $patients->firstItem() ?? 0 }} - {{ $patients->lastItem() ?? 0 }} trong tổng số {{ $patients->total() }} bệnh nhân
                </div>
                <div>
                    {{ $patients->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
