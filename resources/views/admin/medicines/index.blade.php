@extends('layouts.admin')

@section('title', 'Quản lý Danh mục Thuốc')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800 font-weight-bold">
        <i class="fas fa-pills text-primary mr-2"></i>Quản lý Danh mục Thuốc & Dược phẩm
    </h1>
    <a href="{{ route('admin.medicines.create') }}" class="btn btn-sm btn-primary shadow-sm">
        <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Thêm thuốc mới
    </a>
</div>

{{-- Flash Alert --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle mr-2"></i>{{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

<!-- 4 Thẻ KPI Tồn kho Dược phẩm (1-Click Lọc nhanh) -->
@if(isset($stockStats))
<div class="row mb-2">
    <div class="col-xl-3 col-md-6 mb-3">
        <a href="{{ route('admin.medicines.index') }}" class="card border-left-primary shadow-sm h-100 py-2 text-decoration-none">
            <div class="card-body py-2">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Tổng danh mục thuốc</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stockStats['total'] ?? 0 }} biệt dược</div>
                    </div>
                    <div class="col-auto"><i class="fas fa-capsules fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <a href="{{ route('admin.medicines.index', ['stock_status' => 'available']) }}" class="card border-left-success shadow-sm h-100 py-2 text-decoration-none">
            <div class="card-body py-2">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Đủ tồn kho (>10)</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stockStats['available'] ?? 0 }} loại</div>
                    </div>
                    <div class="col-auto"><i class="fas fa-boxes fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <a href="{{ route('admin.medicines.index', ['stock_status' => 'low']) }}" class="card border-left-warning shadow-sm h-100 py-2 text-decoration-none">
            <div class="card-body py-2">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Sắp hết hàng (&le;10)</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stockStats['low'] ?? 0 }} loại</div>
                    </div>
                    <div class="col-auto"><i class="fas fa-exclamation-triangle fa-2x text-warning"></i></div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <a href="{{ route('admin.medicines.index', ['stock_status' => 'out']) }}" class="card border-left-danger shadow-sm h-100 py-2 text-decoration-none">
            <div class="card-body py-2">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Hết hàng (=0)</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stockStats['out'] ?? 0 }} loại</div>
                    </div>
                    <div class="col-auto"><i class="fas fa-ban fa-2x text-danger"></i></div>
                </div>
            </div>
        </a>
    </div>
</div>
@endif

