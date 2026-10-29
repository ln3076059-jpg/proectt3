@extends('layouts.profile')

@section('title', 'Phiếu Tiếp Nhận & Lịch Khám #' . ($appointment->appointment_code ?? $appointment->id))
@section('breadcrumb_current', 'Chi tiết ca khám #' . ($appointment->appointment_code ?? $appointment->id))

@push('styles')
<style>
/* ========================================================
   TIKI CLINICAL DESIGN SYSTEM · PHÒNG KHÁM 125 THÁI THỊNH
   ======================================================== */
.clinical-card {
    background-color: var(--surface);
    border: 0;
    border-radius: var(--radius);
}

.clinical-header-title {
    font-family: var(--font-display);
    font-size: var(--step-lead);
    font-weight: 700;
    color: var(--ink);
    letter-spacing: -0.015em;
}

.clinical-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: var(--step-small);
    font-weight: 600;
    padding: 5px 12px;
    border-radius: 6px;
}
.clinical-badge.pending {
    background-color: var(--status-warn-bg);
    color: var(--status-warn-ink);
}
.clinical-badge.confirmed,
.clinical-badge.received {
    background-color: var(--surface-soft);
    color: var(--accent);
}
.clinical-badge.completed {
    background-color: var(--status-ok-bg);
    color: var(--status-ok-ink);
}
.clinical-badge.cancelled {
    background-color: var(--status-danger-bg);
    color: var(--status-danger-ink);
}

/* 5-STAGE CLINICAL JOURNEY STEPPER */
.clinical-journey {
    position: relative;
    padding: 18px 8px 8px 8px;
}
.clinical-journey-line-bg {
    position: absolute;
    top: 36px;
    left: 8%;
    right: 8%;
    height: 4px;
    background-color: var(--bg);
    border-radius: 4px;
    z-index: 1;
}
.clinical-journey-line-fill {
    position: absolute;
    top: 36px;
    left: 8%;
    height: 4px;
    background-color: var(--accent);
    border-radius: 4px;
    z-index: 1;
    transition: width 0.5s ease;
}
.clinical-journey-steps {
    display: flex;
    justify-content: space-between;
    position: relative;
    z-index: 2;
}
.clinical-journey-step {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}
.clinical-journey-circle {
    width: 38px;
    height: 38px;
    border-radius: var(--radius);
    background-color: var(--bg);
    color: var(--muted);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: var(--step-small);
    margin-bottom: 8px;
    transition: background-color 0.25s ease, color 0.25s ease;
}
.clinical-journey-step.active .clinical-journey-circle {
    background-color: var(--accent);
    color: var(--accent-ink);
}
.clinical-journey-step.completed .clinical-journey-circle {
    background-color: var(--status-ok-bg);
    color: var(--status-ok-ink);
}
.clinical-journey-label {
    font-size: var(--step-small);
    font-weight: 600;
    color: var(--muted);
    margin-bottom: 2px;
}
.clinical-journey-step.active .clinical-journey-label {
    color: var(--accent);
    font-weight: 700;
}
.clinical-journey-step.completed .clinical-journey-label {
    color: var(--ink);
}
.clinical-journey-sub {
    font-size: var(--step-small);
    color: var(--muted);
}

@media (max-width: 576px) {
    .clinical-journey-sub {
        display: none;
    }
}

/* INFO CARDS */
.clinical-info-card {
    background-color: var(--surface);
    border: 0;
    border-radius: var(--radius);
    padding: 20px;
    height: 100%;
    display: flex;
    flex-direction: column;
}
.clinical-info-header {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: var(--step-small);
    font-weight: 700;
    color: var(--muted);
    padding-bottom: 10px;
    margin-bottom: 12px;
}

/* PREPARATION CHECKLIST */
.prep-checklist {
    background-color: var(--status-ok-bg);
    border-radius: var(--radius);
    padding: 16px 18px;
}
.prep-checklist-item {
    font-size: var(--step-small);
    color: var(--status-ok-ink);
    display: flex;
    align-items: flex-start;
    gap: 10px;
    margin-bottom: 8px;
}
.prep-checklist-item:last-child {
    margin-bottom: 0;
}

/* PRICE & INVOICE */
.clinical-price-display {
    font-family: var(--font-display);
    font-size: var(--step-lead);
    font-weight: 700;
    color: var(--accent);
    font-variant-numeric: tabular-nums;
}

/* PRINT MEDIA STYLESHEET (PROFESSIONAL MEDICAL VOUCHER) */
@media print {
    body * {
        visibility: hidden;
    }
    .appointment-detail-page, .appointment-detail-page * {
        visibility: visible;
    }
    .appointment-detail-page {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        background: #ffffff !important;
        padding: 0 !important;
    }
    .no-print, .ts-header, .tiki-breadcrumb, .tiki-profile-sidebar, footer, .modal {
        display: none !important;
    }
    .print-only-header {
        display: block !important;
    }
}
.print-only-header {
    display: none;
}
</style>
@endpush

