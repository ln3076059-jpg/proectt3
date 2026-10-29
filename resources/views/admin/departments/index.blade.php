@extends('layouts.admin')

@section('title', 'Quản lý Khoa phòng')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-1 text-gray-800 font-weight-bold">
            <i class="fas fa-hospital-alt text-primary mr-2"></i>Quản lý Khoa phòng
        </h1>
        <p class="text-muted small mb-0">Quản lý danh sách các chuyên khoa, quy mô nhân sự bác sĩ và gói dịch vụ trực thuộc</p>
    </div>
    <a href="{{ route('admin.departments.create') }}" class="btn btn-sm btn-primary shadow-sm mt-2 mt-sm-0">
        <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Thêm khoa mới
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
            <i class="fas fa-filter mr-1"></i> Tìm kiếm & Lọc chuyên khoa
        </h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.departments.index') }}" method="GET" class="form-row align-items-end">
            <div class="col-md-7 mb-2">
                <label class="font-weight-bold text-gray-700 small">TÊN CHUYÊN KHOA / MÔ TẢ</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Nhập tên chuyên khoa hoặc nội dung hoạt động chuyên môn...">
            </div>
            <div class="col-md-3 mb-2">
                <label class="font-weight-bold text-gray-700 small">TRẠNG THÁI HOẠT ĐỘNG</label>
                <select name="is_active" class="form-control">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Đang mở tiếp nhận</option>
                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Tạm khóa</option>
                </select>
            </div>
            <div class="col-md-2 mb-2 d-flex">
                <button type="submit" class="btn btn-primary flex-grow-1 mr-1">
                    <i class="fas fa-search mr-1"></i> Lọc
                </button>
                @if(request()->filled('search') || request()->filled('is_active'))
                    <a href="{{ route('admin.departments.index') }}" class="btn btn-light border" title="Xóa bộ lọc">
                        <i class="fas fa-redo-alt"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- DataTales Example (5 CỘT BAO GỒM 1 CỘT CHỨC NĂNG) -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-list mr-1"></i> Danh sách các chuyên khoa tại 125 Thái Thịnh
        </h6>
        <span class="badge badge-primary px-3 py-2">
            Tổng số: {{ method_exists($departments, 'total') ? $departments->total() : $departments->count() }} chuyên khoa
        </span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                <thead class="bg-light">
                    <tr>
                        <th class="text-center" style="width: 55px;">STT</th>
                        <th style="width: 260px;">Tên chuyên khoa</th>
                        <th class="text-center" style="width: 210px;">Quy mô & Trạng thái</th>
                        <th>Mô tả hoạt động chuyên môn</th>
                        <th class="text-center" style="width: 150px;">Chức năng</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($departments as $department)
                        <tr>
                            <!-- Cột 1: STT -->
                            <td class="text-center font-weight-bold text-gray-600 align-middle">
                                {{ method_exists($departments, 'firstItem') ? $departments->firstItem() + $loop->index : $loop->iteration }}
                            </td>

                            <!-- Cột 2: Tên chuyên khoa & ảnh -->
                            <td class="align-middle">
                                <div class="d-flex align-items-center">
                                    @if($department->image)
                                        <img src="{{ asset('storage/'.$department->image) }}" alt="{{ $department->name }}" class="rounded mr-3 border" style="width: 44px; height: 44px; object-fit: cover;">
                                    @else
                                        <div class="bg-primary text-white rounded d-flex align-items-center justify-content-center mr-3 shadow-sm" style="width: 44px; height: 44px; flex-shrink: 0;">
                                            <i class="fas fa-clinic-medical"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="font-weight-bold text-primary">{{ $department->name }}</div>
                                        <small class="text-gray-500">Mã: #KHOA-{{ str_pad($department->id, 3, '0', STR_PAD_LEFT) }}</small>
                                    </div>
                                </div>
                            </td>

                            <!-- Cột 3: Quy mô & Trạng thái -->
                            <td class="text-center align-middle">
                                <div class="mb-1">
                                    @if($department->is_active ?? true)
                                        <span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i>Đang mở</span>
                                    @else
                                        <span class="badge badge-secondary px-2 py-1"><i class="fas fa-lock mr-1"></i>Tạm khóa</span>
                                    @endif
                                </div>
                                <div class="small text-muted">
                                    <a href="{{ route('admin.doctors.index', ['department_id' => $department->id]) }}" class="text-primary font-weight-bold mr-2">
                                        <i class="fas fa-user-md mr-1"></i>{{ $department->doctors_count ?? 0 }} BS
                                    </a>
                                    <a href="{{ route('admin.services.index', ['department_id' => $department->id]) }}" class="text-info font-weight-bold">
                                        <i class="fas fa-stethoscope mr-1"></i>{{ $department->services_count ?? 0 }} DV
                                    </a>
                                </div>
                            </td>

                            <!-- Cột 4: Mô tả -->
                            <td class="text-gray-700 align-middle">
                                {{ \Illuminate\Support\Str::limit($department->description ?? 'Đang cập nhật thông tin giới thiệu chuyên khoa.', 120) }}
                            </td>

                            <!-- Cột 5: Chức năng -->
                            <td class="text-center align-middle">
                                <a href="{{ route('admin.doctors.index', ['department_id' => $department->id]) }}" class="btn btn-primary btn-circle btn-sm mr-1" title="Xem bác sĩ thuộc khoa">
                                    <i class="fas fa-user-md"></i>
                                </a>
                                <a href="{{ route('admin.departments.edit', $department) }}" class="btn btn-info btn-circle btn-sm mr-1" title="Chỉnh sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.departments.destroy', $department) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa chuyên khoa này không?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-circle btn-sm" title="Xóa khoa">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Không tìm thấy chuyên khoa nào phù hợp.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($departments, 'links'))
            <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap">
                <div class="small text-muted mb-2 mb-sm-0">
                    Hiển thị {{ $departments->firstItem() ?? 0 }} - {{ $departments->lastItem() ?? 0 }} trong tổng số {{ $departments->total() }} chuyên khoa
                </div>
                <div>
                    {{ $departments->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
