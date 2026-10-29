@extends('layouts.admin')

@section('title', 'Chỉnh sửa chuyên khoa')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800 font-weight-bold">
        <i class="fas fa-edit text-primary mr-2"></i>Chỉnh sửa chuyên khoa: {{ $department->name }}
    </h1>
    <a href="{{ route('admin.departments.index') }}" class="btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50 mr-1"></i> Quay lại danh sách
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Form Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Cập nhật thông tin khoa phòng</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.departments.update', $department) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label class="font-weight-bold text-gray-700">Tên chuyên khoa <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $department->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold text-gray-700">Trạng thái hoạt động</label>
                        <select name="is_active" class="form-control">
                            <option value="1" {{ old('is_active', $department->is_active ? '1' : '0') == '1' ? 'selected' : '' }}>Hoạt động bình thường</option>
                            <option value="0" {{ old('is_active', $department->is_active ? '1' : '0') == '0' ? 'selected' : '' }}>Tạm dừng tiếp nhận</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold text-gray-700">Hình ảnh đại diện</label>
                        @if($department->image)
                            <div class="mb-2">
                                <img src="{{ asset('storage/'.$department->image) }}" class="rounded border" style="width: 80px; height: 80px; object-fit: cover;">
                            </div>
                        @endif
                        <input type="file" name="image" class="form-control-file @error('image') is-invalid @enderror">
                        <small class="form-text text-muted">Tải ảnh mới nếu muốn thay đổi ảnh hiện tại</small>
                        @error('image')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold text-gray-700">Mô tả chức năng & nhiệm vụ chuyên khoa</label>
                        <textarea name="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description', $department->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr>

                    <button type="submit" class="btn btn-primary btn-icon-split">
                        <span class="icon text-white-50"><i class="fas fa-save"></i></span>
                        <span class="text">Cập nhật thay đổi</span>
                    </button>
                    <a href="{{ route('admin.departments.index') }}" class="btn btn-secondary btn-icon-split ml-2">
                        <span class="icon text-white-50"><i class="fas fa-times"></i></span>
                        <span class="text">Hủy bỏ</span>
                    </a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