@section('content')
<div class="appointment-detail-page">
    {{-- PRINT HEADER (Chỉ hiện khi bấm In phiếu khám) --}}
    <div class="print-only-header border-bottom pb-3 mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-bold mb-0 text-uppercase" style="letter-spacing: 0.5px;">Phòng Khám Đa Khoa Thái Thịnh</h4>
                <small class="d-block text-muted">Địa chỉ: 125 Thái Thịnh, Đống Đa, Hà Nội • Hotline cấp cứu &amp; đặt khám: 1900 1234</small>
                <small class="d-block text-muted">Website: phongkhamthaithinh.vn • Giấy phép KCB số: 125/BYT-GPHĐ</small>
            </div>
            <div class="text-end">
                <h5 class="fw-bold text-primary mb-0 font-monospace">PHIẾU TIẾP NHẬN KHÁM BỆNH</h5>
                <small class="text-muted">Mã hồ sơ: <strong>{{ $appointment->appointment_code ?? ('APT-' . $appointment->id) }}</strong></small>
            </div>
        </div>
    </div>

    {{-- TOP ACTION & TITLE BAR --}}
    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2 no-print">
        <div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h5 class="clinical-header-title mb-0">
                    Phiếu Tiếp Nhận &amp; Lịch Khám: <span class="text-primary font-monospace">#{{ $appointment->appointment_code ?? str_pad($appointment->id, 6, '0', STR_PAD_LEFT) }}</span>
                </h5>
                @php
                    $statusClass = match($appointment->status) {
                        'pending' => 'pending',
                        'confirmed' => 'confirmed',
                        'checked_in' => 'received',
                        'completed' => 'completed',
                        'cancelled' => 'cancelled',
                        default => 'pending'
                    };
                @endphp
                <span class="clinical-badge {{ $statusClass }}">
                    @if($appointment->status === 'confirmed')
                        <i class="fas fa-check-circle"></i>
                    @elseif($appointment->status === 'checked_in')
                        <i class="fas fa-qrcode"></i>
                    @elseif($appointment->status === 'completed')
                        <i class="fas fa-flag-checkered"></i>
                    @elseif($appointment->status === 'cancelled')
                        <i class="fas fa-times-circle"></i>
                    @else
                        <i class="fas fa-hourglass-half"></i>
                    @endif
                    {{ $appointment->status_label ?? $appointment->status }}
                </span>
            </div>
            <div class="text-muted small mt-1" style="font-size: 12.5px;">
                <i class="far fa-calendar-plus me-1 text-secondary"></i>Ngày đăng ký tiếp nhận: <strong>{{ $appointment->created_at ? $appointment->created_at->format('H:i - d/m/Y') : '---' }}</strong>
            </div>
        </div>

        @php
            $calCode = $appointment->appointment_code ?: ('TT-' . str_pad($appointment->id, 5, '0', STR_PAD_LEFT));
            $calDateStr = \Carbon\Carbon::parse($appointment->appointment_date)->format('Ymd');
            $calStartHour = '080000';
            $calEndHour = '090000';
            if ($appointment->timeSlot && $appointment->timeSlot->start_time) {
                $calStartHour = str_replace(':', '', substr($appointment->timeSlot->start_time, 0, 5)) . '00';
                $calEndHour = $appointment->timeSlot->end_time ? (str_replace(':', '', substr($appointment->timeSlot->end_time, 0, 5)) . '00') : '090000';
            }
            $calTitle = 'Khám bệnh tại PK 125 Thái Thịnh (#' . $calCode . ')';
            $calDetails = 'Bác sĩ: ' . ($appointment->doctor->user->name ?? 'Chuyên khoa') . ' - Khoa: ' . ($appointment->doctor->department->name ?? 'Đa khoa') . ' - Mã phiếu: ' . $calCode;
            $calLocation = 'Phòng khám Đa khoa 125 Thái Thịnh, Đống Đa, Hà Nội';
            $googleCalUrl = 'https://calendar.google.com/calendar/render?action=TEMPLATE'
                . '&text=' . urlencode($calTitle)
                . '&dates=' . $calDateStr . 'T' . $calStartHour . '/' . $calDateStr . 'T' . $calEndHour
                . '&details=' . urlencode($calDetails)
                . '&location=' . urlencode($calLocation);
        @endphp

        <div class="d-flex gap-2 flex-wrap">
            @if(!in_array($appointment->status, ['cancelled', 'completed']))
                <a href="{{ $googleCalUrl }}" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm py-1.5 px-3 rounded-pill shadow-sm" title="Thêm lịch nhắc vào Google Calendar">
                    <i class="far fa-calendar-check me-1"></i> Nhắc lịch Google
                </a>
            @endif
            <button type="button" class="btn btn-primary btn-sm py-1.5 px-3 rounded-pill shadow-sm" onclick="window.downloadMedicalPassPng && window.downloadMedicalPassPng()">
                <i class="fas fa-download me-1"></i> Tải phiếu khám (PNG)
            </button>
            <button type="button" class="btn btn-outline-dark btn-sm py-1.5 px-3 rounded-pill shadow-sm" onclick="window.print()">
                <i class="fas fa-print me-1 text-primary"></i> In phiếu khám A4/A5
            </button>
            <a href="{{ route('appointments.index') }}" class="btn btn-outline-secondary btn-sm py-1.5 px-3 rounded-pill shadow-sm">
                <i class="fas fa-arrow-left me-1"></i> Danh sách ca khám
            </a>
        </div>
    </div>

    @if(in_array($appointment->status, ['confirmed', 'checked_in']))
        <div class="p-3 rounded-3 mb-3 d-flex align-items-center justify-content-between flex-wrap gap-2 no-print" style="background: #EFF6FF; border: 1px solid #BFDBFE; color: #1E3A8A;">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-users-cog fs-5 text-primary"></i>
                <div class="small">
                    <strong>Trạng thái hàng đợi buồng khám thời gian thực:</strong>
                    @if(($queueAheadCount ?? 0) === 0)
                        <span>Bạn đang là <strong>ca khám ưu tiên tiếp theo</strong> của <strong>{{ $appointment->doctor->user->name ?? 'Bác sĩ' }}</strong> trong ngày hẹn.</span>
                    @else
                        <span>Hiện có <strong>{{ $queueAheadCount }} bệnh nhân</strong> xếp lịch trước ca của bạn tại buồng khám của <strong>{{ $appointment->doctor->user->name ?? 'Bác sĩ' }}</strong>.</span>
                    @endif
                </div>
            </div>
            <span class="badge bg-primary text-white rounded-pill px-3 py-1">
                {{ $appointment->status === 'checked_in' ? 'Đã Check-in QR · Sẵn sàng vào khám' : 'Vui lòng đến trước 10 phút' }}
            </span>
        </div>
    @endif

    {{-- ALERT MESSAGES --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 rounded-3 shadow-sm py-2 px-3 mb-3 small d-flex align-items-center no-print" role="alert">
            <i class="fas fa-check-circle me-2 fs-5"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 rounded-3 shadow-sm py-2 px-3 mb-3 small d-flex align-items-center no-print" role="alert">
            <i class="fas fa-exclamation-circle me-2 fs-5"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- CLINICAL JOURNEY TRACKER (5 CHẶNG LÂM SÀNG CHUẨN Y KHOA) --}}
    <div class="clinical-card p-3 mb-3">
        @php
            $statusStep = match($appointment->status) {
                'pending' => 1,
                'confirmed' => 2,
                'checked_in' => 3,
                'in_progress' => 4,
                'completed' => 5,
                'cancelled' => 0,
                default => 1
            };

            $progressPercent = match($statusStep) {
                1 => 0,
                2 => 25,
                3 => 50,
                4 => 75,
                5 => 100,
                default => 0
            };

            $confirmedTime = optional($appointment->statusHistories->firstWhere('to_status', 'confirmed'))->created_at;
            $checkedInTime = optional($appointment->statusHistories->firstWhere('to_status', 'checked_in'))->created_at ?? $appointment->checked_in_at;
            $completedTime = optional($appointment->statusHistories->firstWhere('to_status', 'completed'))->created_at;
        @endphp

        @if($appointment->status === 'cancelled')
            <div class="alert alert-danger d-flex align-items-center justify-content-between p-3 rounded-3 mb-0 border-0 bg-danger-subtle">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; flex-shrink: 0;">
                        <i class="fas fa-calendar-times fs-5"></i>
                    </div>
                    <div>
                        <strong class="d-block text-danger fs-6">Lịch khám đã được hủy bỏ</strong>
                        <span class="small text-dark">
                            <strong>Lý do:</strong> {{ $appointment->cancellation_reason ?? 'Bệnh nhân hoặc phòng khám đã hủy ca khám này.' }}
                        </span>
                        @if($appointment->cancelled_at)
                            <div class="small text-muted mt-0.5">
                                Thời điểm hủy: {{ \Carbon\Carbon::parse($appointment->cancelled_at)->format('H:i - d/m/Y') }}
                            </div>
                        @endif
                    </div>
                </div>

                @if(in_array($appointment->payment_status, ['paid', 'success']))
                    <div class="text-end ps-3 border-start border-danger-subtle">
                        @if($appointment->refund_status === 'refunded')
                            <span class="badge bg-success text-white fw-bold mb-1">
                                <i class="fas fa-check-double me-1"></i>Đã hoàn tiền viện phí ({{ number_format($appointment->refund_amount ?? $appointment->total_amount ?? $appointment->total ?? ($appointment->service->price ?? 0), 0, ',', '.') }} đ)
                            </span>
                            <div class="small text-muted" style="font-size: 11px;">
                                {{ $appointment->refund_note ?: 'Đã hoàn trả 100% viện phí vào tài khoản của bệnh nhân.' }}
                            </div>
                        @else
                            <span class="badge bg-warning text-dark fw-bold mb-1">
                                <i class="fas fa-undo me-1"></i>Chờ hoàn tiền viện phí
                            </span>
                            <div class="small text-muted" style="font-size: 11px;">
                                {{ $appointment->refund_note ?: 'Đang xử lý đối soát hoàn tiền (1 - 3 ngày làm việc)' }}
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        @else
            <div class="clinical-journey">
                <div class="clinical-journey-line-bg"></div>
                <div class="clinical-journey-line-fill" style="width: {{ $progressPercent }}%;"></div>

                <div class="clinical-journey-steps">
                    {{-- CHẶNG 1: TIẾP NHẬN --}}
                    <div class="clinical-journey-step {{ $statusStep >= 1 ? ($statusStep > 1 ? 'completed' : 'active') : '' }}">
                        <div class="clinical-journey-circle">
                            @if($statusStep > 1) <i class="fas fa-check"></i> @else 1 @endif
                        </div>
                        <span class="clinical-journey-label">Đã tiếp nhận</span>
                        <span class="clinical-journey-sub">{{ $appointment->created_at ? $appointment->created_at->format('d/m H:i') : '' }}</span>
                    </div>

                    {{-- CHẶNG 2: XÁC NHẬN KHÁM --}}
                    <div class="clinical-journey-step {{ $statusStep >= 2 ? ($statusStep > 2 ? 'completed' : 'active') : '' }}">
                        <div class="clinical-journey-circle">
                            @if($statusStep > 2) <i class="fas fa-check"></i> @else 2 @endif
                        </div>
                        <span class="clinical-journey-label">Đã xác nhận</span>
                        <span class="clinical-journey-sub">{{ $confirmedTime ? $confirmedTime->format('d/m H:i') : ($statusStep >= 2 ? 'Đã duyệt' : 'Chờ duyệt') }}</span>
                    </div>

                    {{-- CHẶNG 3: CHECK-IN KIOSK --}}
                    <div class="clinical-journey-step {{ $statusStep >= 3 ? ($statusStep > 3 ? 'completed' : 'active') : '' }}">
                        <div class="clinical-journey-circle">
                            @if($statusStep > 3) <i class="fas fa-check"></i> @else 3 @endif
                        </div>
                        <span class="clinical-journey-label">Tiếp đón Kiosk</span>
                        <span class="clinical-journey-sub">{{ $checkedInTime ? \Carbon\Carbon::parse($checkedInTime)->format('d/m H:i') : ($statusStep >= 3 ? 'Đã quét QR' : 'Chờ quét QR') }}</span>
                    </div>

                    {{-- CHẶNG 4: KHÁM CHUYÊN KHOA --}}
                    <div class="clinical-journey-step {{ $statusStep >= 4 ? ($statusStep > 4 ? 'completed' : 'active') : '' }}">
                        <div class="clinical-journey-circle">
                            @if($statusStep > 4) <i class="fas fa-check"></i> @else 4 @endif
                        </div>
                        <span class="clinical-journey-label">Khám lâm sàng</span>
                        <span class="clinical-journey-sub">{{ $statusStep >= 4 ? 'Đang khám/CLS' : 'Chờ gọi vào phòng' }}</span>
                    </div>

                    {{-- CHẶNG 5: HOÀN TẤT & HỒ SƠ EMR --}}
                    <div class="clinical-journey-step {{ $statusStep >= 5 ? 'completed' : '' }}">
                        <div class="clinical-journey-circle">
                            @if($statusStep >= 5) <i class="fas fa-check"></i> @else 5 @endif
                        </div>
                        <span class="clinical-journey-label">Kết luận bệnh án</span>
                        <span class="clinical-journey-sub">{{ $completedTime ? $completedTime->format('d/m H:i') : ($statusStep >= 5 ? 'Đã kết luận' : 'Chờ hoàn tất') }}</span>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- CLINICAL 3-COLUMN INFORMATION GRID --}}
    <div class="row g-3 mb-3">
        {{-- BOX 1: BỆNH NHÂN & ĐỊNH DANH Y TẾ --}}
        <div class="col-md-4">
            <div class="clinical-info-card">
                <div class="clinical-info-header">
                    <i class="fas fa-id-card-alt text-primary"></i>
                    <span>{{ $appointment->booking_for === 'relative' ? 'HỒ SƠ KHÁM (ĐẶT HỘ NGƯỜI THÂN)' : 'HỒ SƠ BỆNH NHÂN' }}</span>
                </div>
                <div>
                    @if($appointment->booking_for === 'relative')
                        <h6 class="fw-bold text-dark mb-1 fs-6">{{ $appointment->relative_name }}</h6>
                        <div class="mb-2">
                            <span class="badge bg-danger-subtle text-danger rounded-pill px-2.5 py-1 fw-bold" style="font-size: 11px;">
                                <i class="fas fa-users me-1"></i>Quan hệ: {{ $appointment->relative_relationship }}
                            </span>
                            @if($appointment->relative_dob)
                                <span class="badge bg-light text-secondary border ms-1" style="font-size: 11px;">
                                    Sinh ngày: {{ \Carbon\Carbon::parse($appointment->relative_dob)->format('d/m/Y') }}
                                </span>
                            @endif
                        </div>
                        <div class="p-2 bg-light rounded-2 small text-secondary">
                            <i class="far fa-user me-1"></i>Tài khoản người đại diện: <strong>{{ $appointment->patient->name ?? optional($appointment->patient->user)->name }}</strong>
                            <br><i class="fas fa-phone-alt me-1 text-muted"></i>SĐT: {{ $appointment->patient->phone ?? optional($appointment->patient->user)->phone ?? '---' }}
                        </div>
                    @else
                        <h6 class="fw-bold text-dark mb-1 fs-6">{{ $appointment->patient->name ?? optional($appointment->patient->user)->name }}</h6>
                        <div class="small text-muted mb-1.5">
                            <i class="fas fa-phone me-1 text-primary"></i>{{ $appointment->patient->phone ?? optional($appointment->patient->user)->phone ?? 'Chưa cập nhật SĐT' }}
                        </div>
                        <div class="small text-muted mb-2">
                            <i class="fas fa-envelope me-1 text-primary"></i>{{ $appointment->patient->email ?? optional($appointment->patient->user)->email ?? 'Chưa có email' }}
                        </div>
                        <div class="p-2 bg-light rounded-2 small text-secondary">
                            <i class="fas fa-hospital-user me-1 text-primary"></i>Mã hồ sơ BN: <strong>#BN-{{ str_pad($appointment->patient_id, 5, '0', STR_PAD_LEFT) }}</strong>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- BOX 2: LỊCH KHÁM, BÁC SĨ & ĐỊỀU HƯỚNG PHÒNG --}}
        <div class="col-md-4">
            <div class="clinical-info-card">
                <div class="clinical-info-header">
                    <i class="fas fa-stethoscope text-primary"></i>
                    <span>LỊCH HẸN &amp; PHÒNG KHÁM CHUYÊN KHOA</span>
                </div>
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <strong class="text-primary fs-6">
                            <i class="far fa-calendar-check me-1"></i>
                            {{ $appointment->appointment_date ? \Carbon\Carbon::parse($appointment->appointment_date)->format('d/m/Y') : '---' }}
                        </strong>
                        <span class="badge bg-primary-subtle text-primary fw-bold" style="font-size: 11.5px;">
                            <i class="far fa-clock me-1"></i>{{ $appointment->timeSlot ? $appointment->timeSlot->formatted_time : ($appointment->medical_examination ?? 'Trong giờ') }}
                        </span>
                    </div>

                    <div class="small text-dark fw-bold mb-1">
                        <i class="fas fa-user-md text-primary me-1.5"></i>
                        {{ $appointment->doctor->user->name ?? 'Bác sĩ chuyên khoa' }}
                    </div>
                    <div class="small text-muted mb-2">
                        Khoa: <strong>{{ $appointment->doctor->department->name ?? 'Đa khoa' }}</strong>
                    </div>

                    {{-- PHÒNG KHÁM & SỐ THỨ TỰ (QUY CHẾ TIẾP ĐÓN CHUẨN Y KHOA) --}}
                    @php
                        $deptName = $appointment->doctor->department->name ?? 'Đa khoa';
                        $clinicArea = 'Khu khám ' . $deptName . ' (Tầng 2, Nhà A)';
                    @endphp
                    <div class="p-2.5 bg-light rounded-2 border">
                        <div class="d-flex justify-content-between align-items-center mb-1.5">
                            <small class="text-muted"><i class="fas fa-door-open me-1 text-primary"></i>Khu tiếp nhận:</small>
                            <span class="small fw-bold text-dark">{{ $clinicArea }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted"><i class="fas fa-sort-numeric-up me-1 text-success"></i>Số thứ tự (STT):</small>
                            @if($appointment->status === 'checked_in' || $appointment->status === 'in_progress')
                                <span class="badge bg-success text-white fw-bold px-2 py-1"><i class="fas fa-check-circle me-1"></i>Đã cấp STT tại Quầy</span>
                            @elseif($appointment->status === 'completed')
                                <span class="badge bg-secondary text-white fw-bold px-2 py-1"><i class="fas fa-flag-checkered me-1"></i>Đã hoàn tất khám</span>
                            @else
                                <span class="badge bg-primary-subtle text-primary fw-bold px-2 py-1"><i class="fas fa-qrcode me-1"></i>Cấp tự động khi quét QR</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- BOX 3: QUY CHẾ TIẾP ĐÓN & VIỆN PHÍ --}}
        <div class="col-md-4">
            <div class="clinical-info-card">
                <div class="clinical-info-header">
                    <i class="fas fa-file-invoice-dollar text-primary"></i>
                    <span>HÌNH THỨC THANH TOÁN &amp; BHYT</span>
                </div>
                <div>
                    <div class="mb-2">
                        <small class="text-muted d-block" style="font-size: 11px;">PHƯƠNG THỨC:</small>
                        <span class="small fw-bold text-dark">
                            {{ $appointment->payment_method ? strtoupper($appointment->payment_method) : 'Thanh toán trực tiếp / Cổng VNPAY' }}
                        </span>
                    </div>

                    <div class="mb-2">
                        <small class="text-muted d-block" style="font-size: 11px;">TRẠNG THÁI VIỆN PHÍ:</small>
                        @if(in_array($appointment->payment_status, ['paid', 'success']))
                            @if($appointment->refund_status === 'refunded')
                                <span class="badge bg-info-subtle text-info-emphasis fw-bold px-2.5 py-1" style="font-size: 11.5px;">
                                    <i class="fas fa-undo-alt me-1"></i>Đã hoàn trả viện phí (100%)
                                </span>
                            @elseif($appointment->status === 'cancelled' || $appointment->refund_status === 'pending')
                                <span class="badge bg-warning-subtle text-warning-emphasis fw-bold px-2.5 py-1" style="font-size: 11.5px;">
                                    <i class="fas fa-hourglass-half me-1"></i>Đã thu — Chờ hoàn tiền
                                </span>
                            @else
                                <span class="badge bg-success-subtle text-success fw-bold px-2.5 py-1" style="font-size: 11.5px;">
                                    <i class="fas fa-check-circle me-1"></i>Đã hoàn tất thanh toán (100%)
                                </span>
                            @endif
                        @else
                            <span class="badge bg-warning-subtle text-warning-emphasis fw-bold px-2.5 py-1" style="font-size: 11.5px;">
                                <i class="fas fa-clock me-1"></i>Chưa thanh toán (Thanh toán tại Quầy / VNPAY)
                            </span>
                        @endif
                    </div>

                    <div class="p-2 bg-light rounded-2 small text-secondary">
                        <i class="fas fa-shield-alt text-success me-1"></i>BHYT / Bảo hiểm bảo lãnh: Xuất trình thẻ khi quét QR tiếp đón tại quầy lễ tân.
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MAIN ROW: DỊCH VỤ KHÁM & CHỈ DẪN Y KHOA (LEFT) & THẺ TIẾP ĐÓN QR (RIGHT) --}}
    <div class="row g-3">
        {{-- LEFT COLUMN: THÔNG TIN DỊCH VỤ, LỜI DẶN Y TẾ & VIỆN PHÍ (MOBILE ORDER 2, DESKTOP ORDER 1) --}}
        <div class="col-lg-8 order-2 order-lg-1">
            <div class="clinical-card mb-3 overflow-hidden">
                {{-- Card Header --}}
                <div class="px-3 py-2.5 bg-light border-bottom d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark small text-uppercase" style="letter-spacing: 0.5px;">
                        <i class="fas fa-stethoscope text-primary me-1.5"></i>Dịch Vụ &amp; Chỉ Định Khám Chuyên Khoa
                    </span>
                    <span class="badge bg-primary-subtle text-primary fw-bold" style="font-size: 11px;">
                        Khám trực tiếp tại phòng khám
                    </span>
                </div>

                {{-- Dịch vụ chi tiết --}}
                <div class="p-3 border-bottom">
                    <div class="d-flex align-items-start justify-content-between gap-3">
                        <div class="d-flex align-items-start gap-3">
                            <div class="rounded-3 bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 52px; height: 52px; flex-shrink: 0;">
                                <i class="fas fa-heartbeat fs-3"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-dark mb-1 fs-6">
                                    {{ $appointment->service->name ?? 'Khám Nội tổng quát chuyên sâu' }}
                                </h6>
                                <p class="text-muted small mb-1.5" style="line-height: 1.4;">
                                    Bác sĩ phụ trách: <strong class="text-dark">{{ $appointment->doctor->user->name ?? 'Bác sĩ chuyên khoa' }}</strong>
                                    <span class="mx-1">•</span> Chuyên khoa: {{ $appointment->doctor->department->name ?? 'Đa khoa' }}
                                </p>
                                @if(optional($appointment->service)->description)
                                    <p class="text-secondary small fst-italic mb-2" style="font-size: 12px; line-height: 1.4;">
                                        {{ $appointment->service->description }}
                                    </p>
                                @endif
                                <div class="d-flex gap-2 flex-wrap">
                                    <span class="badge bg-light text-secondary border" style="font-size: 11px;">
                                        <i class="fas fa-check-circle text-success me-1"></i>Tiêu chuẩn Bộ Y Tế
                                    </span>
                                    <span class="badge bg-light text-secondary border" style="font-size: 11px;">
                                        <i class="fas fa-clock text-primary me-1"></i>Thời lượng khám dự kiến: 20-30 phút
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Đơn giá khám ban đầu --}}
                        <div class="text-end flex-shrink-0">
                            <div class="clinical-price-display">
                                {{ number_format($appointment->total ?? $appointment->service->price ?? 300000, 0, ',', '.') }} đ
                            </div>
                            <small class="text-muted d-block">Phí khám ban đầu</small>
                        </div>
                    </div>
                </div>

                {{-- Triệu chứng ghi nhận --}}
                @if($appointment->note)
                    <div class="px-3 py-2.5 bg-light border-bottom">
                        <small class="fw-bold text-dark d-block mb-1" style="font-size: 11.5px;">
                            <i class="fas fa-notes-medical text-primary me-1"></i>Triệu chứng ban đầu do bệnh nhân ghi nhận:
                        </small>
                        <p class="mb-0 text-muted small fst-italic ps-3 border-start border-2 border-primary">
                            "{{ $appointment->note }}"
                        </p>
                    </div>
                @endif

                {{-- HƯỚNG DẪN CHUẨN BỊ TRƯỚC KHI KHÁM (SENIOR BA REQUIREMENT) --}}
                <div class="p-3 border-bottom bg-white">
                    <h6 class="fw-bold text-dark mb-2 small d-flex align-items-center gap-1.5">
                        <i class="fas fa-clipboard-check text-success"></i> Hướng Dẫn Chuẩn Bị Trước Khi Đến Khám
                    </h6>
                    <div class="prep-checklist">
                        <div class="prep-checklist-item">
                            <i class="fas fa-check-circle text-success mt-0.5"></i>
                            <span><strong>Nhịn ăn sáng tối thiểu 6-8 tiếng:</strong> Nếu ca khám vào buổi sáng và bạn có nhu cầu xét nghiệm máu, đo đường huyết hoặc siêu âm ổ bụng.</span>
                        </div>
                        <div class="prep-checklist-item">
                            <i class="fas fa-check-circle text-success mt-0.5"></i>
                            <span><strong>Giấy tờ cần mang theo:</strong> Bản gốc CCCD/VNeID, Thẻ BHYT (hoặc ứng dụng VssID) để được hưởng quyền lợi giảm trừ viện phí theo quy định.</span>
                        </div>
                        <div class="prep-checklist-item">
                            <i class="fas fa-check-circle text-success mt-0.5"></i>
                            <span><strong>Hồ sơ y tế cũ:</strong> Vui lòng mang theo đơn thuốc đang dùng, phim chụp X-quang, MRI hoặc kết quả xét nghiệm trong vòng 6 tháng gần nhất (nếu có).</span>
                        </div>
                        <div class="prep-checklist-item">
                            <i class="fas fa-check-circle text-success mt-0.5"></i>
                            <span><strong>Thời gian có mặt:</strong> Quý khách vui lòng có mặt trước giờ hẹn <strong>10 - 15 phút</strong> để quét mã QR tiếp đón tự động tại Kiosk mà không cần xếp hàng.</span>
                        </div>
                    </div>
                </div>

                {{-- TỔNG KẾT VIỆN PHÍ & DỰ TOÁN --}}
                @php
                    $consultationFee = (float) ($appointment->total_amount ?? $appointment->total ?? optional($appointment->service)->price ?? 300000);
                    $prescriptionFee = 0;
                    if ($appointment->medicalRecord && $appointment->medicalRecord->prescriptionItems) {
                        foreach ($appointment->medicalRecord->prescriptionItems as $pItem) {
                            $medPrice = (float) (optional($pItem->medicine)->price ?? 0);
                            $prescriptionFee += $medPrice * max(1, (int) ($pItem->quantity ?? 1));
                        }
                    }
                    $grandTotalMedicalFee = $consultationFee + $prescriptionFee;
                @endphp
                <div class="p-3 bg-light">
                    <div class="row justify-content-between align-items-center">
                        <div class="col-md-5 small text-muted">
                            <i class="fas fa-info-circle me-1"></i>Chi phí bao gồm <strong>Phí khám chuyên khoa ban đầu</strong> và <strong>Đơn thuốc EMR</strong> (nếu Bác sĩ đã kê đơn).
                        </div>
                        <div class="col-md-6 col-12 text-md-end mt-2 mt-md-0">
                            <div class="d-flex justify-content-between justify-content-md-end gap-md-4 mb-1 small text-muted">
                                <span>Phí khám chuyên khoa:</span>
                                <span class="fw-semibold text-dark font-monospace">
                                    {{ number_format($consultationFee, 0, ',', '.') }} đ
                                </span>
                            </div>
                            @if($prescriptionFee > 0)
                                <div class="d-flex justify-content-between justify-content-md-end gap-md-4 mb-1 small text-muted">
                                    <span>Tiền thuốc theo đơn kê EMR:</span>
                                    <span class="fw-semibold text-dark font-monospace">
                                        {{ number_format($prescriptionFee, 0, ',', '.') }} đ
                                    </span>
                                </div>
                            @endif
                            <div class="d-flex justify-content-between justify-content-md-end gap-md-4 mb-1.5 small text-muted">
                                <span>Ưu đãi / Giảm trừ BHYT:</span>
                                <span class="fw-semibold text-success font-monospace">-0 đ</span>
                            </div>
                            <hr class="my-1.5 border-secondary-subtle">
                            <div class="d-flex justify-content-between justify-content-md-end align-items-center gap-md-3">
                                <span class="fw-bold text-dark">{{ $prescriptionFee > 0 ? 'Tổng cộng chi phí y tế:' : 'Tổng phí khám dự kiến:' }}</span>
                                <span class="clinical-price-display" style="font-size: 22px;">
                                    {{ number_format($grandTotalMedicalFee, 0, ',', '.') }} đ
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ACTION TOOLBAR (NO-PRINT) --}}
                <div class="p-3 bg-white border-top d-flex justify-content-end gap-2 flex-wrap no-print">
                    @php
                        $cancelCheck = $appointment->canBeCancelledByPatient(2);
                        $rescheduleCheck = $appointment->canBeRescheduledByPatient(2);
                    @endphp

                    @if(in_array($appointment->status, ['pending', 'confirmed']))
                        @if($rescheduleCheck['can'])
                            <button type="button" class="btn btn-outline-primary btn-sm px-3 rounded-pill" data-bs-toggle="modal" data-bs-target="#rescheduleModal">
                                <i class="fas fa-calendar-alt me-1"></i> Dời lịch khám
                            </button>
                        @endif

                        @if($cancelCheck['can'])
                            <button type="button" class="btn btn-outline-danger btn-sm px-3 rounded-pill" data-bs-toggle="modal" data-bs-target="#cancelModal">
                                <i class="fas fa-times-circle me-1"></i> Hủy lịch hẹn
                            </button>
                        @endif

                        @if(!in_array($appointment->payment_status, ['paid', 'success']))
                            <a href="{{ route('payment.checkout', $appointment->id) }}" class="btn btn-primary btn-sm px-4 rounded-pill shadow-sm">
                                <i class="fas fa-credit-card me-1"></i> Thanh toán trực tuyến VNPay
                            </a>
                        @endif
                    @endif

                    @if(in_array($appointment->status, ['completed', 'cancelled']))
                        <a href="{{ route('appointments.create', ['doctor_id' => $appointment->doctor_id, 'department_id' => optional($appointment->doctor)->department_id, 'service_id' => $appointment->service_id]) }}" class="btn btn-outline-primary btn-sm px-3 rounded-pill shadow-sm">
                            <i class="fas fa-redo me-1"></i> Đặt lịch tái khám với Bác sĩ này
                        </a>
                    @endif

                    @if($appointment->status === 'completed' && $appointment->medicalRecord)
                        <a href="{{ route('appointments.record', $appointment->id) }}" class="btn btn-success btn-sm px-3 rounded-pill shadow-sm">
                            <i class="fas fa-file-medical-alt me-1"></i> Xem hồ sơ bệnh án điện tử (EMR)
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN: DIGITAL CHECK-IN PASS & AUDIT TRAIL (MOBILE ORDER 1, DESKTOP ORDER 2) --}}
        <div class="col-lg-4 order-1 order-lg-2">
            {{-- THẺ TIẾP ĐÓN ĐIỆN TỬ (DIGITAL CHECK-IN PASS) --}}
            <div class="clinical-card p-3 text-center mb-3">
                <div class="d-inline-flex align-items-center gap-1.5 px-3 py-1 rounded-pill bg-primary-subtle text-primary fw-bold mb-2" style="font-size: 11.5px;">
                    <i class="fas fa-qrcode"></i>
                    <span>MÃ TIẾP ĐÓN TỰ ĐỘNG (QR PASS)</span>
                </div>

                {{-- QR CODE CANVAS (NỘI BỘ, SẮC NÉT, QUÉT BẰNG ĐIỆN THOẠI & KIOSK) --}}
                <div class="my-2 d-flex justify-content-center">
                    <div id="qrcode-container" class="border rounded-3 p-2.5 bg-white shadow-sm d-flex align-items-center justify-content-center position-relative" style="width: 176px; height: 176px;">
                        {{-- Rendered by QRCode.js or Fallback --}}
                        <div id="qrcode" class="d-flex align-items-center justify-content-center"></div>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-center gap-2 mt-1">
                    <span class="font-monospace fw-bold text-dark fs-6" id="apptCodeText" style="letter-spacing: 0.5px;">
                        {{ $appointment->appointment_code ?? ('APT-' . $appointment->id) }}
                    </span>
                    <button type="button" class="btn btn-light btn-sm border rounded-pill py-0 px-2 small text-secondary no-print" onclick="navigator.clipboard.writeText('{{ $appointment->appointment_code ?? ('APT-' . $appointment->id) }}'); this.innerHTML='<i class=\'fas fa-check text-success\'></i> Đã chép';" title="Sao chép mã tiếp đón">
                        <i class="far fa-copy"></i> Chép mã
                    </button>
                </div>

                <div class="mt-2 mb-2">
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1" style="font-size: 10.5px;">
                        <i class="fas fa-shield-alt me-1"></i>Mã định danh chuẩn Kiosk 125 Thái Thịnh
                    </span>
                </div>

                <p class="text-muted small mb-2.5 px-2" style="line-height: 1.4; font-size: 12px;">
                    Quét tại Kiosk tự động hoặc Quầy tiếp đón <strong>125 Thái Thịnh</strong> để nhận số thứ tự khám ngay mà không cần xếp hàng.
                </p>

                {{-- NÚT TẢI PHIẾU QR (PNG) & IN PHIẾU KHÁM TRỰC TIẾP TRÊN THẺ QR --}}
                <div class="d-grid gap-2 mt-2 pt-2 border-top no-print">
                    <button type="button" id="btnDownloadQrVoucher" class="btn btn-primary btn-sm rounded-pill py-1.5 fw-semibold shadow-sm" onclick="window.downloadMedicalPassPng && window.downloadMedicalPassPng()">
                        <i class="fas fa-download me-1"></i> Tải phiếu khám QR về máy (PNG)
                    </button>
                    <button type="button" class="btn btn-outline-dark btn-sm rounded-pill py-1.5 fw-semibold" onclick="window.print()">
                        <i class="fas fa-print me-1 text-primary"></i> In phiếu khám A4 / A5
                    </button>
                </div>
            </div>

            {{-- AUDIT TRAIL TIMELINE (LỊCH SỬ TIẾN TRÌNH LÂM SÀNG) --}}
            <div class="clinical-card p-3 mb-3">
                <h6 class="fw-bold text-dark mb-2.5 small border-bottom pb-2">
                    <i class="fas fa-history text-primary me-1"></i>Tiến Trình Xử Lý Hồ Sơ
                </h6>
                @if($appointment->statusHistories && $appointment->statusHistories->count() > 0)
                    <div class="position-relative border-start border-2 border-primary-subtle ms-2 ps-3">
                        @foreach($appointment->statusHistories as $history)
                            <div class="mb-2.5 position-relative">
                                <span class="position-absolute rounded-circle bg-primary" style="width: 7px; height: 7px; left: -19.5px; top: 6px;"></span>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-light text-dark border" style="font-size: 10px;">{{ ucfirst($history->to_status) }}</span>
                                    <small class="text-muted" style="font-size: 10.5px;">{{ $history->created_at->format('d/m H:i') }}</small>
                                </div>
                                <p class="text-dark small mb-0 mt-0.5" style="font-size: 11.5px;">{{ $history->note ?: 'Cập nhật trạng thái ca khám' }}</p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted small mb-0" style="font-size: 11.5px;">Hồ sơ ca khám đang được theo dõi theo quy trình tiếp đón chuẩn.</p>
                @endif
            </div>

            {{-- LIÊN HỆ & TỔNG ĐÀI PHÒNG KHÁM --}}
            <div class="clinical-card p-3 bg-light">
                <h6 class="fw-bold text-dark mb-1 small">
                    <i class="fas fa-hospital text-primary me-1"></i>Phòng khám Đa khoa Thái Thịnh
                </h6>
                <p class="text-muted small mb-1" style="font-size: 11.5px;"><i class="fas fa-map-marker-alt text-danger me-1"></i>125 Thái Thịnh, Đống Đa, Hà Nội</p>
                <p class="text-muted small mb-1" style="font-size: 11.5px;"><i class="fas fa-phone-alt text-success me-1"></i>Tổng đài cấp cứu &amp; tư vấn: <strong>1900 1234</strong></p>
                <small class="text-secondary d-block fst-italic" style="font-size: 10.5px;">
                    * Lưu ý: Khi đến khám, vui lòng chuẩn bị sẵn mã QR trên điện thoại hoặc bản in phiếu này.
                </small>
            </div>
        </div>
    </div>
