@extends('layouts.admin')

@section('title', 'Quản lý Hồ sơ Bệnh nhân')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-1 text-gray-800 font-weight-bold">
            <i class="fas fa-user-injured text-primary mr-2"></i>Quản lý Hồ sơ Bệnh nhân
        </h1>
        <p class="text-muted small mb-0">Tra cứu thông tin hành chính, thẻ BHYT và lịch sử khám bệnh của bệnh nhân</p>
    </div>
    <div class="mt-2 mt-sm-0 d-flex align-items-center" style="gap: 8px;">
        <button type="button" id="exportAdminPatientsCsvBtn" class="btn btn-success btn-icon-split btn-sm shadow-sm m-0">
            <span class="icon text-white-50"><i class="fas fa-file-csv"></i></span>
            <span class="text">Xuất CSV Bệnh nhân</span>
        </button>
        <a href="{{ route('admin.patients.create') }}" class="btn btn-primary btn-icon-split btn-sm shadow-sm m-0">
            <span class="icon text-white-50"><i class="fas fa-plus"></i></span>
            <span class="text font-weight-bold">Thêm bệnh nhân mới</span>
        </a>
    </div>
</div>

@php
    $totalPatCount = method_exists($patients, 'total') ? $patients->total() : $patients->count();
    $bhytPatCount = $patients->filter(fn($p) => !empty($p->insurance_number))->count();
    $phonePatCount = $patients->filter(fn($p) => !empty($p->phone))->count();
