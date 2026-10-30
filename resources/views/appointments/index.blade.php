@extends('layouts.profile')

@section('title', 'Lịch hẹn của tôi - Phòng khám Thái Thịnh')
@section('breadcrumb_current', 'Lịch hẹn của tôi')

@section('content')
<div class="mb-4">
    {{-- Header Banner --}}
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <div>
            <h4 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                <i class="far fa-calendar-check text-primary"></i> Quản Lý Lịch Hẹn Khám Bệnh
            </h4>
            <small class="text-muted">Theo dõi trạng thái lịch khám, mã QR check-in tiếp đón và kết quả khám bệnh</small>
        </div>
        <a href="{{ route('appointments.create') }}" class="btn btn-tiki-primary btn-sm">
            <i class="fas fa-plus-circle me-1"></i> Đặt lịch khám mới
        </a>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 rounded-2 mb-3" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 rounded-2 mb-3" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- TIKI STATUS TABS (ĐƠN HÀNG CỦA TÔI STYLE) --}}
    @php
        $currentStatus = request('status', '');
    @endphp
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div class="tiki-tabs-nav mb-0 flex-grow-1">
            <a href="{{ route('appointments.index') }}" class="tiki-tab-link {{ $currentStatus === '' ? 'active' : '' }}">
                <span>Tất cả</span>
            </a>
            <a href="{{ route('appointments.index', ['status' => 'pending']) }}" class="tiki-tab-link {{ $currentStatus === 'pending' ? 'active' : '' }}">
                <span>Chờ duyệt</span>
            </a>
            <a href="{{ route('appointments.index', ['status' => 'confirmed']) }}" class="tiki-tab-link {{ $currentStatus === 'confirmed' ? 'active' : '' }}">
                <span>Đã xác nhận</span>
            </a>
            <a href="{{ route('appointments.index', ['status' => 'checked_in']) }}" class="tiki-tab-link {{ $currentStatus === 'checked_in' ? 'active' : '' }}">
                <span>Đã tiếp nhận QR</span>
            </a>
            <a href="{{ route('appointments.index', ['status' => 'completed']) }}" class="tiki-tab-link {{ $currentStatus === 'completed' ? 'active' : '' }}">
                <span>Đã khám xong</span>
            </a>
            <a href="{{ route('appointments.index', ['status' => 'cancelled']) }}" class="tiki-tab-link {{ $currentStatus === 'cancelled' ? 'active' : '' }}">
                <span>Đã hủy</span>
            </a>
        </div>
        <div style="min-width: 280px;">
            <form action="{{ route('appointments.index') }}" method="GET" class="d-flex gap-1">
                @if($currentStatus !== '')
                    <input type="hidden" name="status" value="{{ $currentStatus }}">
                @endif
                <input type="text" name="search" id="quickSearchAppt" value="{{ request('search') }}" class="tiki-form-control w-100" placeholder="Tìm mã lịch, bác sĩ, tên người khám...">
                <button type="submit" class="ts-btn px-3" title="Tìm kiếm trên toàn hệ thống">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>
    </div>

    {{-- APPOINTMENTS LIST --}}
    @if($appointments->isEmpty())
        <div class="tiki-card text-center py-5">
            <div class="my-4">
                <h5 class="fw-bold text-dark">Chưa có lịch hẹn nào</h5>
                <p class="text-muted small mb-4">
                    @if($currentStatus !== '')
                        Không tìm thấy ca khám nào ở trạng thái này.
                    @else
                        Bạn chưa đăng ký lịch khám nào tại Phòng khám 125 Thái Thịnh.
                    @endif
                </p>
                <a href="{{ route('appointments.create') }}" class="ts-btn">
                    <span>Đặt lịch khám ngay</span>
                </a>
            </div>
        </div>
    @else
        <div class="d-flex flex-column gap-3" id="appointmentCardsContainer">
            @foreach($appointments as $appointment)
                <div class="tiki-card p-4 appointment-card-item">
                    {{-- Card Header: Code, Date & Status --}}
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pb-3 mb-3" style="background-color: var(--bg); padding: 12px 16px; border-radius: var(--radius);">
                        <div class="d-flex align-items-center gap-3">
                            <span class="med-badge-spec ts-num fw-bold">
                                #{{ $appointment->appointment_code ?? str_pad($appointment->id, 6, '0', STR_PAD_LEFT) }}
                            </span>
                            <small class="text-muted ts-num">
                                Ngày đặt: {{ $appointment->created_at ? $appointment->created_at->format('d/m/Y H:i') : '---' }}
                            </small>
                        </div>
                        <div class="d-flex align-items-center flex-wrap gap-2">
                            @if(in_array($appointment->payment_status, ['success', 'paid']))
                                <span class="tiki-status-badge completed">Đã thanh toán</span>
                            @elseif($appointment->status !== 'cancelled')
                                <span class="tiki-status-badge pending">Chưa thanh toán</span>
                            @endif

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
                            <span class="tiki-status-badge {{ $statusClass }}">
                                {{ $appointment->status_label ?? $appointment->status }}
                            </span>
                        </div>
                    </div>

                    {{-- Card Body --}}
                    <div class="row align-items-center g-3">
                        {{-- Doctor and Service Detail --}}
                        <div class="col-md-7">
                            <div class="d-flex align-items-start gap-3">
                                @if(!empty($appointment->doctor->avatar))
                                    <img src="{{ asset('storage/' . $appointment->doctor->avatar) }}"
                                         alt="{{ $appointment->doctor->user->name ?? 'Bác sĩ' }}"
                                         class="object-fit-cover"
                                         style="width: 56px; height: 68px; border-radius: var(--radius); flex-shrink: 0;">
                                @else
                                    <img src="{{ asset('image/clinic/doctor-male-senior.jpg') }}"
                                         alt="{{ $appointment->doctor->user->name ?? 'Bác sĩ' }}"
                                         class="object-fit-cover"
                                         style="width: 56px; height: 68px; border-radius: var(--radius); flex-shrink: 0;">
                                @endif
                                <div>
                                    <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                                        <h6 class="fw-bold text-dark mb-0">
                                            {{ $appointment->doctor->formatted_name ?? $appointment->doctor->user->name ?? 'Bác sĩ chuyên khoa' }}
                                        </h6>
                                        <span class="med-badge-spec">
                                            {{ $appointment->doctor->department->name ?? $appointment->department->name ?? 'Đa khoa' }}
                                        </span>
                                    </div>
                                    <p class="text-muted small mb-1">
                                        Gói khám: <strong class="text-dark">{{ $appointment->service->name ?? 'Khám chuyên khoa' }}</strong>
                                    </p>
                                    <div class="small text-muted">
                                        Người khám:
                                        @if($appointment->booking_for === 'relative')
                                            <strong class="text-danger">{{ $appointment->relative_name }}</strong> ({{ $appointment->relative_relationship }})
                                        @else
                                            <strong class="text-dark">{{ Auth::user()->name }}</strong> (Bản thân)
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Appointment Schedule Time & Price --}}
                        <div class="col-md-5 text-md-end">
                            <div class="mb-2">
                                <div class="text-primary fw-bold ts-num">
                                    Ngày khám: {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d/m/Y') }}
                                </div>
                                <div class="text-success fw-semibold small ts-num">
                                    Ca khám: {{ $appointment->timeSlot ? $appointment->timeSlot->formatted_time : ($appointment->appointment_time ? \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i') : ($appointment->medical_examination ?? 'Trong giờ hành chính')) }}
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-md-end gap-2">
                                <small class="text-muted">Chi phí khám:</small>
                                <span class="ts-pass-price">
                                    {{ number_format($appointment->total ?? $appointment->total_amount ?? $appointment->service->price ?? 300000, 0, ',', '.') }} đ
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Card Footer Actions --}}
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pt-3 mt-3" style="background-color: var(--bg); padding: 12px 16px; border-radius: var(--radius);">
                        <div class="small text-muted">
                            @if($appointment->status === 'confirmed' || $appointment->status === 'checked_in')
                                <span class="text-success fw-semibold">Mã QR tiếp đón đã sẵn sàng</span>
                            @elseif($appointment->status === 'pending')
                                <span class="text-warning fw-semibold">Đang chờ phòng khám xác nhận</span>
                            @elseif($appointment->status === 'completed')
                                <span class="text-primary fw-semibold">Đã có kết quả khám bệnh điện tử</span>
                            @endif
                        </div>

                        <div class="d-flex align-items-center flex-wrap gap-2">
                            @if(in_array($appointment->status, ['confirmed', 'checked_in']))
                                @php
                                    $qrCodeVal = $appointment->appointment_code ?: ('TT-' . str_pad($appointment->id, 5, '0', STR_PAD_LEFT));
                                    $qrPatientVal = $appointment->booking_for === 'relative' ? $appointment->relative_name : (Auth::user()->name ?? 'Bệnh nhân');
                                    $qrPayloadStr = 'PK125THAITHINH|MA:' . $qrCodeVal . '|BN:' . $qrPatientVal . '|NGAY:' . \Carbon\Carbon::parse($appointment->appointment_date)->format('d/m/Y');
                                    $qrImgUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=240x240&margin=8&data=' . urlencode($qrPayloadStr);
                                @endphp
                                <button type="button"
                                        class="ts-btn-secondary js-open-quick-qr"
                                        data-code="{{ $qrCodeVal }}"
                                        data-patient="{{ $qrPatientVal }}"
                                        data-doctor="{{ $appointment->doctor->formatted_name ?? $appointment->doctor->user->name ?? 'Bác sĩ chuyên khoa' }}"
                                        data-date="{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d/m/Y') }}"
                                        data-qr="{{ $qrImgUrl }}"
                                        data-show-url="{{ route('appointments.show', $appointment->id) }}">
                                    <i class="fas fa-qrcode me-1"></i>
                                    <span>Mở nhanh Mã QR</span>
                                </button>
                            @endif

                            @if(!in_array($appointment->payment_status, ['success', 'paid']) && in_array($appointment->status, ['pending', 'confirmed']))
                                <a href="{{ route('payment.checkout', $appointment->id) }}" class="ts-btn-secondary">
                                    <span>Thanh toán VNPay</span>
                                </a>
                            @endif

                            @if(in_array($appointment->status, ['completed', 'cancelled']))
                                <a href="{{ route('appointments.create', array_filter(['doctor_id' => $appointment->doctor_id, 'department_id' => $appointment->department_id ?? optional($appointment->doctor)->department_id, 'service_id' => $appointment->service_id])) }}" class="ts-btn-secondary">
                                    <span>Đặt khám lại</span>
                                </a>
                            @endif

                            @if($appointment->status === 'completed')
                                <a href="{{ route('appointments.record', $appointment->id) }}" class="ts-btn-secondary">
                                    <span>Xem bệnh án</span>
                                </a>
                            @endif

                            <a href="{{ route('appointments.show', $appointment->id) }}" class="ts-btn">
                                <span>Chi tiết lịch hẹn</span>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- Pagination --}}
            <div class="d-flex justify-content-center mt-3">
                {{ $appointments->links() }}
            </div>
        </div>
    @endif
