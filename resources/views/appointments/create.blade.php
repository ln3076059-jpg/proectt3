@extends('home.frontend')

@section('title', 'Đặt lịch khám bệnh - Phòng khám Thái Thịnh')

@section('content')
<div class="ts-frame py-5">
    {{-- Breadcrumb & Title --}}
    <div class="row justify-content-center mb-4">
        <div class="col-lg-10">
            <nav class="tiki-breadcrumb" aria-label="breadcrumb">
                <a href="{{ route('home') }}">Trang chủ</a>
                <span>/</span>
                <a href="{{ route('appointments.index') }}">Lịch hẹn</a>
                <span>/</span>
                <span>Đặt lịch khám mới</span>
            </nav>
            <div class="d-flex align-items-end justify-content-between flex-wrap gap-3">
                <div>
                    <h1 class="ts-section-title mb-2">Đặt lịch khám trực tuyến</h1>
                    <p class="mb-0" style="color: var(--muted);">Chọn chuyên khoa, bác sĩ phụ trách và khung giờ tiếp đón tại số 125 Thái Thịnh</p>
                </div>
                <a href="{{ route('appointments.index') }}" class="ts-btn-secondary">
                    <span>Lịch hẹn của tôi</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Alert Messages --}}
    <div class="row justify-content-center">
        <div class="col-lg-10">
            @if ($errors->any())
                <div class="p-4 mb-4" style="background-color: var(--status-danger-bg); color: var(--status-danger-ink); border-radius: var(--radius);" role="alert">
                    <strong>Vui lòng kiểm tra lại thông tin:</strong>
                    <ul class="mb-0 mt-2 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('error'))
                <div class="p-4 mb-4" style="background-color: var(--status-danger-bg); color: var(--status-danger-ink); border-radius: var(--radius);" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            @if (session('success'))
                <div class="p-4 mb-4" style="background-color: var(--status-ok-bg); color: var(--status-ok-ink); border-radius: var(--radius);" role="alert">
                    {{ session('success') }}
                </div>
            @endif
        </div>
    </div>

    {{-- Main Booking Container --}}
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="tiki-card p-0 overflow-hidden">
                {{-- Step Progress Header --}}
                <div class="p-4" style="background-color: var(--surface-soft);">
                    <div class="row text-center position-relative g-2">
                        <div class="col-3 step-item active" id="step-nav-1" role="button" tabindex="0" title="Bước 1: Chọn Bác sĩ & Chuyên khoa">
                            <div class="step-circle mx-auto mb-2">1</div>
                            <span class="step-title fw-semibold ts-small">Bác sĩ &amp; Khoa</span>
                        </div>
                        <div class="col-3 step-item" id="step-nav-2" role="button" tabindex="0" title="Bước 2: Chọn Ngày & Giờ khám">
                            <div class="step-circle mx-auto mb-2">2</div>
                            <span class="step-title fw-semibold ts-small">Ngày &amp; Giờ khám</span>
                        </div>
                        <div class="col-3 step-item" id="step-nav-3" role="button" tabindex="0" title="Bước 3: Chọn Dịch vụ khám">
                            <div class="step-circle mx-auto mb-2">3</div>
                            <span class="step-title fw-semibold ts-small">Dịch vụ khám</span>
                        </div>
                        <div class="col-3 step-item" id="step-nav-4" role="button" tabindex="0" title="Bước 4: Xác nhận lịch hẹn">
                            <div class="step-circle mx-auto mb-2">4</div>
                            <span class="step-title fw-semibold ts-small">Xác nhận lịch</span>
                        </div>
                    </div>

                    {{-- Thanh tóm tắt lựa chọn trực tiếp (Live Booking Context Strip) --}}
                    <div id="liveBookingSelectionStrip" class="mt-3 pt-3 d-flex flex-wrap align-items-center justify-content-between gap-2 ts-small" style="border-top: 1px solid var(--line); color: var(--muted);">
                        <div class="d-flex flex-wrap align-items-center gap-3">
                            <span><i class="fas fa-user-md me-1 text-primary"></i>Bác sĩ: <strong id="stripDocText" style="color: var(--ink);">Chưa chọn</strong></span>
                            <span><i class="far fa-clock me-1 text-primary"></i>Thời gian: <strong id="stripTimeText" style="color: var(--ink);">Chưa chọn</strong></span>
                            <span><i class="fas fa-stethoscope me-1 text-primary"></i>Phí khám: <strong id="stripPriceText" style="color: var(--brand);">--</strong></span>
                        </div>
                        <span class="badge bg-light text-secondary border">Bấm vào từng bước để quay lại nhanh</span>
                    </div>
                </div>

                {{-- Booking Form --}}
                <div class="p-4 p-md-5">
                    <form id="bookingForm" method="POST" action="{{ route('appointments.store') }}">
                        @csrf
                        @php
                            $selectedDepartmentId = $selectedDepartmentId ?? request('department_id');
                            $selectedDoctorId = $selectedDoctorId ?? request('doctor_id');
                            $selectedServiceId = $selectedServiceId ?? request('service_id');
                            $selectedDate = $selectedDate ?? request('date', now()->addDay()->format('Y-m-d'));
                        @endphp
                        <input type="hidden" name="idempotency_key" id="idempotency_key" value="{{ uniqid('appt_', true) }}">
                        <input type="hidden" name="time_slot_id" id="selected_time_slot_id" value="">
                        <input type="hidden" name="appointment_time" id="selected_appointment_time" value="">

                        {{-- ==================== BƯỚC 1: CHUYÊN KHOA & BÁC SĨ ==================== --}}
                        <div class="booking-step" id="step-1">
                            <h2 class="ts-feature-heading mb-4">
                                Bước 1 · Chọn đối tượng, chuyên khoa &amp; bác sĩ điều trị
                            </h2>

                            {{-- Chọn đối tượng khám: Bản thân hoặc Người thân --}}
                            <div class="p-4 mb-4" style="background-color: var(--bg); border-radius: var(--radius);">
                                <label class="form-label mb-3">
                                    Người khám bệnh là:
                                </label>
                                <div class="d-flex flex-wrap gap-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="booking_for" id="bookingForSelf" value="self" checked>
                                        <label class="form-check-label fw-semibold" for="bookingForSelf">
                                            Khám cho bản thân ({{ Auth::user()->name }})
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="booking_for" id="bookingForRelative" value="relative">
                                        <label class="form-check-label fw-semibold" for="bookingForRelative">
                                            Đặt cho người thân (Con nhỏ, Bố/Mẹ, Vợ/Chồng...)
                                        </label>
                                    </div>
                                </div>

                                {{-- Form nhập thông tin người thân --}}
                                <div id="relativeInfoBox" class="p-4 mt-3 d-none" style="background-color: var(--surface); border-radius: var(--radius);">
                                    @if(isset($savedRelatives) && $savedRelatives->isNotEmpty())
                                        <div class="mb-3 pb-3" style="border-bottom: 1px solid var(--line);">
                                            <div class="ts-small fw-semibold mb-2" style="color: var(--muted);">
                                                <i class="fas fa-history me-1"></i>Chọn nhanh hồ sơ người thân đã lưu:
                                            </div>
                                            <div class="d-flex flex-wrap gap-2">
                                                @foreach($savedRelatives as $rel)
                                                    <button type="button"
                                                            class="ts-spec-btn saved-relative-pill"
                                                            onclick="document.getElementById('relative_name').value={{ json_encode($rel->relative_name) }}; document.getElementById('relative_relationship').value={{ json_encode($rel->relative_relationship ?: 'Con nhỏ') }}; document.getElementById('relative_dob').value={{ json_encode($rel->relative_dob ? \Carbon\Carbon::parse($rel->relative_dob)->format('Y-m-d') : '') }}; document.getElementById('relative_gender').value={{ json_encode($rel->relative_gender ?: 'male') }};">
                                                        <i class="fas fa-user-check me-1"></i>
                                                        <span>{{ $rel->relative_name }} ({{ $rel->relative_relationship ?: 'Người thân' }})</span>
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Họ tên người khám *</label>
                                            <input type="text" class="form-control" name="relative_name" id="relative_name" placeholder="Ví dụ: Nguyễn Minh Khang">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Mối quan hệ *</label>
                                            <select class="form-select" name="relative_relationship" id="relative_relationship">
                                                <option value="Con nhỏ">Con nhỏ</option>
                                                <option value="Bố/Mẹ">Bố / Mẹ</option>
                                                <option value="Vợ/Chồng">Vợ / Chồng</option>
                                                <option value="Người thân khác">Người thân khác</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Ngày / Năm sinh</label>
                                            <input type="date" class="form-control" name="relative_dob" id="relative_dob">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Giới tính</label>
                                            <select class="form-select" name="relative_gender" id="relative_gender">
                                                <option value="male">Nam</option>
                                                <option value="female">Nữ</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="ts-small mt-2" style="color: var(--muted);">
                                        Hồ sơ bệnh án và lịch sử khám sẽ được phân loại riêng theo tên người thân.
                                    </div>
                                </div>
                            </div>

                            <div class="row g-4">
                                {{-- Chọn chuyên khoa --}}
                                <div class="col-md-6">
                                    <label for="department_id" class="form-label">
                                        Chuyên khoa *
                                    </label>
                                    <select class="form-select" id="department_id" name="department_id" required>
                                        <option value="">-- Chọn chuyên khoa --</option>
                                        @foreach($departments as $dept)
                                            <option value="{{ $dept->id }}" {{ (old('department_id', $selectedDepartmentId) == $dept->id) ? 'selected' : '' }}>
                                                {{ $dept->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="ts-small mt-1" style="color: var(--muted);">Lọc danh sách bác sĩ chuyên khoa tương ứng</div>
                                </div>

                                {{-- Chọn bác sĩ --}}
                                <div class="col-md-6">
                                    <label for="doctor_id" class="form-label">
                                        Bác sĩ phụ trách *
                                    </label>
                                    <select class="form-select" id="doctor_id" name="doctor_id" required>
                                        <option value="">-- Vui lòng chọn khoa trước --</option>
                                        @foreach($doctors as $doc)
                                            <option value="{{ $doc->id }}"
                                                    data-dept="{{ $doc->department_id }}"
                                                    data-name="{{ $doc->user->name ?? 'Bác sĩ' }}"
                                                    data-spec="{{ $doc->specialization ?? '' }}"
                                                    data-avatar="{{ $doc->avatar ? asset('storage/' . $doc->avatar) : '' }}"
                                                    {{ (old('doctor_id', $selectedDoctorId) == $doc->id) ? 'selected' : '' }}>
                                                {{ $doc->user->name ?? 'Bác sĩ' }} ({{ $doc->department->name ?? 'Chuyên khoa' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Doctor Preview Card --}}
                            <div class="mt-4 p-4 d-none" style="background-color: var(--surface-soft); border-radius: var(--radius);" id="doctorPreviewCard">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 56px; height: 56px; background-color: var(--accent); color: var(--accent-ink); font-size: 1.25rem;" id="docAvatarWrapper">
                                        <i class="fas fa-user-md"></i>
                                    </div>
                                    <div>
                                        <h3 class="ts-feature-heading mb-1" id="docPreviewName">Bác sĩ</h3>
                                        <p class="mb-0 ts-small" style="color: var(--muted);" id="docPreviewSpec">Chuyên môn</p>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <button type="button" class="ts-btn" id="btnNext1">
                                    <span>Tiếp tục · Chọn ngày giờ →</span>
                                </button>
                            </div>
                        </div>

                        {{-- ==================== BƯỚC 2: NGÀY KHÁM & KHUNG GIỜ ==================== --}}
                        <div class="booking-step d-none" id="step-2">
                            <h2 class="ts-feature-heading mb-3">
                                Bước 2 · Chọn ngày khám &amp; khung giờ trống
                            </h2>

                            {{-- Quick 7-Day Date Selector Strip --}}
                            <div class="mb-4">
                                <p class="ts-small fw-semibold mb-2" style="color: var(--muted);">
                                    Chọn nhanh ngày khám trong 7 ngày tới:
                                </p>
                                <div class="d-flex flex-wrap gap-2" id="quickDateStrip">
                                    @php
                                        $dayNames = [0 => 'CN', 1 => 'Thứ 2', 2 => 'Thứ 3', 3 => 'Thứ 4', 4 => 'Thứ 5', 5 => 'Thứ 6', 6 => 'Thứ 7'];
                                    @endphp
                                    @for($i = 0; $i < 7; $i++)
                                        @php
                                            $d = now()->addDays($i);
                                            $dVal = $d->format('Y-m-d');
                                            $dLabel = $i === 0 ? 'Hôm nay' : ($i === 1 ? 'Ngày mai' : $dayNames[$d->dayOfWeek]);
                                            $isSelectedDate = (old('appointment_date', $selectedDate) === $dVal);
                                        @endphp
                                        <button type="button"
                                                class="ts-spec-btn quick-date-pill {{ $isSelectedDate ? 'is-active' : '' }}"
                                                data-date="{{ $dVal }}">
                                            <span class="fw-semibold">{{ $dLabel }}</span>
                                            <span class="ts-num ms-1">({{ $d->format('d/m') }})</span>
                                        </button>
                                    @endfor
                                </div>
                            </div>

                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <label for="appointment_date" class="form-label">
                                        Hoặc chọn ngày cụ thể *
                                    </label>
                                    <input type="date"
                                           class="form-control"
                                           id="appointment_date"
                                           name="appointment_date"
                                           min="{{ now()->format('Y-m-d') }}"
                                           max="{{ now()->addDays(14)->format('Y-m-d') }}"
                                           value="{{ old('appointment_date', $selectedDate) }}"
                                           required>
                                    <div class="ts-small mt-1" style="color: var(--muted);">Bạn có thể đặt trước tối đa 14 ngày</div>
                                </div>
                                <div class="col-md-6 d-flex align-items-end">
                                    <button type="button" class="ts-btn-secondary w-100" id="btnReloadSlots">
                                        <span>Tải lại khung giờ trống</span>
                                    </button>
                                </div>
                            </div>

                            {{-- Time Slot Selection Container --}}
                            <div class="mb-4">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <span class="fw-semibold">Khung giờ khám còn khả dụng:</span>
                                    <span class="ts-small" style="color: var(--muted);" id="slotLoadStatus">Đang tải...</span>
                                </div>

                                {{-- Loading Spinner --}}
                                <div id="slotsSpinner" class="text-center py-4 d-none">
                                    <p class="ts-small mt-2" style="color: var(--muted);">Đang truy vấn lịch làm việc của bác sĩ...</p>
                                </div>

                                {{-- Slots Grid --}}
                                <div class="row g-3" id="slotsGrid">
                                    {{-- Rendered dynamically by JavaScript --}}
                                </div>

                                {{-- Fallback Shifts (Morning / Afternoon) if no specific slots --}}
                                <div id="legacyShiftContainer" class="p-4 mt-3 d-none" style="background-color: var(--bg); border-radius: var(--radius);">
                                    <p class="fw-semibold mb-3">Chọn theo ca khám tổng hợp:</p>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="form-check p-3" style="background-color: var(--surface); border-radius: var(--radius);">
                                                <input class="form-check-input" type="radio" name="medical_examination" id="shiftMorning" value="Ca sáng (07:30 - 11:30)">
                                                <label class="form-check-label fw-semibold" for="shiftMorning">
                                                    Ca sáng (07:30 - 11:30)
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check p-3" style="background-color: var(--surface); border-radius: var(--radius);">
                                                <input class="form-check-input" type="radio" name="medical_examination" id="shiftAfternoon" value="Ca chiều (13:00 - 17:00)">
                                                <label class="form-check-label fw-semibold" for="shiftAfternoon">
                                                    Ca chiều (13:00 - 17:00)
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="ts-btn-secondary" id="btnPrev2">
                                    <span>← Quay lại</span>
                                </button>
                                <button type="button" class="ts-btn" id="btnNext2">
                                    <span>Tiếp tục · Chọn dịch vụ →</span>
                                </button>
                            </div>
                        </div>

                        {{-- ==================== BƯỚC 3: DỊCH VỤ & GHI CHÚ ==================== --}}
                        <div class="booking-step d-none" id="step-3">
                            <h2 class="ts-feature-heading mb-4">
                                Bước 3 · Chọn dịch vụ y tế &amp; nhập triệu chứng
                            </h2>

                            <div class="mb-4">
                                <label for="service_id" class="form-label">
                                    Gói dịch vụ khám *
                                </label>
                                <select class="form-select" id="service_id" name="service_id" required>
                                    <option value="">-- Chọn dịch vụ khám --</option>
                                    @foreach($services as $srv)
                                        <option value="{{ $srv->id }}"
                                                data-dept="{{ $srv->department_id }}"
                                                data-price="{{ $srv->price }}"
                                                data-formatted-price="{{ number_format($srv->price, 0, ',', '.') }} đ"
                                                data-desc="{{ $srv->description ?? '' }}"
                                                {{ (old('service_id', $selectedServiceId) == $srv->id) ? 'selected' : '' }}>
                                            {{ $srv->name }} · {{ number_format($srv->price, 0, ',', '.') }} đ
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Service Description Preview --}}
                            <div class="p-4 mb-4 d-none" style="background-color: var(--surface-soft); border-radius: var(--radius);" id="serviceDescCard">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-semibold" style="color: var(--accent);">Thông tin gói khám</span>
                                    <span class="ts-pass-price" id="servicePriceText">0 đ</span>
                                </div>
                                <p class="mb-0 ts-small" style="color: var(--muted);" id="serviceDescText">Mô tả dịch vụ</p>
                            </div>

                            <div class="mb-4">
                                <label for="note" class="form-label">
                                    Triệu chứng / Lý do khám / Ghi chú cho bác sĩ:
                                </label>
                                <textarea class="form-control"
                                          id="note"
                                          name="note"
                                          rows="3"
                                          placeholder="Mô tả ngắn gọn triệu chứng bạn đang gặp phải (ví dụ: sốt, ho, đau đầu 2 ngày nay...)...">{{ old('note', request('note')) }}</textarea>
                                <div class="mt-3 d-flex flex-wrap align-items-center gap-2">
                                    <span class="ts-small" style="color: var(--muted);">Chọn nhanh triệu chứng:</span>
                                    @foreach(['Sốt, mệt mỏi', 'Ho, đau rát họng', 'Đau đầu, chóng mặt', 'Đau dạ dày, ợ chua', 'Đau nhức xương khớp', 'Khám sức khỏe tổng quát', 'Tái khám theo hẹn'] as $sym)
                                        <button type="button" class="ts-spec-btn quick-symptom-btn" data-symptom="{{ $sym }}">
                                            + {{ $sym }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="ts-btn-secondary" id="btnPrev3">
                                    <span>← Quay lại</span>
                                </button>
                                <button type="button" class="ts-btn" id="btnNext3">
                                    <span>Tiếp tục · Xác nhận lịch →</span>
                                </button>
                            </div>
                        </div>

                        {{-- ==================== BƯỚC 4: TÓM TẮT & XÁC NHẬN ==================== --}}
                        <div class="booking-step d-none" id="step-4">
                            <h2 class="ts-feature-heading mb-4">
                                Bước 4 · Kiểm tra &amp; xác nhận phiếu hẹn khám
                            </h2>

                            <div class="p-4 mb-4" style="background-color: var(--surface-soft); border-radius: var(--radius);">
                                <h3 class="ts-feature-heading mb-3" style="color: var(--accent);">
                                    Tóm tắt phiếu đăng ký khám bệnh
                                </h3>
                                <div class="row g-3">
                                    <div class="col-sm-6">
                                        <span class="ts-small d-block" style="color: var(--muted);">Bệnh nhân thực tế khám:</span>
                                        <strong id="summaryPatientName">{{ Auth::user()->name ?? 'Bệnh nhân' }}</strong>
                                    </div>
                                    <div class="col-sm-6">
                                        <span class="ts-small d-block" style="color: var(--muted);">Tài khoản &amp; SĐT đặt lịch:</span>
                                        <strong class="ts-num">{{ Auth::user()->name }} ({{ Auth::user()->phone ?? 'Chưa cập nhật' }})</strong>
                                    </div>
                                    <div class="col-sm-6">
                                        <span class="ts-small d-block" style="color: var(--muted);">Chuyên khoa:</span>
                                        <strong id="summaryDept">-</strong>
                                    </div>
                                    <div class="col-sm-6">
                                        <span class="ts-small d-block" style="color: var(--muted);">Bác sĩ khám:</span>
                                        <strong style="color: var(--accent);" id="summaryDoctor">-</strong>
                                    </div>
                                    <div class="col-sm-6">
                                        <span class="ts-small d-block" style="color: var(--muted);">Ngày khám:</span>
                                        <strong class="ts-num" id="summaryDate">-</strong>
                                    </div>
                                    <div class="col-sm-6">
                                        <span class="ts-small d-block" style="color: var(--muted);">Khung giờ tiếp nhận:</span>
                                        <strong class="ts-num" style="color: var(--status-ok-ink);" id="summaryTime">-</strong>
                                    </div>
                                    <div class="col-sm-6">
                                        <span class="ts-small d-block" style="color: var(--muted);">Gói dịch vụ:</span>
                                        <strong id="summaryService">-</strong>
                                    </div>
                                    <div class="col-sm-6">
                                        <span class="ts-small d-block" style="color: var(--muted);">Chi phí niêm yết:</span>
                                        <strong class="ts-pass-price" id="summaryPrice">0 đ</strong>
                                    </div>
                                    <div class="col-12" id="summaryNoteWrapper">
                                        <span class="ts-small d-block" style="color: var(--muted);">Ghi chú triệu chứng:</span>
                                        <span id="summaryNote">Không có</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Chọn hình thức thanh toán --}}
                            <div class="p-4 mb-4" style="background-color: var(--bg); border-radius: var(--radius);">
                                <h3 class="ts-feature-heading mb-3">
                                    Phương thức thanh toán viện phí
                                </h3>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="form-check p-3 h-100" style="background-color: var(--surface); border-radius: var(--radius);">
                                            <input class="form-check-input ms-0 me-2" type="radio" name="payment_method_choice" id="payAtClinic" value="counter" checked>
                                            <label class="form-check-label fw-semibold" for="payAtClinic">
                                                Thanh toán tại Quầy tiếp đón
                                                <span class="d-block ts-small fw-normal mt-1" style="color: var(--muted);">Trả bằng tiền mặt, quẹt thẻ POS hoặc xuất trình thẻ BHYT khi tới phòng khám.</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check p-3 h-100" style="background-color: var(--surface); border-radius: var(--radius);">
                                            <input class="form-check-input ms-0 me-2" type="radio" name="payment_method_choice" id="payVnpay" value="vnpay">
                                            <label class="form-check-label fw-semibold" for="payVnpay">
                                                Thanh toán trực tuyến VNPAY
                                                <span class="d-block ts-small fw-normal mt-1" style="color: var(--muted);">Chuyển hướng cổng VNPAY (Thẻ ATM, Internet Banking, Quét mã QR Pay).</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="p-4 mb-4" style="background-color: var(--surface-soft); border-radius: var(--radius);">
                                <p class="ts-small mb-0">
                                    Lịch hẹn được gửi trực tiếp đến hệ thống Phòng khám 125 Thái Thịnh và giữ chỗ ngay lập tức trong khung giờ bạn đã chọn. Vui lòng đến trước 10 phút để quét mã QR đón tiếp.
                                </p>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="ts-btn-secondary" id="btnPrev4">
                                    <span>← Quay lại chỉnh sửa</span>
                                </button>
                                <button type="submit" class="ts-btn" id="btnSubmitBooking">
                                    <span>Xác nhận đặt lịch ngay</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.step-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: var(--surface-soft);
    color: var(--ink);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    transition: background-color 0.25s ease, color 0.25s ease;
}
.step-item.active .step-circle {
    background-color: var(--accent);
    color: var(--accent-ink);
}
.step-item.completed .step-circle {
    background-color: var(--status-ok-bg);
    color: var(--status-ok-ink);
}
.slot-card {
    cursor: pointer;
    background-color: var(--surface-soft);
    border-radius: var(--radius);
    transition: background-color 0.2s ease, transform 0.2s var(--ease-out);
}
.slot-card:hover:not(.disabled) {
    background-color: var(--accent-tint);
    transform: translateY(-2px);
}
.slot-card.selected {
    background-color: var(--accent);
    color: var(--accent-ink);
}
.slot-card.disabled {
    cursor: not-allowed;
    opacity: 0.55;
    background-color: var(--bg);
}
.skeleton-slot {
    height: 76px;
    background-color: var(--surface-soft);
    border-radius: var(--radius);
}
.slot-section-title {
    font-size: var(--step-body);
    font-weight: 700;
    color: var(--ink);
    padding-bottom: 0.5rem;
    margin-top: 1rem;
    margin-bottom: 0.75rem;
}
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    let currentStep = 1;
    const deptSelect = document.getElementById('department_id');
    const docSelect = document.getElementById('doctor_id');
    const srvSelect = document.getElementById('service_id');
    const dateInput = document.getElementById('appointment_date');
    const slotsGrid = document.getElementById('slotsGrid');
    const slotsSpinner = document.getElementById('slotsSpinner');
    const slotLoadStatus = document.getElementById('slotLoadStatus');
    const legacyShiftContainer = document.getElementById('legacyShiftContainer');
    const selectedSlotInput = document.getElementById('selected_time_slot_id');
    const selectedApptTimeInput = document.getElementById('selected_appointment_time');

    // Toggle Relative Booking Info
    const bookingForSelf = document.getElementById('bookingForSelf');
    const bookingForRelative = document.getElementById('bookingForRelative');
    const relativeInfoBox = document.getElementById('relativeInfoBox');
    const relativeNameInput = document.getElementById('relative_name');

    function toggleRelativeBox() {
        if (bookingForRelative && bookingForRelative.checked) {
            relativeInfoBox.classList.remove('d-none');
            relativeNameInput.setAttribute('required', 'required');
        } else if (relativeInfoBox) {
            relativeInfoBox.classList.add('d-none');
            relativeNameInput.removeAttribute('required');
        }
    }

    if (bookingForSelf && bookingForRelative) {
        bookingForSelf.addEventListener('change', toggleRelativeBox);
        bookingForRelative.addEventListener('change', toggleRelativeBox);
    }

    // Filter doctors by selected department
    function filterDoctors() {
        const deptId = deptSelect.value;
        const options = docSelect.querySelectorAll('option');
        let hasSelected = false;

        options.forEach(opt => {
            if (!opt.value) return;
            const docDept = opt.getAttribute('data-dept');
            if (!deptId || docDept === deptId) {
                opt.style.display = '';
                if (!hasSelected && docSelect.value === opt.value) {
                    hasSelected = true;
                }
            } else {
                opt.style.display = 'none';
            }
        });

        if (!hasSelected && deptId) {
            const firstVisible = Array.from(options).find(opt => opt.value && opt.style.display !== 'none');
            if (firstVisible) {
                docSelect.value = firstVisible.value;
            } else {
                docSelect.value = '';
            }
        }
        updateDoctorPreview();
    }

    // Filter services by selected department
    function filterServices() {
        const deptId = deptSelect.value;
        const options = srvSelect.querySelectorAll('option');
        options.forEach(opt => {
            if (!opt.value) return;
            const srvDept = opt.getAttribute('data-dept');
            if (!deptId || srvDept === deptId) {
                opt.style.display = '';
            } else {
                opt.style.display = 'none';
            }
        });
        updateServicePreview();
    }

    function updateDoctorPreview() {
        const card = document.getElementById('doctorPreviewCard');
        const avatarWrap = document.getElementById('docAvatarWrapper');
        const opt = docSelect.options[docSelect.selectedIndex];
        if (opt && opt.value) {
            document.getElementById('docPreviewName').textContent = opt.getAttribute('data-name');
            document.getElementById('docPreviewSpec').textContent = opt.getAttribute('data-spec') || 'Bác sĩ Chuyên khoa - Phòng khám Đa khoa Thái Thịnh';
            const avatarUrl = opt.getAttribute('data-avatar');
            if (avatarWrap) {
                if (avatarUrl) {
                    avatarWrap.innerHTML = `<img src="${avatarUrl}" alt="Avatar" class="rounded-circle w-100 h-100 object-fit-cover border border-2 border-white">`;
                } else {
                    avatarWrap.innerHTML = `<i class="fas fa-user-md"></i>`;
                }
            }
            card.classList.remove('d-none');
        } else {
            card.classList.add('d-none');
        }
    }

    function updateServicePreview() {
        const card = document.getElementById('serviceDescCard');
        const opt = srvSelect.options[srvSelect.selectedIndex];
        if (opt && opt.value) {
            document.getElementById('servicePriceText').textContent = opt.getAttribute('data-formatted-price');
            document.getElementById('serviceDescText').textContent = opt.getAttribute('data-desc') || 'Khám chẩn đoán chuyên khoa toàn diện';
            card.classList.remove('d-none');
        } else {
            card.classList.add('d-none');
        }
    }

    deptSelect.addEventListener('change', () => {
        filterDoctors();
        filterServices();
    });

    docSelect.addEventListener('change', updateDoctorPreview);
    srvSelect.addEventListener('change', updateServicePreview);

    filterDoctors();
    filterServices();

    // Render single slot element
    function createSlotCard(slot) {
        const col = document.createElement('div');
        col.className = 'col-6 col-md-4 col-lg-3';

        const isAvail = slot.is_available;
        let badgeClass = 'bg-secondary';
        let badgeText = slot.reason;
        let borderClass = '';

        if (isAvail) {
            if (slot.remaining === 1) {
                badgeClass = 'tiki-status-badge pending';
                badgeText = 'Còn 1 chỗ';
            } else {
                badgeClass = 'tiki-status-badge completed';
                badgeText = `Còn ${slot.remaining} chỗ`;
            }
        }

        col.innerHTML = `
            <div class="p-3 slot-card ${isAvail ? '' : 'disabled'}" data-slot-id="${slot.id}" data-time="${slot.formatted_time}">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="fw-bold ts-num">${slot.formatted_time}</span>
                    <span class="${badgeClass}">${badgeText}</span>
                </div>
                <span class="ts-small" style="color: var(--muted);">${isAvail ? 'Có thể đăng ký' : 'Đã kín lịch'}</span>
            </div>
        `;

        if (isAvail) {
            col.querySelector('.slot-card').addEventListener('click', function() {
                document.querySelectorAll('.slot-card').forEach(c => c.classList.remove('selected'));
                this.classList.add('selected');
                selectedSlotInput.value = slot.id;
                selectedApptTimeInput.value = slot.formatted_time;
            });
        }

        return col;
    }

    // Fetch time slots from API with Skeleton Loading & Shift Grouping (UI/UX Improvements)
    function fetchTimeSlots() {
        const doctorId = docSelect.value;
        const date = dateInput.value;

        if (!doctorId || !date) {
            slotsGrid.innerHTML = '<div class="col-12 text-center text-muted py-3">Vui lòng chọn bác sĩ và ngày khám để xem khung giờ.</div>';
            return;
        }

        // Hiển thị Skeleton Loading (Giảm độ trễ nhận thức)
        slotsGrid.innerHTML = `
            <div class="col-6 col-md-3"><div class="skeleton-slot"></div></div>
            <div class="col-6 col-md-3"><div class="skeleton-slot"></div></div>
            <div class="col-6 col-md-3"><div class="skeleton-slot"></div></div>
            <div class="col-6 col-md-3"><div class="skeleton-slot"></div></div>
        `;
        slotLoadStatus.textContent = 'Đang truy vấn lịch làm việc...';

        fetch(`/appointments/time-slots?doctor_id=${doctorId}&date=${date}`)
            .then(res => res.json())
            .then(data => {
                slotsGrid.innerHTML = '';

                const renderNextAvailableSuggestions = (nextDates) => {
                    if (!nextDates || !nextDates.length) return '';
                    let pillsHtml = nextDates.map(d => `
                        <button type="button" class="ts-filter-pill py-1 px-3 js-suggest-date-btn ts-num" data-date="${d.date}" style="background: #fff;">
                            <i class="far fa-calendar-check text-primary me-1"></i>
                            <strong>${d.formatted}</strong> (Còn ${d.remaining_slots} ca trống)
                        </button>
                    `).join('');
                    return `
                        <div class="col-12 mb-3">
                            <div class="p-3 rounded-3" style="background-color: #EFF6FF; border: 1px solid #BFDBFE;">
                                <div class="fw-bold small mb-2" style="color: #1E40AF;">
                                    <i class="fas fa-magic me-1"></i>Gợi ý thông minh — Các ngày gần nhất Bác sĩ có lịch trống:
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    ${pillsHtml}
                                </div>
                            </div>
                        </div>
                    `;
                };

                if (data.slots && data.slots.length > 0) {
                    const availableCount = data.slots.filter(s => s.is_available).length;
                    slotLoadStatus.textContent = `Tìm thấy ${data.slots.length} khung giờ (${availableCount} ca còn chỗ)`;
                    legacyShiftContainer.classList.add('d-none');

                    if (availableCount === 0 && data.next_available_dates && data.next_available_dates.length > 0) {
                        slotsGrid.insertAdjacentHTML('beforeend', renderNextAvailableSuggestions(data.next_available_dates));
                    }

                    const morningSlots = data.slots.filter(s => parseInt(s.start_time.split(':')[0]) < 12);
                    const afternoonSlots = data.slots.filter(s => parseInt(s.start_time.split(':')[0]) >= 12);

                    // Phân nhóm Ca Sáng
                    if (morningSlots.length > 0) {
                        const mTitle = document.createElement('div');
                        mTitle.className = 'col-12 slot-section-title text-primary';
                        mTitle.innerHTML = '<i class="fas fa-sun text-warning me-2"></i>Ca Sáng (07:30 - 11:30)';
                        slotsGrid.appendChild(mTitle);

                        morningSlots.forEach(slot => {
                            slotsGrid.appendChild(createSlotCard(slot));
                        });
                    }

                    // Phân nhóm Ca Chiều
                    if (afternoonSlots.length > 0) {
                        const aTitle = document.createElement('div');
                        aTitle.className = 'col-12 slot-section-title text-primary';
                        aTitle.innerHTML = '<i class="fas fa-cloud-sun text-info me-2"></i>Ca Chiều (13:00 - 17:00)';
                        slotsGrid.appendChild(aTitle);

                        afternoonSlots.forEach(slot => {
                            slotsGrid.appendChild(createSlotCard(slot));
                        });
                    }

                    // Tự động chọn khung giờ nếu truyền từ trang chủ (?time=08:00)
                    const urlTime = new URLSearchParams(window.location.search).get('time');
                    if (urlTime && !selectedSlotInput.value) {
                        const matchingCard = Array.from(slotsGrid.querySelectorAll('.slot-card:not(.disabled)')).find(c => {
                            return (c.getAttribute('data-time') || '').startsWith(urlTime);
                        });
                        if (matchingCard) {
                            matchingCard.click();
                        }
                    }
                } else {
                    slotLoadStatus.textContent = 'Chưa có khung giờ chi tiết';
                    const nextDatesHtml = renderNextAvailableSuggestions(data.next_available_dates || []);
                    slotsGrid.innerHTML = `
                        ${nextDatesHtml}
                        <div class="col-12">
                            <div class="alert alert-warning border-0 rounded-4">
                                <i class="fas fa-info-circle me-2"></i>Bác sĩ chưa có khung giờ 30 phút trong ngày này. Bạn có thể bấm chọn <strong>ngày có lịch gần nhất</strong> ở trên hoặc đăng ký theo buổi khám bên dưới:
                            </div>
                        </div>
                    `;
                    legacyShiftContainer.classList.remove('d-none');
                }

                slotsGrid.querySelectorAll('.js-suggest-date-btn').forEach(btn => {
                    btn.addEventListener('click', function () {
                        const targetDate = this.getAttribute('data-date');
                        if (targetDate) {
                            dateInput.value = targetDate;
                            syncQuickDatePills(targetDate);
                            fetchTimeSlots();
                        }
                    });
                });
            })
            .catch(err => {
                slotsGrid.innerHTML = '';
                slotLoadStatus.textContent = 'Không thể tải khung giờ';
                legacyShiftContainer.classList.remove('d-none');
            });
    }

    document.getElementById('btnReloadSlots').addEventListener('click', fetchTimeSlots);
    dateInput.addEventListener('change', function() {
        syncQuickDatePills(this.value);
        fetchTimeSlots();
    });

    // Quick 7-day date pills click handler
    function syncQuickDatePills(selectedVal) {
        document.querySelectorAll('.quick-date-pill').forEach(btn => {
            const isMatch = btn.getAttribute('data-date') === selectedVal;
            btn.classList.toggle('is-active', isMatch);
        });
    }

    document.querySelectorAll('.quick-date-pill').forEach(btn => {
        btn.addEventListener('click', function() {
            const chosenDate = this.getAttribute('data-date');
            dateInput.value = chosenDate;
            syncQuickDatePills(chosenDate);
            fetchTimeSlots();
        });
    });

    // Quick symptom chips click handler
    document.querySelectorAll('.quick-symptom-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const noteEl = document.getElementById('note');
            const sym = this.getAttribute('data-symptom');
            const current = noteEl.value.trim();
            if (!current) {
                noteEl.value = sym;
            } else if (!current.toLowerCase().includes(sym.toLowerCase())) {
                noteEl.value = current + ', ' + sym;
            }
            noteEl.focus();
        });
    });

    // Step Navigation
    function showStep(step) {
        document.querySelectorAll('.booking-step').forEach(el => el.classList.add('d-none'));
        document.getElementById(`step-${step}`).classList.remove('d-none');

        document.querySelectorAll('.step-item').forEach((el, idx) => {
            if (idx + 1 < step) {
                el.classList.add('completed');
                el.classList.remove('active');
                el.style.cursor = 'pointer';
            } else if (idx + 1 === step) {
                el.classList.add('active');
                el.classList.remove('completed');
                el.style.cursor = 'default';
            } else {
                el.classList.remove('active', 'completed');
                el.style.cursor = 'default';
            }
        });

        currentStep = step;
        window.scrollTo({ top: 150, behavior: 'smooth' });
    }

    // Allow clicking step circles to jump back or jump forward if prerequisites are met
    function updateLiveStrip() {
        const stripDoc = document.getElementById('stripDocText');
        const stripTime = document.getElementById('stripTimeText');
        const stripPrice = document.getElementById('stripPriceText');
        if (stripDoc && docSelect.selectedIndex >= 0) {
            const opt = docSelect.options[docSelect.selectedIndex];
            stripDoc.textContent = (opt && opt.value) ? (opt.getAttribute('data-name') || opt.text) : 'Chưa chọn';
        }
        if (stripTime) {
            const dVal = dateInput ? dateInput.value : '';
            const tVal = selectedApptTimeInput ? selectedApptTimeInput.value : '';
            stripTime.textContent = (dVal && tVal) ? `${tVal} (${dVal})` : (dVal ? `Ngày ${dVal}` : 'Chưa chọn');
        }
        if (stripPrice && srvSelect.selectedIndex >= 0) {
            const sOpt = srvSelect.options[srvSelect.selectedIndex];
            stripPrice.textContent = (sOpt && sOpt.value) ? (sOpt.getAttribute('data-formatted-price') || '--') : '--';
        }
    }

    docSelect.addEventListener('change', updateLiveStrip);
    srvSelect.addEventListener('change', updateLiveStrip);
    dateInput.addEventListener('change', updateLiveStrip);
    updateLiveStrip();

    document.querySelectorAll('.step-item').forEach((el, idx) => {
        el.addEventListener('click', function() {
            const target = idx + 1;
            if (target < currentStep) {
                showStep(target);
                updateLiveStrip();
            } else if (target === 2 && deptSelect.value && docSelect.value) {
                showStep(2);
                fetchTimeSlots();
                updateLiveStrip();
            } else if (target === 3 && deptSelect.value && docSelect.value && (selectedSlotInput.value || document.querySelector('input[name="medical_examination"]:checked'))) {
                showStep(3);
                updateLiveStrip();
            }
        });
    });

    function showStepNotice(msg) {
        let notice = document.getElementById('stepNoticeToast');
        if (!notice) {
            notice = document.createElement('div');
            notice.id = 'stepNoticeToast';
            notice.className = 'alert alert-warning border-0 rounded-4 shadow-lg mb-3 d-flex align-items-center position-fixed start-50 translate-middle-x px-4 py-3';
            notice.style.zIndex = '99999';
            notice.style.top = '20px';
            notice.style.maxWidth = '90%';
            notice.style.animation = 'fadeIn 0.3s ease';
            document.body.appendChild(notice);
        }
        notice.innerHTML = `<i class="fas fa-exclamation-circle text-warning fs-5 me-2"></i> <span class="fw-semibold text-dark">${msg}</span>`;
        notice.classList.remove('d-none');
        setTimeout(() => {
            if (notice) notice.classList.add('d-none');
        }, 3500);
    }

    // Next 1 -> 2
    document.getElementById('btnNext1').addEventListener('click', function () {
        if (bookingForRelative && bookingForRelative.checked) {
            const relName = document.getElementById('relative_name').value.trim();
            if (!relName) {
                showStepNotice('Vui lòng nhập họ và tên của người thân cần khám bệnh.');
                document.getElementById('relative_name').focus();
                return;
            }
        }
        if (!deptSelect.value) {
            showStepNotice('Vui lòng chọn chuyên khoa khám trước khi tiếp tục.');
            deptSelect.focus();
            return;
        }
        if (!docSelect.value) {
            showStepNotice('Vui lòng chọn bác sĩ phụ trách.');
            docSelect.focus();
            return;
        }
        showStep(2);
        fetchTimeSlots();
    });

    // Prev 2 -> 1
    document.getElementById('btnPrev2').addEventListener('click', () => showStep(1));

    // Next 2 -> 3
    document.getElementById('btnNext2').addEventListener('click', function () {
        if (!dateInput.value) {
            showStepNotice('Vui lòng chọn ngày khám bệnh.');
            dateInput.focus();
            return;
        }

        const hasSlot = selectedSlotInput.value;
        const hasLegacyShift = document.querySelector('input[name="medical_examination"]:checked');

        if (!hasSlot && !hasLegacyShift) {
            showStepNotice('Vui lòng chọn một khung giờ khám còn trống.');
            return;
        }

        showStep(3);
    });

    // Prev 3 -> 2
    document.getElementById('btnPrev3').addEventListener('click', () => showStep(2));

    // Next 3 -> 4 (Summary)
    document.getElementById('btnNext3').addEventListener('click', function () {
        if (!srvSelect.value) {
            showStepNotice('Vui lòng chọn dịch vụ khám chuyên khoa.');
            srvSelect.focus();
            return;
        }

        // Fill Summary
        if (bookingForRelative && bookingForRelative.checked) {
            const relName = document.getElementById('relative_name').value.trim();
            const relShip = document.getElementById('relative_relationship').value;
            document.getElementById('summaryPatientName').textContent = (relName ? relName : 'Người thân') + ' (' + relShip + ')';
        } else {
            document.getElementById('summaryPatientName').textContent = '{{ Auth::user()->name }} (Bản thân)';
        }

        document.getElementById('summaryDept').textContent = deptSelect.options[deptSelect.selectedIndex].text;
        document.getElementById('summaryDoctor').textContent = docSelect.options[docSelect.selectedIndex].getAttribute('data-name');
        const rawDate = dateInput.value;
        if (rawDate && rawDate.includes('-')) {
            const parts = rawDate.split('-');
            document.getElementById('summaryDate').textContent = `${parts[2]}/${parts[1]}/${parts[0]}`;
        } else {
            document.getElementById('summaryDate').textContent = rawDate;
        }

        let timeText = 'Ca khám';
        if (selectedSlotInput.value) {
            timeText = selectedApptTimeInput.value;
        } else {
            const shift = document.querySelector('input[name="medical_examination"]:checked');
            if (shift) timeText = shift.value;
        }
        document.getElementById('summaryTime').textContent = timeText;

        const srvOpt = srvSelect.options[srvSelect.selectedIndex];
        document.getElementById('summaryService').textContent = srvOpt.text;
        document.getElementById('summaryPrice').textContent = srvOpt.getAttribute('data-formatted-price');

        const noteVal = document.getElementById('note').value.trim();
        document.getElementById('summaryNote').textContent = noteVal ? noteVal : 'Không có ghi chú';

        showStep(4);
    });

    // Prev 4 -> 3
    document.getElementById('btnPrev4').addEventListener('click', () => showStep(3));

    // Prevent double form submission
    document.getElementById('bookingForm').addEventListener('submit', function () {
        const btn = document.getElementById('btnSubmitBooking');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang xử lý đặt lịch...';
    });
});
</script>
@endpush
@endsection
