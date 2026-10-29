@extends('layouts.admin')

@section('title', 'Thêm khoa phòng mới')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800 font-weight-bold">
        <i class="fas fa-plus-circle text-primary mr-2"></i>Thêm khoa phòng mới
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
                <h6 class="m-0 font-weight-bold text-primary">Thông tin chuyên khoa mới</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.departments.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="form-group">
                        <label class="font-weight-bold text-gray-700">Tên chuyên khoa <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Ví dụ: Khoa Tim mạch, Khoa Nhi..." required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold text-gray-700">Trạng thái hoạt động</label>
                        <select name="is_active" class="form-control">
                            <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Hoạt động bình thường</option>
                            <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Tạm dừng tiếp nhận</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold text-gray-700">Hình ảnh đại diện</label>
                        <input type="file" name="image" class="form-control-file @error('image') is-invalid @enderror">
                        <small class="form-text text-muted">Định dạng file: JPG, PNG, WEBP (Tối đa 2MB)</small>
                        @error('image')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold text-gray-700">Mô tả chức năng & nhiệm vụ chuyên khoa</label>
                        <textarea name="description" rows="4" class="form-control @error('description') is-invalid @enderror" placeholder="Mô tả kỹ thuật khám chữa bệnh, máy móc trang thiết bị...">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr>

                    <button type="submit" class="btn btn-primary btn-icon-split">
                        <span class="icon text-white-50"><i class="fas fa-save"></i></span>
                        <span class="text">Lưu chuyên khoa</span>
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