</div>

{{-- MODAL HỦY LỊCH HẸN (VỚI HƯỚNG DẪN HOÀN TIỀN & CHÍNH SÁCH RÕ RÀNG) --}}
<div class="modal fade no-print" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <form action="{{ route('appointments.cancel', $appointment->id) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-header border-bottom">
                    <h6 class="modal-title fw-bold text-dark" id="cancelModalLabel">
                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>Xác nhận hủy lịch hẹn khám bệnh
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-dark mb-3">
                        Bạn có chắc chắn muốn hủy lịch hẹn <strong>{{ $appointment->appointment_code ?? '#' . $appointment->id }}</strong> không?
                    </p>

                    @if(in_array($appointment->payment_status, ['paid', 'success']))
                        <div class="alert alert-warning border-0 rounded-3 small mb-3">
                            <i class="fas fa-info-circle me-1"></i>
                            <strong>Chính sách hoàn tiền viện phí:</strong> Lịch hẹn này đã được thanh toán trực tuyến. Sau khi hủy, yêu cầu hoàn tiền sẽ được chuyển sang bộ phận kế toán xử lý tự động trong vòng <strong>1 - 3 ngày làm việc</strong> theo chính sách hoàn viện phí của phòng khám.
                        </div>
                    @else
                        <div class="alert alert-info border-0 rounded-3 small mb-3">
                            <i class="fas fa-info-circle me-1"></i>
                            Khung giờ khám của bạn sẽ được mở lại cho các bệnh nhân khác có nhu cầu ngay sau khi bạn xác nhận hủy.
                        </div>
                    @endif

                    <div class="mb-3">
                        <label for="cancelReason" class="form-label fw-bold small">Lý do hủy lịch khám <span class="text-danger">*</span>:</label>
                        <textarea class="form-control" id="cancelReason" name="reason" rows="3" placeholder="Nhập lý do bạn muốn hủy ca khám này (Ví dụ: Bận đột xuất, đã đỡ bệnh, dời sang ngày khác...)" required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-4" data-bs-dismiss="modal">Không, quay lại</button>
                    <button type="submit" class="btn btn-danger btn-sm rounded-pill px-4">Xác nhận hủy lịch</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL DỜI LỊCH KHÁM (PHÂN NHÓM CA SÁNG / CA CHIỀU & KIỂM TRA CHỖ TRỐNG) --}}