@endphp
<!-- 3 Thẻ KPI Hồ sơ Bệnh nhân & Bảo hiểm Y tế -->
<div class="row mb-2">
    <div class="col-md-4 mb-3">
        <div class="card border-left-primary shadow-sm h-100 py-2">
            <div class="card-body py-2 d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Tổng hồ sơ bệnh nhân</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalPatCount }} mã định danh</div>
                </div>
                <i class="fas fa-users fa-2x text-gray-300"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card border-left-success shadow-sm h-100 py-2">
            <div class="card-body py-2 d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Hồ sơ có thẻ BHYT (Trang hiện tại)</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $bhytPatCount }} thẻ đã liên kết</div>
                </div>
                <i class="fas fa-id-card-alt fa-2x text-gray-300"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card border-left-info shadow-sm h-100 py-2">
            <div class="card-body py-2 d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Đã xác thực SĐT liên lạc</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $phonePatCount }} / {{ $patients->count() }} hồ sơ</div>
                </div>
                <i class="fas fa-phone-volume fa-2x text-gray-300"></i>
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
            <i class="fas fa-search mr-1"></i> Tìm kiếm nhanh Hồ sơ Bệnh nhân
        </h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.patients.index') }}" method="GET" class="form-row align-items-end">
            <div class="col-md-9 mb-2">
                <label class="font-weight-bold text-gray-700 small">TỪ KHÓA TRA CỨU</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Nhập họ tên bệnh nhân, số điện thoại, email, mã thẻ BHYT hoặc địa chỉ...">
            </div>
            <div class="col-md-3 mb-2 d-flex">
                <button type="submit" class="btn btn-primary flex-grow-1 mr-2">
                    <i class="fas fa-search mr-1"></i> Tìm kiếm
                </button>
                @if(request()->filled('search'))
                    <a href="{{ route('admin.patients.index') }}" class="btn btn-light border" title="Xóa tìm kiếm">
                        <i class="fas fa-redo-alt"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Danh sách Hồ sơ Bệnh nhân làm thành Card Thẻ BHYT (Có các button y nguyên) -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-id-card-alt mr-1"></i> Danh sách Thẻ Định danh &amp; BHYT Bệnh nhân tại cơ sở 125 Thái Thịnh
        </h6>
        <span class="badge badge-primary px-3 py-2">
            Tổng số: {{ method_exists($patients, 'total') ? $patients->total() : $patients->count() }} hồ sơ
        </span>
    </div>
    <div class="card-body">
        <div class="row">
            @forelse($patients as $patient)
                @php
                    $genderLabel = match($patient->gender) {
                        'male', 'Nam' => 'Nam',
                        'female', 'Nữ' => 'Nữ',
                        default => 'Khác',
                    };
                    $genderBadge = $genderLabel === 'Nam' ? 'badge-primary' : ($genderLabel === 'Nữ' ? 'badge-info' : 'badge-secondary');
                @endphp
                <div class="col-xl-4 col-md-6 mb-3">
                    <div class="sb-bhyt-card h-100 p-3 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="d-flex align-items-center" style="gap: 8px;">
                                    <span class="sb-bhyt-chip" title="Chip định danh BHYT"></span>
                                    <span class="badge badge-light border text-muted">#PAT-{{ str_pad($patient->id, 4, '0', STR_PAD_LEFT) }}</span>
                                </div>
                                @if($patient->insurance_number)
                                    <span class="badge badge-success"><i class="fas fa-id-card mr-1"></i>BHYT: {{ $patient->insurance_number }}</span>
                                @else
                                    <span class="badge badge-secondary">Khám Dịch vụ</span>
                                @endif
                            </div>

                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <div class="font-weight-bold text-primary text-truncate" style="font-size: 0.96rem;">
                                    <i class="fas fa-user mr-1 text-info"></i>{{ $patient->name }}
                                </div>
                                <span class="badge {{ $genderBadge }} px-2 py-1">{{ $genderLabel }}</span>
                            </div>

                            <div class="small text-gray-700 border-top pt-2 mb-2">
                                <div class="d-flex justify-content-between mb-1">
                                    <span><i class="far fa-calendar mr-1 text-primary"></i>NS: <strong>{{ $patient->birthdate ? \Carbon\Carbon::parse($patient->birthdate)->format('d/m/Y') : 'Chưa cập nhật' }}</strong></span>
                                    <span><i class="fas fa-phone mr-1 text-success"></i><strong>{{ $patient->phone ?: ($patient->user->phone ?? 'Chưa cập nhật') }}</strong></span>
                                </div>
                                <div class="text-truncate text-gray-600">
                                    <i class="fas fa-map-marker-alt text-danger mr-1"></i>{{ $patient->address ?: 'Chưa cập nhật địa chỉ' }}
                                </div>
                            </div>
                        </div>

                        <!-- CÁC BUTTON CHỨC NĂNG Y NGUYÊN -->
                        <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                            <span class="small text-muted font-weight-bold">Chức năng:</span>
                            <div class="d-flex align-items-center" style="gap: 6px;">
                                <a href="{{ route('admin.patients.show', $patient->id) }}" class="btn btn-primary btn-circle btn-sm m-0" title="Xem bệnh án & chi tiết">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.patients.edit', $patient->id) }}" class="btn btn-warning btn-circle btn-sm m-0" title="Chỉnh sửa hồ sơ">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('admin.appointments.index', ['search' => $patient->name]) }}" class="btn btn-info btn-circle btn-sm m-0" title="Lịch hẹn của bệnh nhân">
                                    <i class="fas fa-calendar-check"></i>
                                </a>
                                <form action="{{ route('admin.patients.destroy', $patient->id) }}" method="POST" class="d-inline m-0" onsubmit="return confirm('Bạn có chắc chắn muốn xóa hồ sơ bệnh nhân này?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-circle btn-sm m-0" title="Xóa hồ sơ">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="text-center text-muted py-4 border rounded bg-light">Không tìm thấy hồ sơ bệnh nhân nào phù hợp.</div>
                </div>
            @endforelse
        </div>

        <div class="table-responsive d-none">
            <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                <thead class="bg-light">
                    <tr>
                        <th class="text-center" style="width: 55px;">STT</th>
                        <th>Họ tên Bệnh nhân & Mã BHYT</th>
                        <th style="width: 190px;">Giới tính & Liên hệ</th>
                        <th>Địa chỉ thường trú</th>
                        <th class="text-center" style="width: 165px;">Chức năng</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($patients as $patient)
                        @php
                            $genderLabel = match($patient->gender) {
                                'male', 'Nam' => 'Nam',
                                'female', 'Nữ' => 'Nữ',
                                default => 'Khác',
                            };
                            $genderBadge = $genderLabel === 'Nam' ? 'badge-primary' : ($genderLabel === 'Nữ' ? 'badge-info' : 'badge-secondary');
                        @endphp
                        <tr>
                            <!-- Cột 1: STT -->
                            <td class="text-center font-weight-bold text-gray-600 align-middle">
                                {{ method_exists($patients, 'firstItem') ? $patients->firstItem() + $loop->index : $loop->iteration }}
                            </td>

                            <!-- Cột 2: Tên, Ngày sinh & BHYT -->
                            <td class="align-middle">
                                <div class="font-weight-bold text-primary">
                                    <i class="fas fa-user mr-1 text-info"></i>{{ $patient->name }}
                                    <span class="badge badge-light border text-muted ml-1">#PAT-{{ str_pad($patient->id, 4, '0', STR_PAD_LEFT) }}</span>
                                </div>
                                <div class="small text-gray-600 mt-1">
                                    <i class="far fa-calendar mr-1"></i>NS: {{ $patient->birthdate ? \Carbon\Carbon::parse($patient->birthdate)->format('d/m/Y') : 'Chưa cập nhật' }}
                                    @if($patient->insurance_number)
                                        <span class="badge badge-success ml-2"><i class="fas fa-id-card mr-1"></i>BHYT: {{ $patient->insurance_number }}</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Cột 3: Giới tính & SĐT -->
                            <td class="align-middle">
                                <div>
                                    <span class="badge {{ $genderBadge }} px-2 py-1">{{ $genderLabel }}</span>
                                </div>
                                <div class="small font-weight-bold text-gray-800 mt-1">
                                    <i class="fas fa-phone mr-1 text-success"></i>{{ $patient->phone ?: ($patient->user->phone ?? 'Chưa cập nhật') }}
                                </div>
                            </td>

                            <!-- Cột 4: Địa chỉ -->
                            <td class="text-gray-700 align-middle">
                                <i class="fas fa-map-marker-alt text-danger mr-1"></i>{{ $patient->address ?: 'Chưa cập nhật địa chỉ' }}
                            </td>

                            <!-- Cột 5: Chức năng -->
                            <td class="text-center align-middle">
                                <a href="{{ route('admin.patients.show', $patient->id) }}" class="btn btn-primary btn-circle btn-sm mr-1" title="Xem bệnh án & chi tiết">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.patients.edit', $patient->id) }}" class="btn btn-warning btn-circle btn-sm mr-1" title="Chỉnh sửa hồ sơ">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('admin.appointments.index', ['search' => $patient->name]) }}" class="btn btn-info btn-circle btn-sm mr-1" title="Lịch hẹn của bệnh nhân">
                                    <i class="fas fa-calendar-check"></i>
                                </a>
                                <form action="{{ route('admin.patients.destroy', $patient->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa hồ sơ bệnh nhân này?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-circle btn-sm" title="Xóa hồ sơ">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Không tìm thấy hồ sơ bệnh nhân nào phù hợp.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($patients, 'links'))
            <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap">
                <div class="small text-muted mb-2 mb-sm-0">
                    Hiển thị {{ $patients->firstItem() ?? 0 }} - {{ $patients->lastItem() ?? 0 }} trong tổng số {{ $patients->total() }} bệnh nhân
                </div>
                <div>
                    {{ $patients->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('exportAdminPatientsCsvBtn')?.addEventListener('click', function () {
        var rows = [['STT', 'Họ tên Bệnh nhân & Mã BHYT', 'Giới tính & Liên hệ', 'Địa chỉ thường trú']];
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
        link.download = 'danh-sach-benh-nhan-' + new Date().toISOString().slice(0, 10) + '.csv';
        link.click();
    });
</script>
@endpush
