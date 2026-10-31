@extends('layouts.admin')

@section('title', 'Thêm loại xét nghiệm mới')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800 font-weight-bold">
        <i class="fas fa-plus-circle text-primary mr-2"></i>Thêm loại xét nghiệm cận lâm sàng
    </h1>
    <a href="{{ route('admin.test-types.index') }}" class="btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50 mr-1"></i> Quay lại danh sách
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Form Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Thông tin xét nghiệm</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.test-types.store') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label class="font-weight-bold text-gray-700">Tên loại xét nghiệm <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Ví dụ: Tổng phân tích tế bào máu, Xét nghiệm men gan..." required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-7">
                            <label class="font-weight-bold text-gray-700">Khoa phòng phụ trách <span class="text-danger">*</span></label>
                            <select name="department_id" class="form-control @error('department_id') is-invalid @enderror" required>
                                <option value="">-- Chọn khoa phòng --</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('department_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group col-md-5">
                            <label class="font-weight-bold text-gray-700">Chi phí xét nghiệm (VNĐ)</label>
                            <input type="number" name="price" min="0" step="1000" class="form-control @error('price') is-invalid @enderror" value="{{ old('price', 150000) }}" placeholder="150000">
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold text-gray-700">Mô tả quy chuẩn & Khoảng tham chiếu sinh học</label>
                        <textarea name="description" rows="4" class="form-control @error('description') is-invalid @enderror" placeholder="Các chỉ số bình thường, đơn vị đo lường...">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr>

                    <button type="submit" class="btn btn-primary btn-icon-split">
                        <span class="icon text-white-50"><i class="fas fa-save"></i></span>
                        <span class="text">Lưu loại xét nghiệm</span>
                    </button>
                    <a href="{{ route('admin.test-types.index') }}" class="btn btn-secondary btn-icon-split ml-2">
                        <span class="icon text-white-50"><i class="fas fa-times"></i></span>
                        <span class="text">Hủy bỏ</span>
                    </a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
