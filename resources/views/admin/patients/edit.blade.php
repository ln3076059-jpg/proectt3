@extends('layouts.admin')

@section('title', 'Chỉnh sửa Bệnh nhân - ' . $patient->name)

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800 font-weight-bold">
        <i class="fas fa-user-edit text-primary mr-2"></i>Cập nhật Hồ sơ Bệnh nhân: {{ $patient->name }}
    </h1>
    <a href="{{ route('admin.patients.index') }}" class="btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50 mr-1"></i> Quay lại danh sách
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Thông tin hành chính &amp; liên lạc</h6>
            </div>
            <div class="card-body">
        <form action="{{ route('admin.patients.update', $patient->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label class="font-weight-bold text-gray-700">Họ và tên bệnh nhân <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $patient->name ?? optional($patient->user)->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group col-md-6">
                    <label class="font-weight-bold text-gray-700">Email tài khoản <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $patient->email ?? optional($patient->user)->email) }}" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            @php
                $currentGender = old('gender', $patient->gender ?? optional($patient->user)->gender);
                if ($currentGender === 'male') $currentGender = 'Nam';
                if ($currentGender === 'female') $currentGender = 'Nữ';
            @endphp

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label class="font-weight-bold text-gray-700">Số điện thoại</label>
                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $patient->phone ?? optional($patient->user)->phone) }}">
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group col-md-4">
                    <label class="font-weight-bold text-gray-700">Giới tính</label>
                    <select name="gender" class="form-control @error('gender') is-invalid @enderror">
                        <option value="male" {{ $currentGender === 'Nam' ? 'selected' : '' }}>Nam</option>
                        <option value="female" {{ $currentGender === 'Nữ' ? 'selected' : '' }}>Nữ</option>
                        <option value="other" {{ !in_array($currentGender, ['Nam', 'Nữ']) ? 'selected' : '' }}>Khác</option>
                    </select>
                    @error('gender')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group col-md-4">
                    <label class="font-weight-bold text-gray-700">Ngày sinh</label>
                    <input type="date" name="birthdate" class="form-control @error('birthdate') is-invalid @enderror" value="{{ old('birthdate', $patient->birthdate ? \Carbon\Carbon::parse($patient->birthdate)->format('Y-m-d') : '') }}">
                    @error('birthdate')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label class="font-weight-bold text-gray-700">Số thẻ BHYT</label>
                    <input type="text" name="insurance_number" class="form-control @error('insurance_number') is-invalid @enderror" value="{{ old('insurance_number', $patient->insurance_number) }}">
                    @error('insurance_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group col-md-6">
                    <label class="font-weight-bold text-gray-700">Địa chỉ thường trú</label>
                    <input type="text" name="address" class="form-control @error('address') is-invalid @enderror" value="{{ old('address', $patient->address ?? optional($patient->user)->address) }}">
                    @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <hr>
            <div>
                <button type="submit" class="btn btn-primary btn-icon-split">
                    <span class="icon text-white-50"><i class="fas fa-save"></i></span>
                    <span class="text">Lưu thay đổi</span>
                </button>
                <a href="{{ route('admin.patients.index') }}" class="btn btn-secondary btn-icon-split ml-2">
                    <span class="icon text-white-50"><i class="fas fa-times"></i></span>
                    <span class="text">Hủy bỏ</span>
                </a>
            </div>
        </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow mb-4 border-left-info">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-info">
                    <i class="fas fa-user-injured mr-1"></i> Tóm tắt Hồ sơ #PAT-{{ str_pad($patient->id, 4, '0', STR_PAD_LEFT) }}
                </h6>
            </div>
            <div class="card-body">
                <div class="sb-room-card mb-3">
                    <div class="small mb-1"><strong>Họ tên:</strong> {{ $patient->name }}</div>
                    <div class="small mb-1"><strong>Thẻ BHYT:</strong> <span class="badge badge-success">{{ $patient->insurance_number ?: 'Chưa cập nhật' }}</span></div>
                    <div class="small"><strong>Tổng lượt khám:</strong> {{ $patient->appointments()->count() }} ca</div>
                </div>
                <a href="{{ route('admin.patients.show', $patient->id) }}" class="btn btn-outline-primary btn-sm btn-block font-weight-bold">
                    <i class="fas fa-history mr-1"></i> Xem toàn bộ lịch sử khám bệnh
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
