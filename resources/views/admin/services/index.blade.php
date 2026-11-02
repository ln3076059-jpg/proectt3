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
    <a href="{{ route('admin.services.create') }}" class="btn btn-primary btn-icon-split btn-sm shadow-sm mt-2 mt-sm-0">
        <span class="icon text-white-50"><i class="fas fa-plus"></i></span>
        <span class="text font-weight-bold">Thêm dịch vụ mới</span>
    </a>
</div>

@php
    $totalSrvCount = method_exists($services, 'total') ? $services->total() : $services->count();
    $avgSrvPrice = $services->count() > 0 ? round($services->avg('price')) : 0;
    $minSrvPrice = $services->count() > 0 ? $services->min('price') : 0;
    $maxSrvPrice = $services->count() > 0 ? $services->max('price') : 0;
@endphp
<!-- 3 Thẻ KPI Bảng giá & Danh mục Gói khám -->
<div class="row mb-2">
    <div class="col-md-4 mb-3">
        <div class="card border-left-primary shadow-sm h-100 py-2">
            <div class="card-body py-2 d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Tổng danh mục gói khám</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalSrvCount }} dịch vụ y tế</div>
                </div>
                <i class="fas fa-notes-medical fa-2x text-gray-300"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card border-left-success shadow-sm h-100 py-2">
            <div class="card-body py-2 d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Đơn giá niêm yết trung bình</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($avgSrvPrice, 0, ',', '.') }} VNĐ</div>
                </div>
                <i class="fas fa-tags fa-2x text-gray-300"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card border-left-info shadow-sm h-100 py-2">
            <div class="card-body py-2 d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Biên độ giá gói khám</div>
                    <div class="h6 mb-0 font-weight-bold text-gray-800">{{ number_format($minSrvPrice, 0, ',', '.') }}đ &ndash; {{ number_format($maxSrvPrice, 0, ',', '.') }}đ</div>
                </div>
                <i class="fas fa-file-invoice-dollar fa-2x text-gray-300"></i>
            </div>
        </div>
    </div>
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

<!-- Danh mục Gói Khám làm thành Card Phiếu Dịch Vụ Y Tế (Có các button y nguyên) -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-ticket-alt mr-1"></i> Danh mục Card Gói Khám &amp; Bảng Giá Dịch Vụ Y Tế tại Thái Thịnh
        </h6>
        <span class="badge badge-primary px-3 py-2">
            Tổng số: {{ method_exists($services, 'total') ? $services->total() : $services->count() }} dịch vụ
        </span>
    </div>
    <div class="card-body">
        <div class="row">
            @forelse($services as $service)
                <div class="col-xl-4 col-md-6 mb-3">
                    <div class="sb-ticket-card ticket-confirmed h-100 p-3 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge badge-info px-2 py-1">
                                    <i class="fas fa-hospital-alt mr-1"></i>{{ $service->department->name ?? 'Đa khoa' }}
                                </span>
                                <span class="font-weight-bold text-success" style="font-size: 0.96rem;">
                                    {{ number_format($service->price, 0, ',', '.') }} VNĐ
                                </span>
                            </div>

                            <div class="d-flex align-items-center mb-2">
                                @if($service->image)
                                    <img src="{{ asset('storage/'.$service->image) }}" class="rounded mr-3 border" style="width: 42px; height: 42px; object-fit: cover; flex-shrink: 0;">
                                @else
                                    <div class="bg-info text-white rounded d-flex align-items-center justify-content-center mr-3 shadow-sm" style="width: 42px; height: 42px; flex-shrink: 0;">
                                        <i class="fas fa-notes-medical"></i>
                                    </div>
                                @endif
                                <div>
                                    <div class="font-weight-bold text-primary" style="font-size: 0.94rem;">{{ $service->name }}</div>
                                    <small class="text-muted">Mã gói: #SRV-{{ str_pad($service->id, 3, '0', STR_PAD_LEFT) }}</small>
                                </div>
                            </div>

                            <div class="small text-gray-600 mb-2">
                                {{ \Illuminate\Support\Str::limit($service->description ?? 'Gói khám lâm sàng tiêu chuẩn', 95) }}
                            </div>

                            @if($service->symptoms && $service->symptoms->isNotEmpty())
                                <div class="mb-2">
                                    @foreach($service->symptoms->take(4) as $sym)
                                        <span class="badge badge-light border text-secondary mr-1 mb-1">{{ $sym->symptom_name }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <!-- CÁC BUTTON CHỨC NĂNG Y NGUYÊN -->
                        <div class="d-flex align-items-center justify-content-between pt-2" style="border-top: 1px dashed #cbd5e1;">
                            <span class="small text-muted font-weight-bold">Chức năng:</span>
                            <div class="d-flex align-items-center" style="gap: 6px;">
                                <a href="{{ route('admin.services.edit', $service) }}" class="btn btn-info btn-circle btn-sm m-0" title="Sửa dịch vụ">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.services.destroy', $service) }}" method="POST" class="d-inline m-0" onsubmit="return confirm('Bạn có chắc chắn muốn xóa dịch vụ này không?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-circle btn-sm m-0" title="Xóa dịch vụ">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="text-center text-muted py-4 border rounded bg-light">Không tìm thấy dịch vụ nào phù hợp.</div>
                </div>
            @endforelse
        </div>

        <div class="table-responsive d-none">
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
