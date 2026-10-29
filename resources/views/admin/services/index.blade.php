@extends('layouts.admin')

@section('title', 'Quản lý Dịch vụ khám')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-1 text-gray-800 font-weight-bold">
            <i class="fas fa-stethoscope text-primary mr-2"></i>Quản lý Dịch vụ khám bệnh
        </h1>
        <p class="text-muted small mb-0">Quản lý danh mục gói khám, bảng giá niêm yết và từ khóa triệu chứng gợi ý</p>
    </div>
    <a href="{{ route('admin.services.create') }}" class="btn btn-sm btn-primary shadow-sm mt-2 mt-sm-0">
        <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Thêm dịch vụ mới
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
            <i class="fas fa-filter mr-1"></i> Tìm kiếm & Lọc gói dịch vụ y tế
        </h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.services.index') }}" method="GET" class="form-row align-items-end">
            <div class="col-md-6 mb-2">
                <label class="font-weight-bold text-gray-700 small">TỪ KHÓA DỊCH VỤ / TRIỆU CHỨNG</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Nhập tên gói khám, mô tả hoặc triệu chứng...">
            </div>
            <div class="col-md-4 mb-2">
                <label class="font-weight-bold text-gray-700 small">CHUYÊN KHOA PHỤ TRÁCH</label>
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
                    <a href="{{ route('admin.services.index') }}" class="btn btn-light border" title="Xóa bộ lọc">
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
            <i class="fas fa-list mr-1"></i> Danh mục bảng giá dịch vụ y tế tại Thái Thịnh
        </h6>
        <span class="badge badge-primary px-3 py-2">
            Tổng số: {{ method_exists($services, 'total') ? $services->total() : $services->count() }} dịch vụ
        </span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                <thead class="bg-light">
                    <tr>
                        <th class="text-center" style="width: 55px;">STT</th>
                        <th>Tên gói khám & Dịch vụ</th>
                        <th style="width: 220px;">Khoa phụ trách</th>
                        <th style="width: 170px;" class="text-right">Đơn giá niêm yết</th>
                        <th class="text-center" style="width: 130px;">Chức năng</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($services as $service)
                        <tr>
                            <!-- Cột 1: STT -->
                            <td class="text-center font-weight-bold text-gray-600 align-middle">
                                {{ method_exists($services, 'firstItem') ? $services->firstItem() + $loop->index : $loop->iteration }}
                            </td>

                            <!-- Cột 2: Tên dịch vụ & ảnh -->
                            <td class="align-middle">
                                <div class="d-flex align-items-center">
                                    @if($service->image)
                                        <img src="{{ asset('storage/'.$service->image) }}" class="rounded mr-3 border" style="width: 44px; height: 44px; object-fit: cover;">
                                    @else
                                        <div class="bg-info text-white rounded d-flex align-items-center justify-content-center mr-3 shadow-sm" style="width: 44px; height: 44px; flex-shrink: 0;">
                                            <i class="fas fa-notes-medical"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="font-weight-bold text-primary">{{ $service->name }}</div>
                                        <small class="text-gray-600 d-block">{{ \Illuminate\Support\Str::limit($service->description ?? 'Không có mô tả', 85) }}</small>
                                        @if($service->symptoms && $service->symptoms->isNotEmpty())
                                            <div class="mt-1">
                                                @foreach($service->symptoms->take(4) as $sym)
                                                    <span class="badge badge-light border text-secondary mr-1">{{ $sym->symptom_name }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Cột 3: Khoa -->
                            <td class="align-middle">
                                <span class="badge badge-info px-2 py-1">
                                    <i class="fas fa-hospital-alt mr-1"></i>{{ $service->department->name ?? 'Đa khoa' }}
                                </span>
                            </td>

                            <!-- Cột 4: Giá -->
                            <td class="text-right font-weight-bold text-success align-middle" style="font-size: 1rem;">
                                {{ number_format($service->price, 0, ',', '.') }} VNĐ
                            </td>

                            <!-- Cột 5: Chức năng -->
                            <td class="text-center align-middle">
                                <a href="{{ route('admin.services.edit', $service) }}" class="btn btn-info btn-circle btn-sm mr-1" title="Sửa dịch vụ">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.services.destroy', $service) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa dịch vụ này không?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-circle btn-sm" title="Xóa dịch vụ">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Không tìm thấy dịch vụ nào phù hợp.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($services, 'links'))
            <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap">
                <div class="small text-muted mb-2 mb-sm-0">
                    Hiển thị {{ $services->firstItem() ?? 0 }} - {{ $services->lastItem() ?? 0 }} trong tổng số {{ $services->total() }} dịch vụ
                </div>
                <div>
                    {{ $services->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
