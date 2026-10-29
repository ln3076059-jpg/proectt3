@extends('layouts.admin')

@section('title', 'Chỉnh sửa thông tin thuốc')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800 font-weight-bold">
        <i class="fas fa-edit text-primary mr-2"></i>Chỉnh sửa thuốc: {{ $medicine->name }}
    </h1>
    <a href="{{ route('admin.medicines.index') }}" class="btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50 mr-1"></i> Quay lại danh sách
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Form Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Cập nhật thông tin dược phẩm</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.medicines.update', $medicine) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-700">Tên thuốc <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $medicine->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-700">Tên biệt dược</label>
                                <input type="text" name="brand_name" class="form-control @error('brand_name') is-invalid @enderror" value="{{ old('brand_name', $medicine->brand_name) }}">
                                @error('brand_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-700">Hoạt chất chính (Generic Name)</label>
                                <input type="text" name="generic_name" class="form-control @error('generic_name') is-invalid @enderror" value="{{ old('generic_name', $medicine->generic_name) }}">
                                @error('generic_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-700">Nhóm điều trị / Phân loại</label>
                                <input type="text" name="category" class="form-control @error('category') is-invalid @enderror" value="{{ old('category', $medicine->category) }}">
                                @error('category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-700">Hàm lượng</label>
                                <input type="text" name="strength" class="form-control @error('strength') is-invalid @enderror" value="{{ old('strength', $medicine->strength) }}">
                                @error('strength')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-700">Dạng bào chế</label>
                                <input type="text" name="dosage_form" class="form-control @error('dosage_form') is-invalid @enderror" value="{{ old('dosage_form', $medicine->dosage_form) }}">
                                @error('dosage_form')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-700">Đơn vị tính <span class="text-danger">*</span></label>
                                <select name="unit" class="form-control @error('unit') is-invalid @enderror" required>
                                    @foreach(['Viên', 'Vỉ', 'Hộp', 'Chai', 'Lọ', 'Ống', 'Gói'] as $u)
                                        <option value="{{ $u }}" {{ old('unit', $medicine->unit) == $u ? 'selected' : '' }}>{{ $u }}</option>
                                    @endforeach
                                </select>
                                @error('unit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-700">Đơn giá bán lẻ (VNĐ) <span class="text-danger">*</span></label>
                                <input type="number" name="price" class="form-control @error('price') is-invalid @enderror" value="{{ old('price', (int)$medicine->price) }}" min="0" step="100" required>
                                @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-700">Số lượng tồn kho</label>
                                <input type="number" name="stock" class="form-control @error('stock') is-invalid @enderror" value="{{ old('stock', $medicine->stock) }}" min="0">
                                @error('stock')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold text-gray-700">Hướng dẫn sử dụng & Liều dùng</label>
                        <textarea name="usage" rows="3" class="form-control @error('usage') is-invalid @enderror">{{ old('usage', $medicine->usage) }}</textarea>
                        @error('usage')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr>

                    <button type="submit" class="btn btn-primary btn-icon-split">
                        <span class="icon text-white-50"><i class="fas fa-save"></i></span>
                        <span class="text">Lưu thay đổi</span>
                    </button>
                    <a href="{{ route('admin.medicines.index') }}" class="btn btn-secondary btn-icon-split ml-2">
                        <span class="icon text-white-50"><i class="fas fa-times"></i></span>
                        <span class="text">Hủy bỏ</span>
                    </a>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Medicine Summary Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-light">
                <h6 class="m-0 font-weight-bold text-secondary">
                    <i class="fas fa-pills mr-1"></i> Tình trạng trong kho
                </h6>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <div class="h5 font-weight-bold text-primary">{{ $medicine->name }}</div>
                    <div class="text-xs text-gray-500">Mã SKU: #MED-{{ str_pad($medicine->id, 4, '0', STR_PAD_LEFT) }}</div>
                </div>
                <hr>
                <div class="small">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-gray-600">Đơn vị:</span>
                        <span class="font-weight-bold">{{ $medicine->unit ?? 'Viên' }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-gray-600">Tồn kho hiện tại:</span>
                        <span class="font-weight-bold {{ ($medicine->stock ?? 0) <= 10 ? 'text-danger' : 'text-success' }}">
                            {{ $medicine->stock ?? 0 }} {{ $medicine->unit ?? 'viên' }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-gray-600">Ngày cập nhật:</span>
                        <span>{{ $medicine->updated_at ? $medicine->updated_at->format('d/m/Y H:i') : 'Mới tạo' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