<div class="modal fade no-print" id="rescheduleModal" tabindex="-1" aria-labelledby="rescheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <form action="{{ route('appointments.reschedule', $appointment->id) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-header border-bottom">
                    <h6 class="modal-title fw-bold text-dark" id="rescheduleModalLabel">
                        <i class="fas fa-calendar-alt text-primary me-2"></i>Dời lịch khám sang khung giờ mới
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="p-2.5 bg-light rounded-3 mb-3 small">
                        <i class="fas fa-user-md text-primary me-1"></i>Bác sĩ: <strong>{{ $appointment->doctor->user->name ?? 'Bác sĩ' }}</strong>
                        <span class="mx-1">•</span> Khoa: <strong>{{ $appointment->doctor->department->name ?? 'Đa khoa' }}</strong>
                    </div>

                    <div class="mb-3">
                        <label for="rescheduleDate" class="form-label fw-bold small">Chọn ngày khám mới:</label>
                        <input type="date" class="form-control" id="rescheduleDate"
                               min="{{ now()->format('Y-m-d') }}"
                               max="{{ now()->addDays(14)->format('Y-m-d') }}"
                               value="{{ now()->addDay()->format('Y-m-d') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small d-flex justify-content-between align-items-center mb-1">
                            <span>Khung giờ khả dụng:</span>
                            <span class="small text-muted fw-normal" id="rescheduleStatus">Đang kiểm tra...</span>
                        </label>
                        <div id="rescheduleSlotsGrid" style="max-height: 240px; overflow-y: auto;">
                            {{-- Generated by JS --}}
                        </div>
                        <input type="hidden" name="new_time_slot_id" id="new_time_slot_id" required>
                    </div>

                    <div class="mb-2">
                        <label for="rescheduleReason" class="form-label fw-bold small">Lý do dời lịch (tùy chọn):</label>
                        <textarea class="form-control" id="rescheduleReason" name="reschedule_reason" rows="2" placeholder="Ví dụ: Bận công tác đột xuất, muốn đổi sang ca chiều..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-4" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4" id="btnSubmitReschedule" disabled>
                        Xác nhận dời lịch
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
@php
    $qrPassData = [
        'apptCode' => $appointment->appointment_code ?? ('APT-' . $appointment->id),
        'patientName' => $appointment->patient_display_name ?? ($appointment->patient->name ?? 'Bệnh nhân'),
        'patientPhone' => $appointment->patient->phone ?? optional($appointment->patient->user)->phone ?? '---',
        'patientIdCode' => '#BN-' . str_pad((string) $appointment->patient_id, 5, '0', STR_PAD_LEFT),
        'doctorName' => $appointment->doctor->user->name ?? 'Bác sĩ chuyên khoa',
        'deptName' => $appointment->doctor->department->name ?? 'Đa khoa',
        'serviceName' => $appointment->service->name ?? 'Khám chuyên khoa',
        'apptDateFormatted' => $appointment->appointment_date ? \Carbon\Carbon::parse($appointment->appointment_date)->format('d/m/Y') : '---',
        'apptTimeFormatted' => $appointment->timeSlot ? $appointment->timeSlot->formatted_time : ($appointment->medical_examination ?? 'Trong giờ hành chính'),
        'statusLabel' => $appointment->status_label ?? ucfirst($appointment->status),
        'paymentStatusText' => in_array($appointment->payment_status, ['paid', 'success']) ? ($appointment->refund_status === 'refunded' ? 'Đã hoàn tiền viện phí' : 'Đã thanh toán viện phí') : 'Thanh toán tại Quầy / VNPay',
        'totalFeeFormatted' => number_format($grandTotalMedicalFee ?? ($appointment->total_amount ?? $appointment->total ?? ($appointment->service->price ?? 300000)), 0, ',', '.') . ' VNĐ',
    ];
@endphp
{{-- QRCODE.JS (NHẸ, CHẠY TRỰC TIẾP CLIENT-SIDE KHÔNG PHỤ THUỘC API NGOÀI) --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. RENDER MÃ QR THỰC TẾ CHUẨN KIOSK 125 THÁI THỊNH
    const passInfo = @json($qrPassData);
    const apptCode = passInfo.apptCode;
    const patientName = passInfo.patientName;
    const patientPhone = passInfo.patientPhone;
    const patientIdCode = passInfo.patientIdCode;
    const doctorName = passInfo.doctorName;
    const deptName = passInfo.deptName;
    const serviceName = passInfo.serviceName;
    const apptDateFormatted = passInfo.apptDateFormatted;
    const apptTimeFormatted = passInfo.apptTimeFormatted;
    const statusLabel = passInfo.statusLabel;
    const paymentStatusText = passInfo.paymentStatusText;
    const totalFeeFormatted = passInfo.totalFeeFormatted;

    // Payload mã QR thực tế (quét bằng Zalo/Camera/Máy quét Kiosk 2D đều hiển thị thông tin phiếu khám)
    const qrPayload = `PK125THAITHINH|MA:${apptCode}|NGAY:${apptDateFormatted}|GIO:${apptTimeFormatted}`;
    const qrContainer = document.getElementById('qrcode');

    function renderFallbackQrImage() {
        if (!qrContainer) return;
        const img = document.createElement('img');
        img.crossOrigin = 'anonymous';
        img.src = 'https://api.qrserver.com/v1/create-qr-code/?size=152x152&margin=2&data=' + encodeURIComponent(qrPayload);
        img.alt = 'QR ' + apptCode;
        img.width = 152;
        img.height = 152;
        img.className = 'img-fluid';
        qrContainer.innerHTML = '';
        qrContainer.appendChild(img);
    }

    if (qrContainer && typeof QRCode !== 'undefined') {
        try {
            qrContainer.innerHTML = '';
            new QRCode(qrContainer, {
                text: qrPayload,
                width: 152,
                height: 152,
                colorDark: "#0f172a",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.M
            });
            // Đảm bảo hiển thị sắc nét trên mọi trình duyệt
            setTimeout(() => {
                const canvasEl = qrContainer.querySelector('canvas');
                const imgEl = qrContainer.querySelector('img');
                if (canvasEl && (!imgEl || !imgEl.getAttribute('src'))) {
                    canvasEl.style.display = 'block';
                }
            }, 60);
        } catch (e) {
            renderFallbackQrImage();
        }
    } else {
        renderFallbackQrImage();
    }

    // 1B. HÀM TẢI PHIẾU KHÁM ĐIỆN TỬ KÈM MÃ QR (PNG ĐỘ PHÂN GIẢI CAO)
    window.downloadMedicalPassPng = function() {
        const canvas = document.createElement('canvas');
        const W = 800;
        const H = 1040;
        canvas.width = W;
        canvas.height = H;
        const ctx = canvas.getContext('2d');

        // Nền phiếu khám
        ctx.fillStyle = '#f8fafc';
        ctx.fillRect(0, 0, W, H);

        // Khung phiếu chính
        ctx.fillStyle = '#ffffff';
        ctx.strokeStyle = '#cbd5e1';
        ctx.lineWidth = 2;
        ctx.fillRect(36, 36, W - 72, H - 72);
        ctx.strokeRect(36, 36, W - 72, H - 72);

        // Dải màu thương hiệu 125 Thái Thịnh ở đỉnh phiếu
        ctx.fillStyle = '#0052cc';
        ctx.fillRect(36, 36, W - 72, 112);

        // Tiêu đề Phòng khám
        ctx.fillStyle = '#ffffff';
        ctx.font = 'bold 24px Inter, Segoe UI, Arial, sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText('PHÒNG KHÁM ĐA KHOA 125 THÁI THỊNH', W / 2, 82);

        ctx.fillStyle = '#e0f2fe';
        ctx.font = '500 15px Inter, Segoe UI, Arial, sans-serif';
        ctx.fillText('125 Thái Thịnh, Đống Đa, Hà Nội • Tổng đài: 1900 1234', W / 2, 114);

        // Nhãn Phiếu Tiếp Đón
        ctx.fillStyle = '#0f172a';
        ctx.font = 'bold 22px Inter, Segoe UI, Arial, sans-serif';
        ctx.fillText('PHIẾU TIẾP NHẬN KHÁM BỆNH ĐIỆN TỬ', W / 2, 192);

        // Trạng thái hồ sơ
        ctx.fillStyle = '#eff6ff';
        ctx.fillRect(W / 2 - 140, 208, 280, 34);
        ctx.strokeStyle = '#bfdbfe';
        ctx.lineWidth = 1.5;
        ctx.strokeRect(W / 2 - 140, 208, 280, 34);
        ctx.fillStyle = '#0052cc';
        ctx.font = 'bold 14px Inter, Segoe UI, Arial, sans-serif';
        ctx.fillText('TRẠNG THÁI: ' + statusLabel.toUpperCase(), W / 2, 230);

        // Khung chứa mã QR ở trung tâm
        const qrBoxSize = 230;
        const qrBoxX = (W - qrBoxSize) / 2;
        const qrBoxY = 260;
        ctx.fillStyle = '#ffffff';
        ctx.strokeStyle = '#94a3b8';
        ctx.lineWidth = 2;
        ctx.fillRect(qrBoxX, qrBoxY, qrBoxSize, qrBoxSize);
        ctx.strokeRect(qrBoxX, qrBoxY, qrBoxSize, qrBoxSize);

        // Vẽ QR từ #qrcode canvas hoặc img
        const srcCanvas = qrContainer ? qrContainer.querySelector('canvas') : null;
        const srcImg = qrContainer ? qrContainer.querySelector('img') : null;
        if (srcCanvas) {
            ctx.drawImage(srcCanvas, qrBoxX + 19, qrBoxY + 19, 192, 192);
        } else if (srcImg && srcImg.complete) {
            try {
                ctx.drawImage(srcImg, qrBoxX + 19, qrBoxY + 19, 192, 192);
            } catch (err) {}
        }

        // Mã lịch hẹn nổi bật dưới QR
        ctx.fillStyle = '#0f172a';
        ctx.font = 'bold 26px Consolas, monospace';
        ctx.fillText('#' + apptCode, W / 2, 526);

        ctx.fillStyle = '#64748b';
        ctx.font = '14px Inter, Segoe UI, Arial, sans-serif';
        ctx.fillText('Quét mã QR tại Kiosk tiếp đón tự động Tầng 1 để nhận số thứ tự vào phòng khám', W / 2, 552);

        // Đường kẻ phân cách
        ctx.strokeStyle = '#e2e8f0';
        ctx.lineWidth = 1.5;
        ctx.beginPath();
        ctx.moveTo(76, 576);
        ctx.lineTo(W - 76, 576);
        ctx.stroke();

        // Bảng thông tin chi tiết (2 cột: Nhãn trái - Giá trị phải)
        const rows = [
            ['Họ và tên người khám:', patientName + ' (' + patientIdCode + ')'],
            ['Số điện thoại liên hệ:', patientPhone],
            ['Ngày hẹn khám:', apptDateFormatted],
            ['Khung giờ tiếp nhận:', apptTimeFormatted],
            ['Bác sĩ phụ trách:', doctorName],
            ['Chuyên khoa & Khu khám:', 'Khoa ' + deptName + ' (Tầng 2 - Nhà A)'],
            ['Dịch vụ đăng ký:', serviceName],
            ['Tổng viện phí dự kiến:', totalFeeFormatted + ' (' + paymentStatusText + ')']
        ];

        let startY = 618;
        rows.forEach((row, idx) => {
            if (idx % 2 === 0) {
                ctx.fillStyle = '#f8fafc';
                ctx.fillRect(76, startY - 24, W - 152, 38);
            }
            ctx.textAlign = 'left';
            ctx.fillStyle = '#475569';
            ctx.font = '600 15px Inter, Segoe UI, Arial, sans-serif';
            ctx.fillText(row[0], 92, startY);

            ctx.textAlign = 'right';
            ctx.fillStyle = idx === rows.length - 1 ? '#0052cc' : '#0f172a';
            ctx.font = 'bold 15.5px Inter, Segoe UI, Arial, sans-serif';
            ctx.fillText(String(row[1]).substring(0, 52), W - 92, startY);

            startY += 42;
        });

        // Chân trang lưu ý
        ctx.textAlign = 'center';
        ctx.fillStyle = '#64748b';
        ctx.font = 'italic 13.5px Inter, Segoe UI, Arial, sans-serif';
        ctx.fillText('* Lưu ý: Vui lòng mang theo CCCD/VNeID và Thẻ BHYT (nếu có), có mặt trước giờ hẹn 10-15 phút.', W / 2, 972);

        // Kích hoạt tải file PNG
        const link = document.createElement('a');
        link.download = 'Phieu-Kham-125ThaiThinh-' + apptCode + '.png';
        link.href = canvas.toDataURL('image/png');
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    };

    // 2. DỜI LỊCH KHÁM (PHÂN NHÓM CA SÁNG / CA CHIỀU)
    const reschedDate = document.getElementById('rescheduleDate');
    const reschedGrid = document.getElementById('rescheduleSlotsGrid');
    const reschedStatus = document.getElementById('rescheduleStatus');
    const newSlotInput = document.getElementById('new_time_slot_id');
    const btnSubmit = document.getElementById('btnSubmitReschedule');
    const doctorId = {{ $appointment->doctor_id }};
    const currentSlotId = {{ $appointment->time_slot_id ?? 0 }};

    function loadRescheduleSlots() {
        if (!reschedDate || !reschedGrid) return;
        const date = reschedDate.value;
        if (!date) return;

        reschedGrid.innerHTML = '<div class="text-center py-3 text-muted small"><span class="spinner-border spinner-border-sm me-2"></span>Đang truy vấn lịch làm việc của Bác sĩ...</div>';
        reschedStatus.textContent = 'Đang tải...';
        newSlotInput.value = '';
        btnSubmit.disabled = true;

        fetch(`/appointments/time-slots?doctor_id=${doctorId}&date=${date}`)
            .then(res => res.json())
            .then(data => {
                reschedGrid.innerHTML = '';
                if (data.slots && data.slots.length > 0) {
                    const availableSlots = data.slots.filter(s => s.is_available && s.id !== currentSlotId);
                    reschedStatus.textContent = `Còn ${availableSlots.length} khung giờ trống`;

                    if (availableSlots.length === 0) {
                        reschedGrid.innerHTML = '<div class="alert alert-warning py-2 px-3 small mb-0 rounded-3"><i class="fas fa-exclamation-triangle me-1"></i>Bác sĩ đã kín lịch trong ngày này. Vui lòng chọn một ngày khác.</div>';
                        return;
                    }

                    // Phân nhóm Ca Sáng & Ca Chiều
                    const morningSlots = availableSlots.filter(s => parseInt(s.start_time.split(':')[0]) < 12);
                    const afternoonSlots = availableSlots.filter(s => parseInt(s.start_time.split(':')[0]) >= 12);

                    let html = '';
                    if (morningSlots.length > 0) {
                        html += '<div class="small fw-bold text-primary mb-1 mt-1"><i class="fas fa-sun me-1"></i>Ca sáng (07:30 - 11:30):</div><div class="row g-2 mb-2">';
                        morningSlots.forEach(slot => {
                            html += `
                                <div class="col-6">
                                    <button type="button" class="btn btn-outline-primary btn-sm w-100 rounded-3 text-start p-2 slot-btn" data-slot-id="${slot.id}">
                                        <div class="fw-bold">${slot.formatted_time}</div>
                                        <small class="text-muted">Còn ${slot.remaining} chỗ</small>
                                    </button>
                                </div>
                            `;
                        });
                        html += '</div>';
                    }

                    if (afternoonSlots.length > 0) {
                        html += '<div class="small fw-bold text-warning-emphasis mb-1 mt-1"><i class="fas fa-cloud-sun me-1"></i>Ca chiều (13:00 - 17:00):</div><div class="row g-2 mb-2">';
                        afternoonSlots.forEach(slot => {
                            html += `
                                <div class="col-6">
                                    <button type="button" class="btn btn-outline-primary btn-sm w-100 rounded-3 text-start p-2 slot-btn" data-slot-id="${slot.id}">
                                        <div class="fw-bold">${slot.formatted_time}</div>
                                        <small class="text-muted">Còn ${slot.remaining} chỗ</small>
                                    </button>
                                </div>
                            `;
                        });
                        html += '</div>';
                    }

                    reschedGrid.innerHTML = html;

                    // Gắn sự kiện chọn khung giờ
                    reschedGrid.querySelectorAll('.slot-btn').forEach(btn => {
                        btn.addEventListener('click', function() {
                            reschedGrid.querySelectorAll('.slot-btn').forEach(b => {
                                b.classList.remove('btn-primary', 'text-white');
                                b.classList.add('btn-outline-primary');
                            });
                            this.classList.remove('btn-outline-primary');
                            this.classList.add('btn-primary', 'text-white');
                            newSlotInput.value = this.getAttribute('data-slot-id');
                            btnSubmit.disabled = false;
                        });
                    });
                } else {
                    reschedStatus.textContent = 'Hết lịch';
                    reschedGrid.innerHTML = '<div class="alert alert-secondary py-2 px-3 small mb-0 rounded-3">Bác sĩ không có lịch trực khám trong ngày đã chọn.</div>';
                }
            })
            .catch(err => {
                reschedGrid.innerHTML = '<div class="alert alert-danger py-2 px-3 small mb-0 rounded-3">Không thể tải khung giờ làm việc. Vui lòng thử lại.</div>';
            });
    }

    if (reschedDate) {
        reschedDate.addEventListener('change', loadRescheduleSlots);
        const modalEl = document.getElementById('rescheduleModal');
        if (modalEl) {
            modalEl.addEventListener('shown.bs.modal', loadRescheduleSlots);
        }
    }
});
</script>
@endpush
@endsection
