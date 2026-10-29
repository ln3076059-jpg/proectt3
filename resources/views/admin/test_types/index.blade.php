@extends('layouts.admin')

@section('title', 'Quản lý Loại xét nghiệm')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-1 text-gray-800 font-weight-bold">
            <i class="fas fa-tags text-primary mr-2"></i>Quản lý Danh mục Loại xét nghiệm
        </h1>
        <p class="text-muted small mb-0">Cấu hình danh mục các chỉ định xét nghiệm cận lâm sàng và khoa phòng phụ trách</p>
    </div>
    <a href="{{ route('admin.test-types.create') }}" class="btn btn-sm btn-primary shadow-sm mt-2 mt-sm-0">
        <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Thêm loại xét nghiệm
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

<!-- Search & Filter Card -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-filter mr-1"></i> Tìm kiếm & Lọc danh mục xét nghiệm
        </h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.test-types.index') }}" method="GET" class="form-row align-items-end">
            <div class="col-md-6 mb-2">
                <label class="font-weight-bold text-gray-700 small">TÊN XÉT NGHIỆM / MÔ TẢ CHỈ SỐ</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Nhập tên loại xét nghiệm hoặc mô tả chỉ số chuẩn...">
            </div>
            <div class="col-md-4 mb-2">
                <label class="font-weight-bold text-gray-700 small">KHOA PHỤ TRÁCH</label>
                <select name="department_id" class="form-control">
                    <option value="">-- Tất cả chuyên khoa --</option>
                    @foreach($departments ?? [] as $dept)
                        <option value="{{ $dept->id }}" {{ (string) request('department_id') === (string) $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 mb-2 d-flex">
                <button type="submit" class="btn btn-primary flex-grow-1 mr-1">
                    <i class="fas fa-search mr-1"></i> Lọc
                </button>
                @if(request()->filled('search') || request()->filled('department_id'))
                    <a href="{{ route('admin.test-types.index') }}" class="btn btn-light border" title="Xóa bộ lọc">
                        <i class="fas fa-redo-alt"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- DataTales (5 CỘT BAO GỒM 1 CỘT CHỨC NĂNG) -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-vials mr-1"></i> Danh mục các loại xét nghiệm cận lâm sàng
        </h6>
        <span class="badge badge-primary px-3 py-2">
            Tổng số: {{ method_exists($types, 'total') ? $types->total() : $types->count() }} danh mục
        </span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                <thead class="bg-light">
                    <tr>
                        <th class="text-center" style="width: 55px;">STT</th>
                        <th>Tên loại xét nghiệm</th>
                        <th style="width: 220px;">Khoa phụ trách</th>
                        <th>Mô tả / Chỉ số chuẩn</th>
                        <th class="text-center" style="width: 130px;">Chức năng</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($types as $type)
                        <tr>
                            <!-- Cột 1: STT -->
                            <td class="text-center font-weight-bold text-gray-600 align-middle">
                                {{ method_exists($types, 'firstItem') ? $types->firstItem() + $loop->index : $loop->iteration }}
                            </td>

                            <!-- Cột 2: Tên loại -->
                            <td class="align-middle">
                                <div class="font-weight-bold text-primary">
                                    <i class="fas fa-vial text-info mr-1"></i>{{ $type->name }}
                                </div>
                                <small class="text-gray-500">Mã: #XN-{{ str_pad($type->id, 4, '0', STR_PAD_LEFT) }}</small>
                            </td>

                            <!-- Cột 3: Khoa -->
                            <td class="align-middle">
                                <span class="badge badge-info px-2 py-1">
                                    <i class="fas fa-hospital-alt mr-1"></i>{{ $type->department->name ?? 'Xét nghiệm cận lâm sàng' }}
                                </span>
                            </td>

                            <!-- Cột 4: Mô tả -->
                            <td class="text-gray-700 align-middle">
                                {{ \Illuminate\Support\Str::limit($type->description ?? 'Không có mô tả chi tiết', 110) }}
                            </td>

                            <!-- Cột 5: Chức năng -->
                            <td class="text-center align-middle">
                                <a href="{{ route('admin.test-types.edit', $type->id) }}" class="btn btn-info btn-circle btn-sm mr-1" title="Sửa loại xét nghiệm">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.test-types.destroy', $type->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa loại xét nghiệm này?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-circle btn-sm" title="Xóa loại xét nghiệm">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Không tìm thấy loại xét nghiệm nào phù hợp.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($types, 'links'))
            <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap">
                <div class="small text-muted mb-2 mb-sm-0">
                    Hiển thị {{ $types->firstItem() ?? 0 }} - {{ $types->lastItem() ?? 0 }} trong tổng số {{ $types->total() }} loại xét nghiệm
                </div>
                <div>
                    {{ $types->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
