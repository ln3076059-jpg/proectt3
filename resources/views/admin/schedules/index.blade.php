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

<!-- Danh sách Ca khám & Sơ đồ Ghế khám -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-wrap align-items-center justify-content-between" style="gap: 8px;">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-chair mr-1"></i> Danh sách Ca trực &amp; Sơ đồ Ghế khám
        </h6>
        <div class="d-flex align-items-center" style="gap: 8px;">
            <span class="badge badge-success"><i class="fas fa-chair mr-1"></i>Ghế trống</span>
            <span class="badge badge-primary"><i class="fas fa-user-check mr-1"></i>Đang có khách</span>
            <span class="badge badge-warning text-dark"><i class="fas fa-users mr-1"></i>Đầy ghế</span>
            <span class="badge badge-danger"><i class="fas fa-lock mr-1"></i>Đã khóa</span>
        </div>
    </div>
    <div class="card-body">
        @forelse($schedules as $schedule)
            @php
                $totalSlots = $schedule->timeSlots->count();
                $totalCapacity = $schedule->timeSlots->sum('capacity');
                $totalBooked = $schedule->timeSlots->sum('booked_count');
                $shiftPct = $totalCapacity > 0 ? min(100, round(($totalBooked / $totalCapacity) * 100)) : 0;
            @endphp
            <div class="card mb-3 border-left-{{ $schedule->status === 'on_leave' ? 'warning' : 'primary' }} shadow-sm">
                <!-- Header Card Ca trực (Đầy đủ thông tin & các button y nguyên) -->
                <div class="card-header bg-white py-2 px-3 d-flex flex-wrap align-items-center justify-content-between" style="gap: 10px;">
                    <div class="d-flex align-items-center" style="gap: 10px;">
                        <span class="badge badge-dark font-weight-bold">#{{ $loop->iteration }}</span>
                        <div>
                            <div class="font-weight-bold text-primary" style="font-size: 0.95rem;">
                                <i class="fas fa-user-md mr-1"></i>{{ $schedule->doctor->formatted_name ?? ($schedule->doctor->user->name ?? 'N/A') }}
                                <span class="badge badge-info ml-1">{{ $schedule->doctor->department->name ?? 'Đa khoa' }}</span>
                                <span class="badge badge-light border text-dark ml-1"><i class="fas fa-door-open text-primary mr-1"></i>{{ $schedule->doctor->room_number ?? 'P.201' }}</span>
                            </div>
                            <div class="small text-gray-700 mt-1">
                                <i class="far fa-calendar-alt text-primary mr-1"></i><strong>{{ \Carbon\Carbon::parse($schedule->date)->format('d/m/Y') }}</strong>
                                <span class="mx-1">&middot;</span>
                                <i class="far fa-clock text-info mr-1"></i><strong>{{ substr($schedule->start_time, 0, 5) }} - {{ substr($schedule->end_time, 0, 5) }}</strong>
                                @if($schedule->status === 'on_leave')
                                    <span class="badge badge-warning text-dark ml-2"><i class="fas fa-user-clock mr-1"></i>Nghỉ phép</span>
                                @else
                                    <span class="badge badge-success ml-2">Đang trực</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center" style="gap: 14px;">
                        <div class="text-right">
                            <div class="small font-weight-bold text-gray-800">
                                {{ $totalSlots }} khung giờ &middot; Đã đặt <span class="text-danger">{{ $totalBooked }}</span>/{{ $totalCapacity }} ghế
                            </div>
                            <div class="progress progress-sm mt-1" style="width: 140px; margin-left: auto;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $shiftPct }}%"></div>
                            </div>
                        </div>

                        <!-- CÁC BUTTON CHỨC NĂNG Y NGUYÊN -->
                        <div class="d-flex align-items-center" style="gap: 6px;">
                            <button type="button" class="btn btn-info btn-circle btn-sm m-0" data-toggle="collapse" data-target="#slots-{{ $schedule->id }}" title="Ẩn / Hiện sơ đồ ghế khám">
                                <i class="fas fa-chair"></i>
                            </button>
                            <form action="{{ route('admin.schedules.leave', $schedule->id) }}" method="POST" class="d-inline m-0" onsubmit="return confirm('{{ $schedule->status === 'on_leave' ? 'Mở lại ca trực này và mở khóa các khung giờ trống?' : 'Báo nghỉ đột xuất cho ca trực này? Hệ thống sẽ khóa các khung giờ trống và tự động điều chuyển lịch hẹn sang bác sĩ cùng chuyên khoa (nếu có).' }}');">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn {{ $schedule->status === 'on_leave' ? 'btn-success' : 'btn-warning text-dark' }} btn-circle btn-sm m-0" title="{{ $schedule->status === 'on_leave' ? 'Kích hoạt lại ca trực' : 'Báo nghỉ đột xuất & Điều chuyển ca' }}">
                                    <i class="fas {{ $schedule->status === 'on_leave' ? 'fa-play' : 'fa-user-slash' }}"></i>
                                </button>
                            </form>
                            <form action="{{ route('admin.schedules.destroy', $schedule->id) }}" method="POST" class="d-inline m-0" onsubmit="return confirm('Bạn có chắc chắn muốn xóa ca làm việc này? Các khung giờ chưa có lịch hẹn sẽ bị xóa.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-circle btn-sm m-0" title="Xóa ca">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Body Card: Sơ đồ Ghế khám dạng Card Hình Ghế (Hiển thị trực tiếp) -->
                <div class="collapse show" id="slots-{{ $schedule->id }}">
                    <div class="card-body bg-light py-3 px-3">
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
                                    <div class="sb-mini-seats mb-2" title="Sơ đồ ghế trong khung giờ ({{ $slot->booked_count }}/{{ $slot->capacity }} ghế đã đặt)">
                                        @php
                                            $maxIcons = min(max(1, (int)$slot->capacity), 8);
                                        @endphp
                                        @for($si = 1; $si <= $maxIcons; $si++)
                                            @php
                                                $seatClass = $isLocked ? 'seat-locked' : ($si <= $slot->booked_count ? 'seat-taken' : 'seat-free');
                                            @endphp
                                            <span class="sb-mini-seat {{ $seatClass }}">
                                                <i class="fas {{ $isLocked ? 'fa-lock' : 'fa-chair' }}"></i>
                                            </span>
                                        @endfor
                                        @if($slot->capacity > 8)
                                            <span class="badge badge-light border text-muted" style="font-size: 0.65rem;">+{{ $slot->capacity - 8 }}</span>
                                        @endif
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
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center text-muted py-4">Chưa có ca làm việc nào phù hợp với điều kiện tìm kiếm.</div>
        @endforelse

        @if(method_exists($schedules, 'links'))
            <div class="mt-3">
                {{ $schedules->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