<!-- Search & Filter Card -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-search mr-1"></i> Tìm kiếm & Lọc kho thuốc
        </h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.medicines.index') }}" method="GET" class="form-row align-items-end">
            <div class="col-md-5 mb-2">
                <label class="font-weight-bold text-gray-700 small">TỪ KHÓA TÌM KIẾM</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Tên thuốc, biệt dược, hoạt chất...">
            </div>
            <div class="col-md-3 mb-2">
                <label class="font-weight-bold text-gray-700 small">NHÓM ĐIỀU TRỊ</label>
                <select name="category" class="form-control">
                    <option value="">-- Tất cả nhóm thuốc --</option>
                    @foreach($categories ?? [] as $cat)
                        <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <label class="font-weight-bold text-gray-700 small">TÌNH TRẠNG KHO</label>
                <select name="stock_status" class="form-control">
                    <option value="">-- Tất cả --</option>
                    <option value="available" {{ request('stock_status') == 'available' ? 'selected' : '' }}>Còn hàng (>10)</option>
                    <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>Sắp hết (<=10)</option>
                    <option value="out" {{ request('stock_status') == 'out' ? 'selected' : '' }}>Hết hàng (=0)</option>
                </select>
            </div>
            <div class="col-md-2 mb-2 d-flex">
                <button type="submit" class="btn btn-primary flex-grow-1 mr-1">
                    <i class="fas fa-search mr-1"></i> Lọc
                </button>
                @if(request()->filled('search') || request()->filled('category') || request()->filled('stock_status'))
                    <a href="{{ route('admin.medicines.index') }}" class="btn btn-light border" title="Xóa bộ lọc">
                        <i class="fas fa-redo-alt"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- DataTales (ĐÚNG 5 CỘT BAO GỒM 1 CỘT CHỨC NĂNG) -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">

        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-table mr-1"></i> Danh mục thuốc & biệt dược được cấp phép
        </h6>
        <span class="badge badge-primary px-3 py-2">Tổng số: {{ $medicines->total() ?? $medicines->count() }} loại thuốc</span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 60px;">STT</th>
                        <th>Tên biệt dược & Nhóm điều trị</th>
                        <th>Hoạt chất & Hàm lượng</th>
                        <th style="width: 200px;" class="text-right">Đơn vị, Giá & Tồn kho</th>
                        <th class="text-center" style="width: 140px;">Chức năng</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($medicines as $medicine)
                        <tr>
                            <!-- Cột 1: STT -->
                            <td class="text-center font-weight-bold text-gray-600 align-middle">
                                {{ method_exists($medicines, 'firstItem') ? $medicines->firstItem() + $loop->index : $loop->iteration }}
                            </td>

                            <!-- Cột 2: Tên thuốc & Phân loại -->
                            <td class="align-middle">
                                <div class="font-weight-bold text-primary">
                                    <i class="fas fa-tablets mr-1 text-info"></i>{{ $medicine->name }}
                                </div>
                                <div class="small text-gray-500">
                                    Biệt dược: <span class="font-weight-bold text-gray-700">{{ $medicine->brand_name ?? '—' }}</span>
                                    | Nhóm: <span class="badge badge-light border">{{ $medicine->category ?? 'Đa khoa' }}</span>
                                </div>
                            </td>

                            <!-- Cột 3: Hoạt chất & Hàm lượng -->
                            <td class="align-middle">
                                <div class="text-gray-800 font-weight-bold">
                                    {{ $medicine->generic_name ?? 'Chưa cập nhật' }}
                                </div>
                                <small class="text-muted d-block">
                                    Hàm lượng: {{ $medicine->strength ?? 'Chuẩn' }} 
                                    @if($medicine->dosage_form)
                                        | Dạng: {{ $medicine->dosage_form }}
                                    @endif
                                </small>
                            </td>

                            <!-- Cột 4: Đơn vị, Giá & Tồn kho -->
                            <td class="text-right align-middle">
                                <div class="font-weight-bold text-success">
                                    {{ number_format($medicine->price ?? 0, 0, ',', '.') }} VNĐ
                                    <small class="text-gray-500">/ {{ $medicine->unit ?? 'Viên' }}</small>
                                </div>
                                <div class="mt-1">
                                    @if(($medicine->stock ?? 0) <= ($medicine->min_stock ?? 10))
                                        <span class="badge badge-danger">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>Sắp hết ({{ $medicine->stock ?? 0 }})
                                        </span>
                                    @else
                                        <span class="badge badge-success">
                                            <i class="fas fa-check mr-1"></i>Còn {{ $medicine->stock ?? 0 }} {{ $medicine->unit ?? 'viên' }}
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- Cột 5: Chức năng -->
                            <td class="text-center align-middle">
                                <a href="{{ route('admin.medicines.edit', $medicine) }}" class="btn btn-info btn-circle btn-sm mr-1" title="Sửa thông tin thuốc">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.medicines.destroy', $medicine) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa thuốc này khỏi kho?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-circle btn-sm" title="Xóa thuốc">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Chưa có dữ liệu dược phẩm trong hệ thống.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($medicines, 'links'))
            <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap">
                <div class="small text-muted mb-2 mb-sm-0">
                    Hiển thị {{ $medicines->firstItem() ?? 0 }} - {{ $medicines->lastItem() ?? 0 }} trong tổng số {{ $medicines->total() }} loại thuốc
                </div>
                <div>
                    {{ $medicines->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
