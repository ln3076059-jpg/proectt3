@extends('layouts.admin')

@section('title', 'Lịch khám bác sĩ ' . ($doctor->user->name ?? $doctor->name))

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-1 text-gray-800 font-weight-bold">
            <i class="fas fa-calendar-check text-primary mr-2"></i>Điều phối Lịch trực: {{ $doctor->formatted_name ?? $doctor->user->name ?? $doctor->name }}
        </h1>
        <p class="text-muted small mb-0">Theo dõi dung lượng slot khám và danh sách bệnh nhân đặt lịch theo ngày</p>
    </div>
    <div class="mt-2 mt-sm-0">
        <a href="{{ route('admin.schedules.create', ['doctor_id' => $doctor->id]) }}" class="btn btn-sm btn-success shadow-sm mr-2">
            <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Mở thêm ca trực cho BS
        </a>
        <a href="{{ route('admin.schedules.index', ['doctor_id' => $doctor->id]) }}" class="btn btn-sm btn-primary shadow-sm mr-2">
            <i class="fas fa-calendar-alt fa-sm text-white-50 mr-1"></i> Quản lý toàn bộ ca & slot
        </a>
        <a href="{{ route('admin.doctors.index') }}" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50 mr-1"></i> Quay lại
        </a>
    </div>
</div>

