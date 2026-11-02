@extends('layouts.admin')

@section('title', 'Chỉnh sửa thông tin bác sĩ')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800 font-weight-bold">
        <i class="fas fa-user-edit text-primary mr-2"></i>Chỉnh sửa hồ sơ: {{ $doctor->formatted_name ?? $doctor->user->name ?? $doctor->name }}
    </h1>
    <a href="{{ route('admin.doctors.index') }}" class="btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50 mr-1"></i> Quay lại danh sách
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Form Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Cập nhật hồ sơ chuyên môn & tài khoản</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.doctors.update', $doctor) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <h6 class="font-weight-bold text-primary mb-3">
                        <i class="fas fa-user mr-1"></i> 1. Thông tin cá nhân & tài khoản
                    </h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-700">Họ và tên Bác sĩ <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $doctor->user->name ?? $doctor->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-700">Email liên hệ <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $doctor->user->email ?? $doctor->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-700">Mật khẩu mới (Để trống nếu không đổi)</label>
                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Nhập nếu muốn đổi mật khẩu mới">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-700">Số điện thoại liên hệ <span class="text-danger">*</span></label>
                                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $doctor->user->phone ?? $doctor->phone) }}" required>
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <h6 class="font-weight-bold text-primary mb-3">
                        <i class="fas fa-stethoscope mr-1"></i> 2. Thông tin chuyên môn
                    </h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-700">Khoa chuyên môn trực thuộc <span class="text-danger">*</span></label>
                                <select name="department_id" class="form-control @error('department_id') is-invalid @enderror" required>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}" {{ old('department_id', $doctor->department_id) == $dept->id ? 'selected' : '' }}>
                                            {{ $dept->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('department_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-700">Chức danh / Học hàm / Học vị <span class="text-danger">*</span></label>
                                <input type="text" name="specialization" class="form-control @error('specialization') is-invalid @enderror" value="{{ old('specialization', $doctor->specialization) }}" required>
                                @error('specialization')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-700">Số chứng chỉ hành nghề (CCHN) <span class="text-danger">*</span></label>
                                <input type="text" name="license_number" class="form-control @error('license_number') is-invalid @enderror" value="{{ old('license_number', $doctor->license_number) }}" required>
                                @error('license_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-700">Buồng khám phụ trách</label>
                                <input type="text" name="room_number" class="form-control @error('room_number') is-invalid @enderror" value="{{ old('room_number', $doctor->room_number) }}" placeholder="Ví dụ: P.201 - Tầng 2">
                                @error('room_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-700">Trạng thái công tác</label>
                                <select name="is_active" class="form-control @error('is_active') is-invalid @enderror">
                                    <option value="1" {{ (int) old('is_active', $doctor->is_active ?? 1) === 1 ? 'selected' : '' }}>Đang công tác (Nhận lịch khám)</option>
                                    <option value="0" {{ (int) old('is_active', $doctor->is_active ?? 1) === 0 ? 'selected' : '' }}>Tạm ngừng nhận lịch</option>
                                </select>
                                @error('is_active')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-700">Ảnh chân dung</label>
                                @if($doctor->avatar)
                                    <div class="mb-2">
                                        <img src="{{ asset('storage/'.$doctor->avatar) }}" class="rounded-circle border" style="width: 60px; height: 60px; object-fit: cover;">
                                    </div>
                                @endif
                                <input type="file" name="avatar" class="form-control-file @error('avatar') is-invalid @enderror">
                                <small class="form-text text-muted">Tải ảnh mới nếu muốn thay ảnh hiện tại</small>
                                @error('avatar')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold text-gray-700">Quá trình công tác & Kinh nghiệm chuyên sâu</label>
                        <textarea name="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description', $doctor->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr>

                    <button type="submit" class="btn btn-primary btn-icon-split">
                        <span class="icon text-white-50"><i class="fas fa-save"></i></span>
                        <span class="text">Cập nhật hồ sơ</span>
                    </button>
                    <a href="{{ route('admin.doctors.index') }}" class="btn btn-secondary btn-icon-split ml-2">
                        <span class="icon text-white-50"><i class="fas fa-times"></i></span>
                        <span class="text">Hủy bỏ</span>
                    </a>
                </form>
            </div>
        </div>
    </div>

    <!-- Cột phải: Thẻ Định danh Bác sĩ & Buồng khám -->
    <div class="col-lg-4">
        <div class="card shadow mb-4 border-left-info">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-user-md mr-1"></i> Thẻ Định danh Chuyên gia
                </h6>
            </div>
            <div class="card-body text-center">
                @if($doctor->avatar)
                    <img src="{{ asset('storage/'.$doctor->avatar) }}" class="rounded-circle border shadow-sm mb-3" style="width: 90px; height: 90px; object-fit: cover;">
                @else
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm" style="width: 84px; height: 84px;">
                        <i class="fas fa-user-md fa-2x"></i>
                    </div>
                @endif
                <h6 class="font-weight-bold text-gray-800 mb-1">{{ $doctor->formatted_name ?? $doctor->user->name ?? $doctor->name }}</h6>
                <div class="badge badge-info px-3 py-1 mb-3">{{ $doctor->department->name ?? 'Đa khoa' }}</div>
                <div class="sb-room-card text-left mb-3">
                    <div class="small mb-1"><i class="fas fa-id-card text-primary mr-2"></i><strong>CCHN:</strong> {{ $doctor->license_number ?: 'Đang cập nhật' }}</div>
                    <div class="small mb-1"><i class="fas fa-door-open text-success mr-2"></i><strong>Buồng khám:</strong> {{ $doctor->room_number ?: 'P.201 - Tầng 2' }}</div>
                    <div class="small"><i class="fas fa-calendar-check text-info mr-2"></i><strong>Ca khám phụ trách:</strong> {{ $doctor->appointments()->count() }} lượt</div>
                </div>
                <a href="{{ route('admin.doctors.schedule', $doctor->id) }}" class="btn btn-outline-primary btn-sm btn-block font-weight-bold">
                    <i class="fas fa-chair mr-1"></i> Xem sơ đồ ghế khám &amp; lịch trực
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
