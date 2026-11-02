@extends('layouts.admin')

@section('title', 'Lịch làm việc & Khung giờ khám')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex flex-wrap align-items-center justify-content-between mb-4" style="gap: 12px 16px;">
    <h1 class="h3 mb-0 text-gray-800 font-weight-bold">
        <i class="fas fa-calendar-alt text-primary mr-2"></i>Lịch làm việc & Khung giờ khám
    </h1>
    <a href="{{ route('admin.schedules.create') }}" class="btn btn-sm btn-primary shadow-sm">
        <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Thiết lập ca làm việc mới
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

@php
    $sumShifts = $schedules->count();
    $sumSlots = $schedules->sum(fn($s) => $s->timeSlots->count());
    $sumCap = $schedules->sum(fn($s) => $s->timeSlots->sum('capacity'));
    $sumBooked = $schedules->sum(fn($s) => $s->timeSlots->sum('booked_count'));
@endphp

<!-- 4 Thẻ KPI Nhịp điệu Ca trực & Công suất Ghế khám -->
<div class="row mb-2">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body py-2">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Ca trực hiển thị</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $sumShifts }} ca làm việc</div>
                    </div>
                    <div class="col-auto"><i class="fas fa-calendar-check fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body py-2">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Tổng khung giờ (Slot)</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $sumSlots }} khung giờ</div>
                    </div>
                    <div class="col-auto"><i class="fas fa-clock fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body py-2">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Tổng ghế / suất tiếp nhận</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $sumCap }} chỗ khám</div>
                    </div>
                    <div class="col-auto"><i class="fas fa-chair fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body py-2">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Đã đặt / Còn trống</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $sumBooked }} đặt · {{ max(0, $sumCap - $sumBooked) }} trống</div>
                    </div>
                    <div class="col-auto"><i class="fas fa-user-check fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Card -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-wrap align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-filter mr-1"></i> Bộ lọc tìm kiếm ca làm việc
        </h6>
        <div class="mt-2 mt-sm-0 d-flex align-items-center" style="gap: 6px;">
            <a href="{{ route('admin.schedules.index', ['from_date' => now()->toDateString(), 'to_date' => now()->toDateString(), 'doctor_id' => request('doctor_id')]) }}" class="btn btn-xs btn-outline-info py-1 px-2">Hôm nay</a>
            <a href="{{ route('admin.schedules.index', ['from_date' => now()->toDateString(), 'to_date' => now()->addDays(6)->toDateString(), 'doctor_id' => request('doctor_id')]) }}" class="btn btn-xs btn-outline-primary py-1 px-2">7 ngày tới</a>
            <a href="{{ route('admin.schedules.index', ['from_date' => now()->startOfMonth()->toDateString(), 'to_date' => now()->endOfMonth()->toDateString(), 'doctor_id' => request('doctor_id')]) }}" class="btn btn-xs btn-outline-secondary py-1 px-2">Tháng này</a>
        </div>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.schedules.index') }}" method="GET" class="form-row align-items-end">
            <div class="col-md-4 mb-2">
                <label class="font-weight-bold text-gray-700 small">BÁC SĨ PHỤ TRÁCH</label>
                <select name="doctor_id" class="form-control">
                    <option value="">-- Tất cả bác sĩ --</option>
                    @foreach($doctors as $doc)
                        <option value="{{ $doc->id }}" {{ (string)$selectedDoctorId === (string)$doc->id ? 'selected' : '' }}>
                            {{ $doc->formatted_name ?? ($doc->user->name ?? 'N/A') }} ({{ $doc->department->name ?? 'Đa khoa' }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 mb-2">
                <label class="font-weight-bold text-gray-700 small">TỪ NGÀY</label>
                <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
            </div>
            <div class="col-md-3 mb-2">
                <label class="font-weight-bold text-gray-700 small">ĐẾN NGÀY</label>
                <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
            </div>
            <div class="col-md-2 mb-2">
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-search mr-1"></i> Lọc ca
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Schedules Data Table (5 CỘT BAO GỒM 1 CỘT CHỨC NĂNG) -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-list mr-1"></i> Danh sách ca khám bệnh đã thiết lập
        </h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 50px;">STT</th>
                        <th>Bác sĩ & Chuyên khoa</th>
                        <th>Ngày làm việc & Ca khám</th>
                        <th style="width: 200px;">Khung giờ & Sức chứa</th>
                        <th class="text-center" style="width: 150px;">Chức năng</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($schedules as $schedule)
                        @php
                            $totalSlots = $schedule->timeSlots->count();
                            $totalCapacity = $schedule->timeSlots->sum('capacity');
                            $totalBooked = $schedule->timeSlots->sum('booked_count');
                        @endphp
                        <tr>
                            <!-- Cột 1: STT -->
                            <td class="text-center font-weight-bold text-gray-600">
                                {{ $loop->iteration }}
                            </td>

                            <!-- Cột 2: Bác sĩ -->
                            <td>
                                <div class="font-weight-bold text-primary">
                                    {{ $schedule->doctor->formatted_name ?? ($schedule->doctor->user->name ?? 'N/A') }}
                                </div>
                                <span class="badge badge-info">{{ $schedule->doctor->department->name ?? 'Đa khoa' }}</span>
                            </td>

                            <!-- Cột 3: Ngày & Ca -->
                            <td>
                                <div class="font-weight-bold text-gray-800">
                                    <i class="far fa-calendar-alt text-primary mr-1"></i>{{ \Carbon\Carbon::parse($schedule->date)->format('d/m/Y') }}
                                    @if($schedule->status === 'on_leave')
                                        <span class="badge badge-warning text-dark ml-1"><i class="fas fa-user-clock mr-1"></i>Nghỉ phép</span>
                                    @else
                                        <span class="badge badge-success ml-1">Đang trực</span>
                                    @endif
                                </div>
                                <div class="small text-muted">
                                    <i class="far fa-clock mr-1"></i>{{ substr($schedule->start_time, 0, 5) }} - {{ substr($schedule->end_time, 0, 5) }}
                                </div>
                            </td>

                            <!-- Cột 4: Slots & Tải -->
                            <td>
                                <div><span class="font-weight-bold">{{ $totalSlots }}</span> khung giờ khám</div>
                                <div class="small text-gray-700">Đã đặt: <strong class="text-danger">{{ $totalBooked }}</strong> / {{ $totalCapacity }} chỗ</div>
                                <div class="progress progress-sm mt-1">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $totalCapacity > 0 ? min(100, ($totalBooked / $totalCapacity) * 100) : 0 }}%"></div>
                                </div>
                            </td>

                            <!-- Cột 5: Chức năng -->
                            <td class="text-center">
                                <button type="button" class="btn btn-info btn-circle btn-sm mr-1" data-toggle="collapse" data-target="#slots-{{ $schedule->id }}" title="Xem chi tiết các slot">
                                    <i class="fas fa-th-large"></i>
                                </button>
                                <form action="{{ route('admin.schedules.leave', $schedule->id) }}" method="POST" class="d-inline mr-1" onsubmit="return confirm('{{ $schedule->status === 'on_leave' ? 'Mở lại ca trực này và mở khóa các khung giờ trống?' : 'Báo nghỉ đột xuất cho ca trực này? Hệ thống sẽ khóa các khung giờ trống và tự động điều chuyển lịch hẹn sang bác sĩ cùng chuyên khoa (nếu có).' }}');">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn {{ $schedule->status === 'on_leave' ? 'btn-success' : 'btn-warning text-dark' }} btn-circle btn-sm" title="{{ $schedule->status === 'on_leave' ? 'Kích hoạt lại ca trực' : 'Báo nghỉ đột xuất & Điều chuyển ca' }}">
                                        <i class="fas {{ $schedule->status === 'on_leave' ? 'fa-play' : 'fa-user-slash' }}"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.schedules.destroy', $schedule->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa ca làm việc này? Các khung giờ chưa có lịch hẹn sẽ bị xóa.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-circle btn-sm" title="Xóa ca">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <!-- Collapsible Detailed Slots Grid Row (Card Hình Ghế Khám / Slot Trực Quan) -->
                        <tr class="collapse {{ $loop->first ? 'show' : '' }}" id="slots-{{ $schedule->id }}">
                            <td colspan="5" class="bg-light p-3">
                                <div class="d-flex flex-wrap align-items-center justify-content-between mb-3" style="gap: 8px;">
                                    <h6 class="font-weight-bold text-primary mb-0">
                                        <i class="fas fa-chair mr-1"></i> Sơ đồ Ghế khám &amp; Khung giờ ca trực: {{ \Carbon\Carbon::parse($schedule->date)->format('d/m/Y') }} — {{ $schedule->doctor->formatted_name ?? $schedule->doctor->user->name ?? 'Bác sĩ' }}
                                    </h6>
                                    <div class="small">
                                        <span class="badge badge-success mr-1"><i class="fas fa-chair mr-1"></i>Còn trống</span>
                                        <span class="badge badge-primary mr-1"><i class="fas fa-user-check mr-1"></i>Đang có khách</span>
                                        <span class="badge badge-warning text-dark mr-1"><i class="fas fa-users mr-1"></i>Đầy chỗ</span>
                                        <span class="badge badge-danger"><i class="fas fa-lock mr-1"></i>Đã khóa</span>
                                    </div>
                                </div>
                                <div class="sb-slot-grid">
                                    @forelse($schedule->timeSlots as $slot)
                                        @php
                                            $isPast = $slot->start_date_time->isPast();
                                            $isFull = $slot->booked_count >= $slot->capacity;
                                            $isLocked = $slot->status === \App\Models\TimeSlot::STATUS_LOCKED;
                                            $slotStateClass = $isLocked ? 'is-locked' : ($isFull ? 'is-full' : ($slot->booked_count > 0 ? 'is-busy' : 'is-available'));
                                            $pct = $slot->capacity > 0 ? min(100, round(($slot->booked_count / $slot->capacity) * 100)) : 0;
                                        @endphp
                                        <div class="sb-slot-card {{ $slotStateClass }}">
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <span class="sb-slot-seat-icon {{ $isLocked ? 'bg-danger text-white' : ($isFull ? 'bg-warning text-dark' : ($slot->booked_count > 0 ? 'bg-primary text-white' : 'bg-success text-white')) }}">
                                                    <i class="fas {{ $isLocked ? 'fa-lock' : 'fa-chair' }}"></i>
                                                </span>
                                                <div class="text-right">
                                                    <div class="font-weight-bold text-gray-800" style="font-size: 0.88rem;">
                                                        {{ substr($slot->start_time, 0, 5) }} - {{ substr($slot->end_time, 0, 5) }}
                                                    </div>
                                                    @if($isLocked)
                                                        <span class="badge badge-danger">Đang khóa</span>
                                                    @elseif($isFull)
                                                        <span class="badge badge-warning text-dark">Đầy ghế</span>
                                                    @elseif($slot->booked_count > 0)
                                                        <span class="badge badge-primary">Đã đặt {{ $slot->booked_count }}/{{ $slot->capacity }}</span>
                                                    @else
                                                        <span class="badge badge-success">Trống {{ $slot->capacity }} ghế</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="progress progress-sm mb-2">
                                                <div class="progress-bar {{ $isLocked ? 'bg-danger' : ($isFull ? 'bg-warning' : 'bg-success') }}" role="progressbar" style="width: {{ $pct }}%"></div>
                                            </div>
                                            <div class="d-flex align-items-center justify-content-between pt-1 border-top">
                                                <small class="text-muted font-weight-bold">Ghế: {{ $slot->booked_count }}/{{ $slot->capacity }}</small>
                                                <div class="d-flex align-items-center" style="gap: 4px;">
                                                    <form action="{{ route('admin.schedules.slots.toggle', $slot->id) }}" method="POST" class="d-inline m-0">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="btn btn-xs {{ $isLocked ? 'btn-success' : 'btn-outline-warning text-dark' }} py-0 px-2 m-0" title="{{ $isLocked ? 'Mở khóa ghế/khung giờ' : 'Khóa ghế/khung giờ' }}">
                                                            <i class="fas {{ $isLocked ? 'fa-unlock' : 'fa-lock' }}"></i>
                                                        </button>
                                                    </form>
                                                    <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2 m-0" data-toggle="modal" data-target="#editCapModal-{{ $slot->id }}" title="Chỉnh số ghế tối đa">
                                                        <i class="fas fa-sliders-h"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Modal Capacity -->
                                        <div class="modal fade" id="editCapModal-{{ $slot->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                            <div class="modal-dialog modal-sm" role="document">
                                                <div class="modal-content">
                                                    <form action="{{ route('admin.schedules.slots.capacity', $slot->id) }}" method="POST">
                                                        @csrf
                                                        @method('PATCH')
                                                        <div class="modal-header">
                                                            <h6 class="modal-title font-weight-bold text-primary">Sức chứa ghế khám</h6>
                                                            <button class="close" type="button" data-dismiss="modal"><span>&times;</span></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <label class="font-weight-bold text-gray-700 small">Giờ: {{ substr($slot->start_time, 0, 5) }} - {{ substr($slot->end_time, 0, 5) }}</label>
                                                            <input type="number" name="capacity" class="form-control" value="{{ $slot->capacity }}" min="1" max="100" required>
                                                            <small class="text-muted">Số ghế / bệnh nhân tối đa trong khung giờ này</small>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Đóng</button>
                                                            <button type="submit" class="btn btn-primary btn-sm">Lưu</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-muted text-center py-2">Chưa có khung giờ nào được sinh ra cho ca này.</div>
                                    @endforelse
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Chưa có ca làm việc nào phù hợp với điều kiện tìm kiếm.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($schedules, 'links'))
            <div class="mt-3">
                {{ $schedules->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
