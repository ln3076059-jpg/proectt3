@extends('layouts.doctor')

@section('title', 'Hồ sơ Bác sĩ')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-1 text-gray-800 font-weight-bold">
            <i class="fas fa-id-card-alt text-primary mr-2"></i>Hồ sơ Bác sĩ & Cài đặt Tài khoản
        </h1>
        <p class="text-muted small mb-0">Quản lý thông tin chứng chỉ hành nghề, chuyên khoa và bảo mật tài khoản</p>
    </div>
    <div class="mt-2 mt-sm-0">
        <a href="{{ route('doctor.dashboard') }}" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50 mr-1"></i> Quay lại Dashboard
        </a>
    </div>
</div>

{{-- Flash Alert --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
        <h6 class="font-weight-bold mb-1"><i class="fas fa-exclamation-circle mr-1"></i> Vui lòng kiểm tra lại thông tin:</h6>
        <ul class="mb-0 pl-3">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

<div class="row">
    <!-- Cột trái: Tóm tắt Hồ sơ Bác sĩ -->
    <div class="col-lg-4 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-light">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-user-md mr-1"></i> Thông tin Chuyên môn
                </h6>
            </div>
            <div class="card-body text-center">
                <div class="position-relative d-inline-block mb-3">
                    @if($doctor->avatar)
                        <img src="{{ asset('storage/' . $doctor->avatar) }}" class="rounded-circle border shadow-sm" style="width: 130px; height: 130px; object-fit: cover;">
                    @else
                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 130px; height: 130px;">
                            <i class="fas fa-user-md fa-4x"></i>
                        </div>
                    @endif
                </div>
                <h5 class="font-weight-bold text-gray-900 mb-1">BS. {{ $user->name }}</h5>
                <p class="text-primary font-weight-bold mb-2">{{ $doctor->specialization ?? 'Bác sĩ chuyên khoa' }}</p>
                <div class="badge badge-info px-3 py-2 mb-3">{{ $doctor->department->name ?? 'Đa khoa' }}</div>

                <hr>

                <div class="text-left small text-gray-700">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted"><i class="fas fa-id-card text-primary mr-2"></i>Số CCHN:</span>
                        <span class="font-weight-bold">{{ $doctor->license_number ?? '012456/HN-CCHN' }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted"><i class="fas fa-envelope text-primary mr-2"></i>Email:</span>
                        <span class="font-weight-bold">{{ $user->email }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted"><i class="fas fa-phone text-primary mr-2"></i>Điện thoại:</span>
                        <span class="font-weight-bold">{{ $user->phone ?? $doctor->phone ?? '0912345678' }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted"><i class="fas fa-check-circle text-success mr-2"></i>Trạng thái trực:</span>
                        <span class="badge badge-success">Đang hoạt động</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hướng dẫn bảo mật Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-light">
                <h6 class="m-0 font-weight-bold text-secondary">
                    <i class="fas fa-shield-alt mr-1"></i> Quy định Bảo mật EMR
                </h6>
            </div>
            <div class="card-body small text-muted">
                <p class="mb-2">Tài khoản Bác sĩ có quyền truy cập hồ sơ bệnh án điện tử và kê đơn thuốc được cấp phép.</p>
                <ul class="pl-3 mb-0">
                    <li class="mb-1">Tuyệt đối không chia sẻ mật khẩu cho nhân viên khác.</li>
                    <li class="mb-1">Đổi mật khẩu định kỳ 3 tháng/lần.</li>
                    <li>Đăng xuất khỏi hệ thống khi rời khỏi phòng khám bệnh.</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Cột phải: Form cập nhật thông tin & Đổi mật khẩu -->
    <div class="col-lg-8 mb-4">
        <!-- Form cập nhật hồ sơ -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-user-edit mr-1"></i> Cập nhật Thông tin Bác sĩ
                </h6>
            </div>
            <div class="card-body">
                <form action="{{ route('doctor.profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="form-row">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-gray-700">Họ và tên Bác sĩ <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-gray-700">Email đăng nhập (Cố định)</label>
                            <input type="email" class="form-control bg-light" value="{{ $user->email }}" readonly>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-gray-700">Số điện thoại liên hệ</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone ?? $doctor->phone) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-gray-700">Giới tính</label>
                            <select name="gender" class="form-control">
                                <option value="male" {{ old('gender', $user->gender) == 'male' ? 'selected' : '' }}>Nam</option>
                                <option value="female" {{ old('gender', $user->gender) == 'female' ? 'selected' : '' }}>Nữ</option>
                                <option value="other" {{ old('gender', $user->gender) == 'other' ? 'selected' : '' }}>Khác</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-gray-700">Khoa / Phòng trực thuộc <span class="badge badge-light border text-muted">Do Admin quản lý</span></label>
                            <input type="text" class="form-control bg-light" value="{{ $doctor->department->name ?? 'Đa khoa' }}" readonly>
                            <small class="text-muted">Liên hệ Quản trị viên nếu cần điều chuyển chuyên khoa.</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-gray-700">Chuyên môn / Học vị <span class="badge badge-light border text-muted">Do Admin quản lý</span></label>
                            <input type="text" class="form-control bg-light" value="{{ $doctor->specialization ?? 'Bác sĩ chuyên khoa' }}" readonly>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-gray-700">Số Chứng chỉ hành nghề (CCHN) <span class="badge badge-light border text-muted">Cố định</span></label>
                            <input type="text" class="form-control bg-light" value="{{ $doctor->license_number ?? '012456/HN-CCHN' }}" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-gray-700">Ảnh đại diện mới</label>
                            <input type="file" name="avatar" class="form-control-file">
                        </div>
                    </div>


                    <div class="form-group">
                        <label class="font-weight-bold text-gray-700">Địa chỉ liên hệ</label>
                        <input type="text" name="address" class="form-control" value="{{ old('address', $user->address) }}" placeholder="Địa chỉ hiện tại">
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold text-gray-700">Giới thiệu bản thân & Kinh nghiệm công tác</label>
                        <textarea name="description" rows="3" class="form-control" placeholder="Tóm tắt quá trình công tác, bệnh viện từng làm việc...">{{ old('description', $doctor->description) }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-icon-split shadow-sm">
                        <span class="icon text-white-50"><i class="fas fa-save"></i></span>
                        <span class="text">Lưu thay đổi hồ sơ</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Form đổi mật khẩu -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-key mr-1"></i> Đổi mật khẩu tài khoản Bác sĩ
                </h6>
            </div>
            <div class="card-body">
                <form action="{{ route('doctor.password.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label class="font-weight-bold text-gray-700">Mật khẩu hiện tại <span class="text-danger">*</span></label>
                        <input type="password" name="current_password" class="form-control" required placeholder="Nhập mật khẩu hiện tại">
                    </div>

                    <div class="form-row">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-gray-700">Mật khẩu mới (Tối thiểu 6 ký tự) <span class="text-danger">*</span></label>
                            <input type="password" name="new_password" class="form-control" required placeholder="Nhập mật khẩu mới">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-gray-700">Xác nhận mật khẩu mới <span class="text-danger">*</span></label>
                            <input type="password" name="new_password_confirmation" class="form-control" required placeholder="Nhập lại mật khẩu mới">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-warning btn-icon-split shadow-sm">
                        <span class="icon text-white-50"><i class="fas fa-lock"></i></span>
                        <span class="text">Cập nhật mật khẩu mới</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
