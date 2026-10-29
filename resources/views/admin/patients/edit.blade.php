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

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Thông tin hành chính & liên lạc</h6>
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
            <div class="d-flex justify-content-end">
                <a href="{{ route('admin.patients.index') }}" class="btn btn-secondary mr-2">Hủy</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i> Lưu thay đổi
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