</div>

{{-- Modal Mở Nhanh Mã QR Tiếp Đón --}}
<div class="modal fade" id="quickQrModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content p-4 text-center" style="border-radius: 16px;">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="badge bg-primary bg-opacity-10 text-primary fw-bold">QUẦY KIOSK TIẾP ĐÓN 125 THÁI THỊNH</span>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <h5 class="fw-bold mb-1 ts-num" id="quickQrCodeText">#TT-00000</h5>
            <p class="text-muted small mb-3">Đưa mã QR này vào máy quét tại quầy Lễ tân để Check-in 1 chạm</p>
            <div class="p-3 d-inline-block mx-auto mb-3 rounded-3" style="border: 2px dashed var(--border-color); background: #fff;">
                <img id="quickQrImg" src="" alt="QR Check-in" style="width: 200px; height: 200px;">
            </div>
            <div class="p-3 rounded-3 text-start small mb-3" style="background-color: var(--bg);">
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">Người khám:</span>
                    <strong id="quickQrPatient">-</strong>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">Bác sĩ:</span>
                    <strong id="quickQrDoctor">-</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Ngày hẹn:</span>
                    <strong class="ts-num" id="quickQrDate">-</strong>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="ts-btn-secondary flex-grow-1" data-bs-dismiss="modal">Đóng</button>
                <a href="#" id="quickQrDetailBtn" class="ts-btn flex-grow-1">Mở phiếu đầy đủ</a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('quickSearchAppt');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const q = this.value.toLowerCase().trim();
            document.querySelectorAll('.appointment-card-item').forEach(card => {
                const text = card.textContent.toLowerCase();
                card.style.display = (!q || text.includes(q)) ? '' : 'none';
            });
        });
    }

    document.querySelectorAll('.js-open-quick-qr').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('quickQrCodeText').textContent = '#' + this.getAttribute('data-code');
            document.getElementById('quickQrPatient').textContent = this.getAttribute('data-patient');
            document.getElementById('quickQrDoctor').textContent = this.getAttribute('data-doctor');
            document.getElementById('quickQrDate').textContent = this.getAttribute('data-date');
            document.getElementById('quickQrImg').src = this.getAttribute('data-qr');
            document.getElementById('quickQrDetailBtn').href = this.getAttribute('data-show-url');
            const modalEl = document.getElementById('quickQrModal');
            if (window.bootstrap && modalEl) {
                new bootstrap.Modal(modalEl).show();
            }
        });
    });
});
</script>
@endpush
@endsection
