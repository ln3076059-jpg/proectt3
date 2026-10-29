@extends('layouts.admin')

@section('title', 'Chỉnh sửa tài khoản người dùng')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-1 text-gray-800 font-weight-bold">
            <i class="fas fa-user-edit text-primary mr-2"></i>Chỉnh sửa tài khoản: {{ $user->name }}
        </h1>
        <p class="text-muted small mb-0">Cập nhật thông tin định danh, phân quyền truy cập và đồng bộ hồ sơ chuyên môn</p>
    </div>
    <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-secondary shadow-sm mt-2 mt-sm-0">
        <i class="fas fa-arrow-left fa-sm text-white-50 mr-1"></i> Quay lại danh sách
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Form Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Thông tin tài khoản & quyền truy cập</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.users.update', $user) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-700">Họ và tên <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-700">Email đăng nhập <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-700">Vai trò phân quyền <span class="text-danger">*</span></label>
                                <select name="role_id" class="form-control @error('role_id') is-invalid @enderror" required>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                                            {{ strtoupper($role->name) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('role_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-700">Số điện thoại</label>
                                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone) }}">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-700">Giới tính</label>
                                <select name="gender" class="form-control @error('gender') is-invalid @enderror">
                                    <option value="">-- Chọn giới tính --</option>
                                    <option value="male" {{ old('gender', $user->gender) === 'male' ? 'selected' : '' }}>Nam</option>
                                    <option value="female" {{ old('gender', $user->gender) === 'female' ? 'selected' : '' }}>Nữ</option>
                                    <option value="other" {{ old('gender', $user->gender) === 'other' ? 'selected' : '' }}>Khác</option>
                                </select>
                                @error('gender')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-700">Ngày sinh</label>
                                <input type="date" name="birthdate" class="form-control @error('birthdate') is-invalid @enderror" value="{{ old('birthdate', $user->birthdate ? \Carbon\Carbon::parse($user->birthdate)->format('Y-m-d') : '') }}">
                                @error('birthdate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold text-gray-700">Đổi mật khẩu mới (Để trống nếu không đổi)</label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Nhập mật khẩu mới (tối thiểu 6 ký tự) nếu muốn thay đổi">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold text-gray-700">Địa chỉ</label>
                        <input type="text" name="address" class="form-control @error('address') is-invalid @enderror" value="{{ old('address', $user->address) }}">
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr>

                    <button type="submit" class="btn btn-primary btn-icon-split shadow-sm">
                        <span class="icon text-white-50"><i class="fas fa-save"></i></span>
                        <span class="text font-weight-bold">Cập nhật tài khoản</span>
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-icon-split ml-2">
                        <span class="icon text-white-50"><i class="fas fa-times"></i></span>
                        <span class="text">Hủy bỏ</span>
                    </a>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-light">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-link mr-1"></i> Hồ sơ liên kết tự động
                </h6>
            </div>
            <div class="card-body small text-gray-700">
                <p class="mb-2"><strong>Mã người dùng:</strong> #USR-{{ str_pad($user->id, 4, '0', STR_PAD_LEFT) }}</p>
                <p class="mb-2"><strong>Ngày tạo:</strong> {{ $user->created_at ? $user->created_at->format('d/m/Y H:i') : 'N/A' }}</p>
                <hr>
                @if($user->doctor)
                    <div class="alert alert-info mb-2 p-2">
                        <strong><i class="fas fa-user-md mr-1"></i>Hồ sơ Bác sĩ:</strong> Đã liên kết<br>
                        Khoa: {{ $user->doctor->department->name ?? 'Đa khoa' }}
                        <div class="mt-2">
                            <a href="{{ route('admin.doctors.edit', $user->doctor) }}" class="btn btn-sm btn-info btn-block">Quản lý Hồ sơ Bác sĩ</a>
                        </div>
                    </div>
                @endif
                @if($user->patient)
                    <div class="alert alert-success mb-0 p-2">
                        <strong><i class="fas fa-user-injured mr-1"></i>Hồ sơ Bệnh nhân:</strong> #PAT-{{ str_pad($user->patient->id, 4, '0', STR_PAD_LEFT) }}
                        <div class="mt-2">
                            <a href="{{ route('admin.patients.show', $user->patient->id) }}" class="btn btn-sm btn-success btn-block">Xem Hồ sơ Bệnh nhân</a>
                        </div>
                    </div>
                @endif
                @if(!$user->doctor && !$user->patient)
                    <p class="text-muted mb-0">Khi chuyển vai trò sang <strong>DOCTOR</strong> hoặc <strong>PATIENT</strong>, hệ thống sẽ tự động khởi tạo hồ sơ tương ứng nếu chưa có.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
