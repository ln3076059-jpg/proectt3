@extends('layouts.admin')

@section('title', 'Quản lý Đội ngũ Bác sĩ')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-1 text-gray-800 font-weight-bold">
            <i class="fas fa-user-md text-primary mr-2"></i>Quản lý Đội ngũ Bác sĩ
        </h1>
        <p class="text-muted small mb-0">Quản lý thông tin chuyên môn, chứng chỉ hành nghề và điều phối lịch trực của đội ngũ y bác sĩ</p>
    </div>
    <div class="mt-2 mt-sm-0">
        <button type="button" id="exportAdminDoctorsCsvBtn" class="btn btn-sm btn-outline-success shadow-sm mr-2">
            <i class="fas fa-file-csv fa-sm mr-1"></i> Xuất CSV Bác sĩ
        </button>
        <a href="{{ route('admin.schedules.create') }}" class="btn btn-sm btn-outline-primary shadow-sm mr-2">
            <i class="fas fa-calendar-plus fa-sm mr-1"></i> Phân lịch ca trực
        </a>
        <a href="{{ route('admin.doctors.create') }}" class="btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Thêm bác sĩ mới
        </a>
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
            <i class="fas fa-filter mr-1"></i> Tìm kiếm & Lọc bác sĩ theo chuyên khoa
        </h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.doctors.index') }}" method="GET" class="form-row align-items-end">
            <div class="col-md-5 mb-2">
                <label class="font-weight-bold text-gray-700 small">TỪ KHÓA TÌM KIẾM</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Nhập tên bác sĩ, email, SĐT, mã CCHN, chuyên ngành...">
            </div>
            <div class="col-md-3 mb-2">
                <label class="font-weight-bold text-gray-700 small">CHUYÊN KHOA</label>
                <select name="department_id" class="form-control">
                    <option value="">-- Tất cả chuyên khoa --</option>
                    @foreach($departments ?? [] as $dept)
                        <option value="{{ $dept->id }}" {{ (string) request('department_id') === (string) $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <label class="font-weight-bold text-gray-700 small">TRẠNG THÁI</label>
                <select name="is_active" class="form-control">
                    <option value="">-- Tất cả --</option>
                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Đang công tác</option>
                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Tạm ngưng</option>
                </select>
            </div>
            <div class="col-md-2 mb-2 d-flex">
                <button type="submit" class="btn btn-primary flex-grow-1 mr-1">
                    <i class="fas fa-search mr-1"></i> Lọc
                </button>
                @if(request()->filled('search') || request()->filled('department_id') || request()->filled('is_active'))
                    <a href="{{ route('admin.doctors.index') }}" class="btn btn-light border" title="Xóa bộ lọc">
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
            <i class="fas fa-list mr-1"></i> Danh sách bác sĩ chuyên khoa phụ trách khám chữa bệnh
        </h6>
        <span class="badge badge-primary px-3 py-2">
            Tổng số: {{ method_exists($doctors, 'total') ? $doctors->total() : $doctors->count() }} bác sĩ
        </span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                <thead class="bg-light">
                    <tr>
                        <th class="text-center" style="width: 55px;">STT</th>
                        <th>Họ tên Bác sĩ & Chức danh</th>
                        <th>Chuyên khoa & Chứng chỉ</th>
                        <th style="width: 240px;">Liên hệ & Lượt khám</th>
                        <th class="text-center" style="width: 170px;">Chức năng</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($doctors as $doctor)
                        <tr>
                            <!-- Cột 1: STT -->
                            <td class="text-center font-weight-bold text-gray-600 align-middle">
                                {{ method_exists($doctors, 'firstItem') ? $doctors->firstItem() + $loop->index : $loop->iteration }}
                            </td>

                            <!-- Cột 2: Bác sĩ -->
                            <td class="align-middle">
                                <div class="d-flex align-items-center">
                                    @if($doctor->avatar)
                                        <img src="{{ asset('storage/'.$doctor->avatar) }}" class="rounded-circle mr-3 border" style="width: 46px; height: 46px; object-fit: cover;">
                                    @else
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mr-3 shadow-sm" style="width: 46px; height: 46px; flex-shrink: 0;">
                                            <i class="fas fa-user-md"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="font-weight-bold text-primary">
                                            {{ $doctor->user->name ?? 'BS. '.$doctor->name }}
                                            @if($doctor->is_active ?? true)
                                                <span class="badge badge-success ml-1" style="font-size: 10px;">Đang trực</span>
                                            @else
                                                <span class="badge badge-secondary ml-1" style="font-size: 10px;">Tạm ngưng</span>
                                            @endif
                                        </div>
                                        <small class="text-gray-600 d-block">{{ $doctor->specialization ?? 'Bác sĩ chuyên khoa' }}</small>
                                    </div>
                                </div>
                            </td>

                            <!-- Cột 3: Chuyên khoa -->
                            <td class="align-middle">
                                <div class="mb-1">
                                    <span class="badge badge-info px-2 py-1">
                                        <i class="fas fa-hospital-alt mr-1"></i>{{ $doctor->department->name ?? 'Đa khoa' }}
                                    </span>
                                </div>
                                <small class="text-gray-600">
                                    <i class="fas fa-id-badge text-muted mr-1"></i>CCHN: <strong>{{ $doctor->license_number ?: 'Đang cập nhật' }}</strong>
                                </small>
                            </td>

                            <!-- Cột 4: Liên hệ & Lượt khám -->
                            <td class="align-middle">
                                <div class="small text-gray-800"><i class="fas fa-envelope mr-1 text-primary"></i>{{ $doctor->user->email ?? $doctor->email }}</div>
                                <div class="small text-gray-600 mt-1"><i class="fas fa-phone mr-1 text-success"></i>{{ $doctor->user->phone ?? $doctor->phone ?? 'Chưa cập nhật' }}</div>
                                <div class="mt-1">
                                    <span class="badge badge-light border text-primary">
                                        <i class="fas fa-calendar-check mr-1"></i>{{ $doctor->appointments_count ?? 0 }} ca hẹn
                                    </span>
                                </div>
                            </td>

                            <!-- Cột 5: Chức năng -->
                            <td class="text-center align-middle">
                                <a href="{{ route('admin.doctors.schedule', $doctor) }}" class="btn btn-primary btn-circle btn-sm mr-1" title="Xem lịch làm việc & bệnh nhân trong ngày">
                                    <i class="fas fa-calendar-alt"></i>
                                </a>
                                <a href="{{ route('admin.schedules.create', ['doctor_id' => $doctor->id]) }}" class="btn btn-success btn-circle btn-sm mr-1" title="Tạo ca trực mới cho bác sĩ">
                                    <i class="fas fa-calendar-plus"></i>
                                </a>
                                <a href="{{ route('admin.doctors.edit', $doctor) }}" class="btn btn-info btn-circle btn-sm mr-1" title="Chỉnh sửa hồ sơ">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.doctors.destroy', $doctor) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bác sĩ này?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-circle btn-sm" title="Xóa bác sĩ">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Không tìm thấy bác sĩ nào phù hợp với bộ lọc.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($doctors, 'links'))
            <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap">
                <div class="small text-muted mb-2 mb-sm-0">
                    Hiển thị {{ $doctors->firstItem() ?? 0 }} - {{ $doctors->lastItem() ?? 0 }} trong tổng số {{ $doctors->total() }} bác sĩ
                </div>
                <div>
                    {{ $doctors->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('exportAdminDoctorsCsvBtn')?.addEventListener('click', function () {
        var rows = [['STT', 'Họ tên Bác sĩ & Chức danh', 'Chuyên khoa & Chứng chỉ', 'Liên hệ & Lượt khám']];
        document.querySelectorAll('table.table tbody tr').forEach(function (tr) {
            var cols = tr.querySelectorAll('td');
            if (cols.length >= 4) {
                rows.push([
                    cols[0].innerText.replace(/\s+/g, ' ').trim(),
                    cols[1].innerText.replace(/\s+/g, ' ').trim(),
                    cols[2].innerText.replace(/\s+/g, ' ').trim(),
                    cols[3].innerText.replace(/\s+/g, ' ').trim()
                ]);
            }
        });
        var csvContent = '\uFEFF' + rows.map(function (r) {
            return r.map(function (c) { return '"' + String(c).replace(/"/g, '""') + '"'; }).join(',');
        }).join('\n');
        var blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        var link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'danh-sach-bac-si-' + new Date().toISOString().slice(0, 10) + '.csv';
        link.click();
    });
</script>
@endpush
