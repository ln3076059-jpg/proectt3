@extends('layouts.admin')

@section('title', 'Thêm thuốc mới')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800 font-weight-bold">
        <i class="fas fa-plus-circle text-primary mr-2"></i>Thêm thuốc mới vào danh mục
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
                <h6 class="m-0 font-weight-bold text-primary">Thông tin thuốc & biệt dược</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.medicines.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-700">Tên thuốc <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Ví dụ: Paracetamol 500mg, Panadol Extra..." required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-700">Tên biệt dược</label>
                                <input type="text" name="brand_name" class="form-control @error('brand_name') is-invalid @enderror" value="{{ old('brand_name') }}" placeholder="Ví dụ: Panadol, Augmentin...">
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
                                <input type="text" name="generic_name" class="form-control @error('generic_name') is-invalid @enderror" value="{{ old('generic_name') }}" placeholder="Ví dụ: Paracetamol, Amoxicillin...">
                                @error('generic_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-700">Nhóm điều trị / Phân loại</label>
                                <input type="text" name="category" class="form-control @error('category') is-invalid @enderror" value="{{ old('category') }}" placeholder="Ví dụ: Giảm đau - hạ sốt, Kháng sinh...">
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
                                <input type="text" name="strength" class="form-control @error('strength') is-invalid @enderror" value="{{ old('strength') }}" placeholder="500mg, 250mg/5ml...">
                                @error('strength')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-700">Dạng bào chế</label>
                                <input type="text" name="dosage_form" class="form-control @error('dosage_form') is-invalid @enderror" value="{{ old('dosage_form') }}" placeholder="Viên nén, Siro, Hỗn dịch...">
                                @error('dosage_form')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-700">Đơn vị tính <span class="text-danger">*</span></label>
                                <select name="unit" class="form-control @error('unit') is-invalid @enderror" required>
                                    <option value="Viên" {{ old('unit') == 'Viên' ? 'selected' : '' }}>Viên</option>
                                    <option value="Vỉ" {{ old('unit') == 'Vỉ' ? 'selected' : '' }}>Vỉ</option>
                                    <option value="Hộp" {{ old('unit') == 'Hộp' ? 'selected' : '' }}>Hộp</option>
                                    <option value="Chai" {{ old('unit') == 'Chai' ? 'selected' : '' }}>Chai</option>
                                    <option value="Lọ" {{ old('unit') == 'Lọ' ? 'selected' : '' }}>Lọ</option>
                                    <option value="Ống" {{ old('unit') == 'Ống' ? 'selected' : '' }}>Ống</option>
                                    <option value="Gói" {{ old('unit') == 'Gói' ? 'selected' : '' }}>Gói</option>
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
                                <input type="number" name="price" class="form-control @error('price') is-invalid @enderror" value="{{ old('price') }}" min="0" step="100" placeholder="Đơn giá niêm yết" required>
                                @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-700">Số lượng tồn kho</label>
                                <input type="number" name="stock" class="form-control @error('stock') is-invalid @enderror" value="{{ old('stock', 100) }}" min="0">
                                @error('stock')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold text-gray-700">Hướng dẫn sử dụng & Liều dùng khuyến cáo</label>
                        <textarea name="usage" rows="3" class="form-control @error('usage') is-invalid @enderror" placeholder="Cách dùng, liều lượng khuyến nghị của bác sĩ...">{{ old('usage') }}</textarea>
                        @error('usage')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr>

                    <button type="submit" class="btn btn-primary btn-icon-split">
                        <span class="icon text-white-50"><i class="fas fa-save"></i></span>
                        <span class="text">Lưu thuốc</span>
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
        <!-- Guidelines Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-light">
                <h6 class="m-0 font-weight-bold text-secondary">
                    <i class="fas fa-info-circle mr-1"></i> Quy định Quản lý Dược phẩm
                </h6>
            </div>
            <div class="card-body">
                <p class="text-muted small">
                    Thuốc mới thêm vào danh mục sẽ có sẵn trong phân hệ Kê đơn thuốc điện tử khi Bác sĩ thực hiện khám chữa bệnh.
                </p>
                <ul class="text-muted small pl-3 mb-0">
                    <li class="mb-2"><strong>Tên biệt dược:</strong> Đặt theo tên đăng ký lưu hành của Bộ Y Tế.</li>
                    <li class="mb-2"><strong>Hoạt chất:</strong> Giúp bác sĩ tra cứu nhanh tương tác và chống chỉ định.</li>
                    <li><strong>Đơn giá:</strong> Được áp dụng tự động vào hóa đơn dịch vụ khám chữa bệnh.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