<div class="row">
    <!-- Doctor Summary Card -->
    <div class="col-lg-4 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Thông tin Bác sĩ phụ trách</h6>
                <a href="{{ route('admin.doctors.edit', $doctor) }}" class="btn btn-sm btn-outline-info py-0 px-2">
                    <i class="fas fa-edit mr-1"></i>Sửa
                </a>
            </div>
            <div class="card-body text-center">
                @if($doctor->avatar)
                    <img src="{{ asset('storage/'.$doctor->avatar) }}" class="rounded-circle border mb-3 shadow-sm" style="width: 100px; height: 100px; object-fit: cover;">
                @else
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm" style="width: 100px; height: 100px;">
                        <i class="fas fa-user-md fa-3x"></i>
                    </div>
                @endif
                <h5 class="font-weight-bold text-gray-800 mb-1">{{ $doctor->formatted_name ?? $doctor->user->name ?? $doctor->name }}</h5>
                <p class="text-primary font-weight-bold mb-2">{{ $doctor->specialization ?? 'Bác sĩ chuyên khoa' }}</p>
                <div class="badge badge-info px-3 py-2 mb-3">{{ $doctor->department->name ?? 'Đa khoa' }}</div>

                <hr>

                <div class="text-left small text-gray-700">
                    <p class="mb-2"><i class="fas fa-id-card text-primary mr-2"></i><strong>CCHN:</strong> {{ $doctor->license_number ?: 'Đang cập nhật' }}</p>
                    <p class="mb-2"><i class="fas fa-envelope text-primary mr-2"></i><strong>Email:</strong> {{ $doctor->user->email ?? $doctor->email }}</p>
                    <p class="mb-0"><i class="fas fa-phone text-primary mr-2"></i><strong>Điện thoại:</strong> {{ $doctor->user->phone ?? $doctor->phone ?? 'Chưa cập nhật' }}</p>
                </div>
            </div>
        </div>

        <!-- Date Selector Card -->
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-search mr-1"></i> Tra cứu theo ngày khám
                </h6>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.doctors.schedule', $doctor->id) }}" method="GET">
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold text-gray-700">CHỌN NGÀY LÀM VIỆC</label>
                        <input type="date" name="date" class="form-control" value="{{ $today }}" onchange="this.form.submit()">
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.doctors.schedule', ['doctor' => $doctor->id, 'date' => \Carbon\Carbon::parse($today)->subDay()->toDateString()]) }}" class="btn btn-sm btn-light border">
                            <i class="fas fa-chevron-left mr-1"></i> Hôm trước
                        </a>
                        <a href="{{ route('admin.doctors.schedule', ['doctor' => $doctor->id, 'date' => \Carbon\Carbon::today()->toDateString()]) }}" class="btn btn-sm btn-outline-primary">
                            Hôm nay
                        </a>
                        <a href="{{ route('admin.doctors.schedule', ['doctor' => $doctor->id, 'date' => \Carbon\Carbon::parse($today)->addDay()->toDateString()]) }}" class="btn btn-sm btn-light border">
                            Hôm sau <i class="fas fa-chevron-right ml-1"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Shifts, Slots & Appointments Card -->
    <div class="col-lg-8 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="far fa-clock mr-1"></i> Tình trạng tiếp nhận ca khám ngày {{ \Carbon\Carbon::parse($today)->format('d/m/Y') }}
                </h6>
                <span class="badge badge-primary px-3 py-1">
                    {{ isset($schedules) ? $schedules->count() : 0 }} ca trực đã mở
                </span>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Ca sáng -->
                    <div class="col-md-6 mb-4">
                        <div class="card border-left-warning shadow-sm h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Ca sáng (07:30 - 11:30)</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $morningCount }} / {{ $morningCapacity ?? 20 }} ca đã đặt</div>
                                        <div class="progress progress-sm mr-2 mt-2">
                                            <div class="progress-bar bg-warning" role="progressbar" style="width: {{ min(100, ($morningCount / max(1, $morningCapacity ?: 20)) * 100) }}%"></div>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-sun fa-2x text-warning"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ca chiều -->
                    <div class="col-md-6 mb-4">
                        <div class="card border-left-info shadow-sm h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Ca chiều (13:00 - 17:00)</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $afternoonCount }} / {{ $afternoonCapacity ?? 20 }} ca đã đặt</div>
                                        <div class="progress progress-sm mr-2 mt-2">
                                            <div class="progress-bar bg-info" role="progressbar" style="width: {{ min(100, ($afternoonCount / max(1, $afternoonCapacity ?: 20)) * 100) }}%"></div>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-cloud-sun fa-2x text-info"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sơ đồ Ghế khám / Slot chi tiết trong ngày dạng Card hình ghế --}}
                <div class="d-flex flex-wrap align-items-center justify-content-between mb-2" style="gap: 8px;">
                    <h6 class="font-weight-bold text-gray-800 mb-0">
                        <i class="fas fa-chair text-primary mr-1"></i> Sơ đồ Ghế khám &amp; Khung giờ (Time Slots) ngày {{ \Carbon\Carbon::parse($today)->format('d/m/Y') }}:
                    </h6>
                    <div class="small">
                        <span class="badge badge-success mr-1"><i class="fas fa-chair mr-1"></i>Còn trống</span>
                        <span class="badge badge-primary mr-1"><i class="fas fa-user-check mr-1"></i>Đã có BN đặt</span>
                        <span class="badge badge-danger"><i class="fas fa-lock mr-1"></i>Đã khóa</span>
                    </div>
                </div>
                @if(isset($timeSlots) && $timeSlots->isNotEmpty())
                    <div class="sb-slot-grid mb-4">
                        @foreach($timeSlots as $slot)
                            @php
                                $bCount = $slot->booked_count ?? 0;
                                $cap = max(1, $slot->capacity ?? 5);
                                $isLocked = ($slot->status ?? '') === \App\Models\TimeSlot::STATUS_LOCKED;
                                $isFull = $bCount >= $cap;
                                $slotCls = $isLocked ? 'is-locked' : ($isFull ? 'is-full' : ($bCount > 0 ? 'is-busy' : 'is-available'));
                            @endphp
                            <div class="sb-slot-card {{ $slotCls }}">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="sb-slot-seat-icon {{ $isLocked ? 'bg-danger text-white' : ($isFull ? 'bg-warning text-dark' : ($bCount > 0 ? 'bg-primary text-white' : 'bg-success text-white')) }}">
                                        <i class="fas {{ $isLocked ? 'fa-lock' : 'fa-chair' }}"></i>
                                    </span>
                                    <div class="text-right">
                                        <div class="font-weight-bold text-gray-800" style="font-size: 0.88rem;">
                                            {{ substr($slot->start_time, 0, 5) }} - {{ substr($slot->end_time, 0, 5) }}
                                        </div>
                                        @if($isLocked)
                                            <span class="badge badge-danger">Đã khóa</span>
                                        @elseif($isFull)
                                            <span class="badge badge-warning text-dark">Đầy ghế</span>
                                        @else
                                            <span class="badge badge-success">Còn {{ max(0, $cap - $bCount) }} ghế</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="sb-mini-seats mb-2" title="Sơ đồ ghế trong khung giờ ({{ $bCount }}/{{ $cap }} ghế đã đặt)">
                                    @php
                                        $maxIcons = min(max(1, (int)$cap), 8);
                                    @endphp
                                    @for($si = 1; $si <= $maxIcons; $si++)
                                        @php
                                            $seatClass = $isLocked ? 'seat-locked' : ($si <= $bCount ? 'seat-taken' : 'seat-free');
                                        @endphp
                                        <span class="sb-mini-seat {{ $seatClass }}">
                                            <i class="fas {{ $isLocked ? 'fa-lock' : 'fa-chair' }}"></i>
                                        </span>
                                    @endfor
                                    @if($cap > 8)
                                        <span class="badge badge-light border text-muted" style="font-size: 0.65rem;">+{{ $cap - 8 }}</span>
                                    @endif
                                </div>
                                <div class="progress progress-sm mb-1">
                                    <div class="progress-bar {{ $isLocked ? 'bg-danger' : 'bg-success' }}" role="progressbar" style="width: {{ min(100, round(($bCount / $cap) * 100)) }}%"></div>
                                </div>
                                <div class="d-flex align-items-center justify-content-between small text-muted mt-1 pt-1 border-top">
                                    <span>Ghế: <strong>{{ $bCount }}/{{ $cap }}</strong> &middot; {{ $doctor->room_number ?? 'P.201' }}</span>
                                    <form action="{{ route('admin.schedules.slots.toggle', $slot->id) }}" method="POST" class="d-inline m-0">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-xs {{ $isLocked ? 'btn-success' : 'btn-outline-warning text-dark' }} py-0 px-2 m-0" title="{{ $isLocked ? 'Mở khóa ghế' : 'Khóa ghế' }}">
                                            <i class="fas {{ $isLocked ? 'fa-unlock' : 'fa-lock' }}"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-warning small mb-4">
                        <i class="fas fa-exclamation-circle mr-1"></i>
                        Bác sĩ chưa có khung giờ (TimeSlot) chi tiết nào được cấu hình vào ngày {{ \Carbon\Carbon::parse($today)->format('d/m/Y') }}.
                        <a href="{{ route('admin.schedules.create', ['doctor_id' => $doctor->id]) }}" class="font-weight-bold ml-1">Tạo ca trực ngay &rarr;</a>
                    </div>
                @endif

                {{-- Danh sách lịch hẹn của bác sĩ trong ngày làm thành Card Phiếu Khám (Giữ nguyên các button) --}}
                <h6 class="font-weight-bold text-gray-800 mb-3">
                    <i class="fas fa-ticket-alt text-primary mr-1"></i> Danh sách Bệnh nhân đặt khám trong ngày ({{ isset($appointments) ? $appointments->count() : 0 }} ca):
                </h6>
                <div class="row">
                    @forelse($appointments ?? [] as $appt)
                        <div class="col-md-6 mb-3">
                            <div class="sb-ticket-card {{ $appt->status === 'pending' ? 'ticket-pending' : ($appt->status === 'checked_in' ? 'ticket-checked-in' : 'ticket-confirmed') }} h-100 p-3">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="badge badge-dark font-weight-bold">#{{ $loop->iteration }} &middot; Mã #{{ str_pad($appt->id, 5, '0', STR_PAD_LEFT) }}</span>
                                    <span class="small font-weight-bold text-primary">
                                        <i class="far fa-clock mr-1"></i>{{ $appt->appointment_time ? substr($appt->appointment_time, 0, 5) : ($appt->medical_examination ?? 'Cả ngày') }}
                                    </span>
                                </div>
                                <div class="font-weight-bold text-gray-800" style="font-size: 0.92rem;">
                                    {{ $appt->patient->name ?? 'Bệnh nhân' }}
                                    <small class="text-muted ml-1"><i class="fas fa-phone-alt text-success mr-1"></i>{{ $appt->patient->phone ?? '---' }}</small>
                                </div>
                                <div class="small text-info font-weight-bold mb-2">
                                    {{ $appt->service->name ?? 'Khám chuyên khoa' }}
                                </div>
                                <div class="d-flex align-items-center justify-content-between pt-2" style="border-top: 1px dashed #cbd5e1;">
                                    <div>
                                        @if($appt->status === 'completed')
                                            <span class="badge badge-success">Đã khám</span>
                                        @elseif($appt->status === 'checked_in')
                                            <span class="badge badge-info">Đã check-in QR</span>
                                        @elseif($appt->status === 'confirmed')
                                            <span class="badge badge-primary">Đã xác nhận</span>
                                        @else
                                            <span class="badge badge-warning text-dark">Chờ duyệt</span>
                                        @endif
                                    </div>
                                    <div class="d-flex align-items-center" style="gap: 6px;">
                                        <a href="{{ route('admin.appointments.show', $appt->id) }}" class="btn btn-info btn-circle btn-sm m-0" title="Xem chi tiết ca khám">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($appt->medicalRecord)
                                            <a href="{{ route('admin.medical-records.show', $appt->medicalRecord->id) }}" class="btn btn-primary btn-circle btn-sm m-0" title="Xem bệnh án EMR">
                                                <i class="fas fa-file-medical-alt"></i>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="text-center text-muted py-3 small border rounded bg-light">
                                Chưa có lịch hẹn nào trong ngày {{ \Carbon\Carbon::parse($today)->format('d/m/Y') }}.
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
