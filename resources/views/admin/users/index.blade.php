@extends('layouts.admin')

@section('title', 'Quản lý Tài khoản Người dùng')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-1 text-gray-800 font-weight-bold">
            <i class="fas fa-users-cog text-primary mr-2"></i>Quản lý Tài khoản & Phân quyền
        </h1>
        <p class="text-muted small mb-0">Quản lý tài khoản đăng nhập, phân quyền vai trò (Admin, Bác sĩ, Bệnh nhân) và đồng bộ hồ sơ</p>
    </div>
    <div class="btn-group btn-group-sm mt-2 mt-sm-0 shadow-sm">
        <a href="{{ route('admin.users.index', array_filter(['search' => request('search')])) }}" class="btn {{ request('role') === null ? 'btn-primary font-weight-bold' : 'btn-outline-primary bg-white' }}">
            Tất cả ({{ $counts['all'] }})
        </a>
        <a href="{{ route('admin.users.index', array_filter(['role' => 'admin', 'search' => request('search')])) }}" class="btn {{ request('role') === 'admin' ? 'btn-primary font-weight-bold' : 'btn-outline-primary bg-white' }}">
            Quản trị ({{ $counts['admin'] }})
        </a>
        <a href="{{ route('admin.users.index', array_filter(['role' => 'doctor', 'search' => request('search')])) }}" class="btn {{ request('role') === 'doctor' ? 'btn-primary font-weight-bold' : 'btn-outline-primary bg-white' }}">
            Bác sĩ ({{ $counts['doctor'] }})
        </a>
        <a href="{{ route('admin.users.index', array_filter(['role' => 'patient', 'search' => request('search')])) }}" class="btn {{ request('role') === 'patient' ? 'btn-primary font-weight-bold' : 'btn-outline-primary bg-white' }}">
            Bệnh nhân ({{ $counts['patient'] }})
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
            <i class="fas fa-search mr-1"></i> Tìm kiếm tài khoản người dùng
        </h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.users.index') }}" method="GET" class="form-row align-items-end">
            @if(request()->filled('role'))
                <input type="hidden" name="role" value="{{ request('role') }}">
            @endif
            <div class="col-md-9 mb-2">
                <label class="font-weight-bold text-gray-700 small">TỪ KHÓA TÌM KIẾM</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Nhập họ tên người dùng, địa chỉ email hoặc số điện thoại...">
            </div>
            <div class="col-md-3 mb-2 d-flex">
                <button type="submit" class="btn btn-primary flex-grow-1 mr-2">
                    <i class="fas fa-search mr-1"></i> Tìm kiếm
                </button>
                @if(request()->filled('search') || request()->filled('role'))
                    <a href="{{ route('admin.users.index') }}" class="btn btn-light border" title="Xóa bộ lọc">
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
            <i class="fas fa-list mr-1"></i> Danh sách tài khoản người dùng trong hệ thống
        </h6>
        <span class="badge badge-primary px-3 py-2">
            Tổng số: {{ method_exists($users, 'total') ? $users->total() : $users->count() }} tài khoản
        </span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                <thead class="bg-light">
                    <tr>
                        <th class="text-center" style="width: 55px;">STT</th>
                        <th>Họ và tên người dùng</th>
                        <th>Email & Số điện thoại</th>
                        <th style="width: 190px;" class="text-center">Vai trò & Hồ sơ liên kết</th>
                        <th class="text-center" style="width: 140px;">Chức năng</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $i => $user)
                        <tr>
                            <!-- Cột 1: STT -->
                            <td class="text-center font-weight-bold text-gray-600 align-middle">
                                {{ method_exists($users, 'firstItem') && $users->firstItem() ? $users->firstItem() + $i : $i + 1 }}
                            </td>

                            <!-- Cột 2: Tên & ID -->
                            <td class="align-middle">
                                <div class="font-weight-bold text-gray-800">
                                    <i class="fas fa-user-circle text-primary mr-1"></i>{{ $user->name }}
                                </div>
                                <small class="text-gray-500">Mã TK: #USR-{{ str_pad($user->id, 4, '0', STR_PAD_LEFT) }}</small>
                            </td>

                            <!-- Cột 3: Email & SĐT -->
                            <td class="align-middle">
                                <div><i class="fas fa-envelope mr-1 text-primary"></i>{{ $user->email }}</div>
                                <small class="text-gray-600"><i class="fas fa-phone mr-1 text-success"></i>{{ $user->phone ?: 'Chưa cập nhật' }}</small>
                            </td>

                            <!-- Cột 4: Vai trò -->
                            <td class="text-center align-middle">
                                @php $roleName = strtolower(optional($user->role)->name ?? ''); @endphp
                                @if($roleName === 'admin')
                                    <span class="badge badge-danger px-2 py-1"><i class="fas fa-user-shield mr-1"></i>Quản trị viên</span>
                                @elseif($roleName === 'doctor')
                                    <span class="badge badge-info px-2 py-1"><i class="fas fa-user-md mr-1"></i>Bác sĩ</span>
                                    @if($user->doctor)
                                        <div class="small mt-1">
                                            <a href="{{ route('admin.doctors.edit', $user->doctor) }}" class="text-info font-weight-bold">
                                                {{ $user->doctor->department->name ?? 'Hồ sơ BS' }} &rarr;
                                            </a>
                                        </div>
                                    @endif
                                @else
                                    <span class="badge badge-success px-2 py-1"><i class="fas fa-user mr-1"></i>Bệnh nhân</span>
                                    @if($user->patient)
                                        <div class="small mt-1">
                                            <a href="{{ route('admin.patients.show', $user->patient->id) }}" class="text-success font-weight-bold">
                                                Hồ sơ #PAT-{{ str_pad($user->patient->id, 4, '0', STR_PAD_LEFT) }} &rarr;
                                            </a>
                                        </div>
                                    @endif
                                @endif
                            </td>

                            <!-- Cột 5: Chức năng -->
                            <td class="text-center align-middle">
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-info btn-circle btn-sm mr-1" title="Chỉnh sửa tài khoản & phân quyền">
                                    <i class="fas fa-user-edit"></i>
                                </a>
                                @if($user->id !== (auth('web_admin')->id() ?? auth()->id()))
                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa tài khoản này không?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-circle btn-sm" title="Xóa tài khoản">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Không tìm thấy tài khoản người dùng nào phù hợp.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($users, 'links'))
            <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap">
                <div class="small text-muted mb-2 mb-sm-0">
                    Hiển thị {{ $users->firstItem() ?? 0 }} - {{ $users->lastItem() ?? 0 }} trong tổng số {{ $users->total() }} tài khoản
                </div>
                <div>
                    {{ $users->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
