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

                {{-- Danh sách Slot chi tiết trong ngày --}}
                <h6 class="font-weight-bold text-gray-800 mb-2">
                    <i class="fas fa-th-large text-primary mr-1"></i> Danh sách khung giờ (Time Slots) ngày {{ \Carbon\Carbon::parse($today)->format('d/m/Y') }}:
                </h6>
                @if(isset($timeSlots) && $timeSlots->isNotEmpty())
                    <div class="d-flex flex-wrap mb-4">
                        @foreach($timeSlots as $slot)
                            <div class="border rounded px-3 py-2 mr-2 mb-2 {{ $slot->is_available ? 'bg-light border-primary' : 'bg-gray-200 text-muted' }}" style="min-width: 130px;">
                                <div class="font-weight-bold small {{ $slot->is_available ? 'text-primary' : 'text-secondary' }}">
                                    <i class="far fa-clock mr-1"></i>{{ substr($slot->start_time, 0, 5) }} - {{ substr($slot->end_time, 0, 5) }}
                                </div>
                                <div class="text-xs mt-1">
                                    Tối đa: <strong>{{ $slot->capacity }} BN</strong>
                                    @if(!$slot->is_available)
                                        <span class="badge badge-secondary ml-1">Đã khóa</span>
                                    @endif
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

                {{-- Danh sách lịch hẹn của bác sĩ trong ngày --}}
                <h6 class="font-weight-bold text-gray-800 mb-2">
                    <i class="fas fa-users text-primary mr-1"></i> Bệnh nhân đặt khám trong ngày ({{ isset($appointments) ? $appointments->count() : 0 }} ca):
                </h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Giờ / Ca khám</th>
                                <th>Bệnh nhân</th>
                                <th>Gói dịch vụ</th>
                                <th class="text-center">Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($appointments ?? [] as $appt)
                                <tr>
                                    <td class="font-weight-bold text-primary align-middle">
                                        {{ $appt->appointment_time ? substr($appt->appointment_time, 0, 5) : ($appt->medical_examination ?? 'Cả ngày') }}
                                    </td>
                                    <td class="align-middle">
                                        <div class="font-weight-bold">{{ $appt->patient->name ?? 'Bệnh nhân' }}</div>
                                        <small class="text-muted">{{ $appt->patient->phone ?? '' }}</small>
                                    </td>
                                    <td class="align-middle small">{{ $appt->service->name ?? 'Khám chuyên khoa' }}</td>
                                    <td class="text-center align-middle">
                                        @if($appt->status === 'completed')
                                            <span class="badge badge-success">Đã khám</span>
                                        @elseif($appt->status === 'checked_in')
                                            <span class="badge badge-info">Đã check-in</span>
                                        @elseif($appt->status === 'confirmed')
                                            <span class="badge badge-primary">Đã xác nhận</span>
                                        @else
                                            <span class="badge badge-warning">Chờ duyệt</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3 small">Chưa có lịch hẹn nào trong ngày {{ \Carbon\Carbon::parse($today)->format('d/m/Y') }}.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
