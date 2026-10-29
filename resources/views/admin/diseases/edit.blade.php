@extends('layouts.admin')

@section('title', 'Chỉnh sửa mặt bệnh')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800 font-weight-bold">
        <i class="fas fa-edit text-primary mr-2"></i>Chỉnh sửa bệnh: {{ $disease->name }}
    </h1>
    <a href="{{ route('admin.diseases.index') }}" class="btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50 mr-1"></i> Quay lại danh sách
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Form Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Cập nhật thông tin bệnh lý</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.diseases.update', $disease) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label class="font-weight-bold text-gray-700">Tên bệnh <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $disease->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold text-gray-700">Chuyên khoa điều trị <span class="text-danger">*</span></label>
                        <select name="department_id" class="form-control @error('department_id') is-invalid @enderror" required>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ old('department_id', $disease->department_id) == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('department_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    @php
                        $symptomText = $disease->symptoms ? $disease->symptoms->pluck('symptom_name')->implode(', ') : '';
                    @endphp
                    <div class="form-group">
                        <label class="font-weight-bold text-gray-700">Triệu chứng lâm sàng điển hình <small class="text-muted">(Cách nhau bởi dấu phẩy hoặc xuống dòng)</small></label>
                        <textarea name="symptoms" rows="3" class="form-control @error('symptoms') is-invalid @enderror" placeholder="Ví dụ: Đau đầu, Chóng mặt, Sốt cao...">{{ is_array(old('symptoms')) ? implode(', ', old('symptoms')) : old('symptoms', $symptomText) }}</textarea>
                        @error('symptoms')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold text-gray-700">Mô tả bệnh lý & Hướng dẫn khám</label>
                        <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $disease->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr>

                    <button type="submit" class="btn btn-primary btn-icon-split">
                        <span class="icon text-white-50"><i class="fas fa-save"></i></span>
                        <span class="text">Cập nhật thay đổi</span>
                    </button>
                    <a href="{{ route('admin.diseases.index') }}" class="btn btn-secondary btn-icon-split ml-2">
                        <span class="icon text-white-50"><i class="fas fa-times"></i></span>
                        <span class="text">Hủy bỏ</span>
                    </a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
