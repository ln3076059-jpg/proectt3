@extends('layouts.admin')

@section('title', 'Quản lý Danh mục Bệnh')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800 font-weight-bold">
        <i class="fas fa-virus text-primary mr-2"></i>Quản lý Danh mục Bệnh lý & Bệnh án
    </h1>
    <a href="{{ route('admin.diseases.create') }}" class="btn btn-sm btn-primary shadow-sm">
        <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Thêm bệnh mới
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
            <i class="fas fa-filter mr-1"></i> Tìm kiếm & Lọc mặt bệnh lý / triệu chứng
        </h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.diseases.index') }}" method="GET" class="form-row align-items-end">
            <div class="col-md-6 mb-2">
                <label class="font-weight-bold text-gray-700 small">TÊN BỆNH LÝ / TRIỆU CHỨNG LÂM SÀNG</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Nhập tên bệnh lý, mô tả hoặc triệu chứng điển hình...">
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
                    <a href="{{ route('admin.diseases.index') }}" class="btn btn-light border" title="Xóa bộ lọc">
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
        <h6 class="m-0 font-weight-bold text-primary">Danh mục các mặt bệnh lý theo chuyên khoa</h6>
        <span class="badge badge-primary px-3 py-2">
            Tổng số: {{ method_exists($diseases, 'total') ? $diseases->total() : $diseases->count() }} mặt bệnh
        </span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 50px;">STT</th>
                        <th>Tên bệnh lý & Phân loại</th>
                        <th style="width: 220px;">Khoa phụ trách</th>
                        <th>Triệu chứng lâm sàng điển hình</th>
                        <th class="text-center" style="width: 130px;">Chức năng</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($diseases as $disease)
                        <tr>
                            <!-- Cột 1: STT -->
                            <td class="text-center font-weight-bold text-gray-600">
                                {{ $loop->iteration }}
                            </td>

                            <!-- Cột 2: Tên bệnh -->
                            <td>
                                <div class="font-weight-bold text-primary">
                                    <i class="fas fa-file-medical-alt text-danger mr-1"></i>{{ $disease->name }}
                                </div>
                                <small class="text-gray-500">Mã: #{{ str_pad($disease->id, 4, '0', STR_PAD_LEFT) }}</small>
                            </td>

                            <!-- Cột 3: Khoa -->
                            <td>
                                <span class="badge badge-info px-2 py-1">
                                    <i class="fas fa-hospital-alt mr-1"></i>{{ $disease->department->name ?? 'Đa khoa' }}
                                </span>
                            </td>

                            <!-- Cột 4: Triệu chứng -->
                            <td class="text-gray-700">
                                @if($disease->symptoms && $disease->symptoms->isNotEmpty())
                                    @foreach($disease->symptoms as $sym)
                                        <span class="badge badge-light border text-gray-800 mr-1 mb-1">{{ $sym->symptom_name }}</span>
                                    @endforeach
                                @elseif(!empty($disease->description))
                                    {{ \Illuminate\Support\Str::limit($disease->description, 100) }}
                                @else
                                    <span class="text-muted small">Chưa cập nhật triệu chứng</span>
                                @endif
                            </td>

                            <!-- Cột 5: Chức năng -->
                            <td class="text-center">
                                <a href="{{ route('admin.diseases.edit', $disease) }}" class="btn btn-info btn-circle btn-sm mr-1" title="Sửa mặt bệnh">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.diseases.destroy', $disease) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bệnh lý này?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-circle btn-sm" title="Xóa bệnh">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Chưa có bệnh nào trong danh mục.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($diseases, 'links'))
            <div class="mt-3">
                {{ $diseases->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
