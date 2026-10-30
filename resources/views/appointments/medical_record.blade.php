@extends('layouts.profile')

@section('title', 'Hồ sơ khám bệnh #' . ($appointment->appointment_code ?? $appointment->id) . ' - Phòng khám Thái Thịnh')
@section('breadcrumb_current', 'Hồ sơ bệnh án điện tử')

@push('styles')
<style>
/* Medical Record Print Stylesheet */
@media print {
    body * {
        visibility: hidden;
    }
    #printable, #printable * {
        visibility: visible;
    }
    #printable {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        background: #ffffff !important;
        padding: 0 !important;
        border: none !important;
        box-shadow: none !important;
    }
    .no-print, .tiki-header, .tiki-breadcrumb, .tiki-profile-sidebar, footer, .modal, .btn {
        display: none !important;
    }
    .print-only-header {
        display: block !important;
    }
}
.print-only-header {
    display: none;
}
.emr-image-card {
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border-radius: 8px;
    overflow: hidden;
    position: relative;
}
.emr-image-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(2, 132, 199, 0.15);
}
.emr-image-card .hover-overlay {
    position: absolute;
    inset: 0;
    background: rgba(15, 23, 42, 0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.2s ease;
    color: #fff;
    font-size: 1.25rem;
}
.emr-image-card:hover .hover-overlay {
    opacity: 1;
}
</style>
@endpush

@section('content')
<div>
    {{-- Page Header --}}
    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2 no-print">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('appointments.show', $appointment->id) }}" class="ts-spec-btn text-decoration-none" title="Quay lại chi tiết ca khám">
                ← Quay lại
            </a>
            <div>
                <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                    Kết quả &amp; Hồ sơ bệnh án điện tử
                </h5>
                <small class="text-muted">Mã hồ sơ ca khám: <strong class="text-primary ts-num">#{{ $appointment->appointment_code ?? str_pad($appointment->id, 6, '0', STR_PAD_LEFT) }}</strong></small>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('appointments.create', ['doctor_id' => $appointment->doctor_id, 'department_id' => optional($appointment->doctor)->department_id, 'service_id' => $appointment->service_id]) }}" class="ts-btn-secondary">
                <span>Đặt lịch tái khám</span>
            </a>
            <a href="{{ route('appointments.show', $appointment->id) }}" class="ts-btn-secondary">
                <span>Phiếu tiếp nhận</span>
            </a>
            <button type="button" class="ts-btn" onclick="window.print()">
                <span>In hồ sơ bệnh án</span>
            </button>
        </div>
    </div>

    {{-- Medical Record Card --}}
    <div class="tiki-card p-4 mb-4" id="printable">
        {{-- PRINT-ONLY HEADER --}}
        <div class="print-only-header border-bottom pb-3 mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-0 text-uppercase">PHÒNG KHÁM ĐA KHOA THÁI THỊNH</h5>
                    <small class="d-block text-muted">Địa chỉ: 125 Thái Thịnh, Đống Đa, Hà Nội • Hotline: 1900 1234</small>
                    <small class="d-block text-muted">HỆ THỐNG QUẢN LÝ BỆNH ÁN ĐIỆN TỬ (EMR)</small>
                </div>
                <div class="text-end">
                    <h5 class="fw-bold text-primary mb-0 font-monospace">PHIẾU KẾT QUẢ KHÁM BỆNH</h5>
                    <small class="text-muted">Mã ca khám: <strong>{{ $appointment->appointment_code ?? ('APT-' . $appointment->id) }}</strong></small>
                </div>
            </div>
        </div>

        {{-- Screen Header --}}
        <div class="d-flex justify-content-between align-items-center pb-3 mb-4 border-bottom no-print">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="fas fa-file-medical-alt fs-5"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-dark mb-0">Hồ Sơ Bệnh Án Điện Tử (EMR)</h6>
                    <small class="text-muted">Phòng khám Đa khoa Thái Thịnh • Lưu trữ hồ sơ y tế bảo mật</small>
                </div>
            </div>
            <span class="tiki-status-badge completed">
                <i class="fas fa-check-circle"></i> Đã hoàn thành ca khám
            </span>
        </div>

        {{-- Patient Information (BA Clinical Accuracy for Relatives) --}}
        <div class="bg-light rounded-3 p-3 mb-4 border">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="text-primary mb-0 fw-bold d-flex align-items-center small">
                    <i class="fas fa-id-card-alt me-2"></i> THÔNG TIN BỆNH NHÂN KHÁM BỆNH
                </h6>
                @if($appointment->booking_for === 'relative')
                    <span class="badge bg-danger-subtle text-danger fw-bold rounded-pill px-2.5 py-1">
                        <i class="fas fa-users me-1"></i>Ca khám đặt hộ người thân
                    </span>
                @else
                    <span class="badge bg-primary-subtle text-primary fw-bold rounded-pill px-2.5 py-1">
                        <i class="fas fa-user me-1"></i>Bệnh nhân chính chủ
                    </span>
                @endif
            </div>

            <div class="row g-3">
                @if($appointment->booking_for === 'relative')
                    {{-- Patient is a Relative --}}
                    <div class="col-md-4">
                        <small class="text-muted d-block" style="font-size: 12px;">Họ và tên bệnh nhân</small>
                        <strong class="text-dark fs-6">{{ $appointment->relative_name }}</strong>
                        <div class="text-danger small fw-semibold">Quan hệ: {{ $appointment->relative_relationship }}</div>
                    </div>

                    <div class="col-md-3">
                        <small class="text-muted d-block" style="font-size: 12px;">Ngày sinh / Tuổi</small>
                        <strong class="text-dark">
                            {{ $appointment->relative_dob ? \Carbon\Carbon::parse($appointment->relative_dob)->format('d/m/Y') : 'Chưa cập nhật' }}
                        </strong>
                    </div>

                    <div class="col-md-2">
                        <small class="text-muted d-block" style="font-size: 12px;">Giới tính</small>
                        <strong class="text-dark">
                            @if($appointment->relative_gender === 'male') Nam @elseif($appointment->relative_gender === 'female') Nữ @else Khác @endif
                        </strong>
                    </div>

                    <div class="col-md-3">
                        <small class="text-muted d-block" style="font-size: 12px;">Người đại diện đặt lịch</small>
                        <strong class="text-dark">{{ optional($patient)->name ?? 'Tài khoản chính' }}</strong>
                        <div class="small text-muted">SĐT: {{ optional($patient)->phone ?? 'N/A' }}</div>
                    </div>
                @else
                    {{-- Patient is Primary User --}}
                    <div class="col-md-4">
                        <small class="text-muted d-block" style="font-size: 12px;">Họ và tên bệnh nhân</small>
                        <strong class="text-dark fs-6">{{ optional($patient)->name ?? 'Không rõ' }}</strong>
                    </div>

                    <div class="col-md-3">
                        <small class="text-muted d-block" style="font-size: 12px;">Ngày sinh</small>
                        <strong class="text-dark">
                            {{ optional($patient)->birthdate ? \Carbon\Carbon::parse(optional($patient)->birthdate)->format('d/m/Y') : 'N/A' }}
                        </strong>
                    </div>

                    <div class="col-md-2">
                        <small class="text-muted d-block" style="font-size: 12px;">Giới tính</small>
                        <strong class="text-dark">
                            @if(optional($patient)->gender === 'male') Nam @elseif(optional($patient)->gender === 'female') Nữ @else Không rõ @endif
                        </strong>
                    </div>

                    <div class="col-md-3">
                        <small class="text-muted d-block" style="font-size: 12px;">Số điện thoại</small>
                        <strong class="text-dark">{{ optional($patient)->phone ?? 'N/A' }}</strong>
                    </div>
                @endif

                <div class="col-md-4">
                    <small class="text-muted d-block" style="font-size: 12px;">Mã thẻ BHYT</small>
                    <strong class="text-dark">{{ optional($patient)->insurance_number ?? 'Không có BHYT' }}</strong>
                </div>

                <div class="col-md-8">
                    <small class="text-muted d-block" style="font-size: 12px;">Địa chỉ thường trú</small>
                    <strong class="text-dark">{{ optional($patient)->address ?? 'Chưa cập nhật' }}</strong>
                </div>
            </div>
        </div>

        {{-- Appointment Details --}}
        <div class="bg-light rounded-3 p-3 mb-4 border">
            <h6 class="text-primary mb-3 fw-bold d-flex align-items-center small">
                <i class="fas fa-stethoscope me-2"></i> THÔNG TIN BUỔI KHÁM &amp; CHUYÊN KHOA
            </h6>
            <div class="row g-3">
                <div class="col-md-4">
                    <small class="text-muted d-block" style="font-size: 12px;">Bác sĩ phụ trách</small>
                    <strong class="text-dark fs-6">{{ optional(optional($appointment->doctor)->user)->name ?? 'Bác sĩ chuyên khoa' }}</strong>
                    <div class="small text-muted">Khoa: <strong>{{ optional(optional($appointment->doctor)->department)->name ?? 'Đa khoa' }}</strong></div>
                </div>

                <div class="col-md-4">
                    <small class="text-muted d-block" style="font-size: 12px;">Gói dịch vụ khám</small>
                    <span class="badge bg-primary-subtle text-primary fw-bold px-2 py-1" style="font-size: 12.5px;">
                        {{ optional($appointment->service)->name ?? 'Khám chuyên khoa' }}
                    </span>
                </div>

                <div class="col-md-4">
                    <small class="text-muted d-block" style="font-size: 12px;">Ngày &amp; Giờ tiếp nhận</small>
                    <strong class="text-dark">
                        {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d/m/Y') }}
                    </strong>
                    <div class="small text-muted">Khung giờ: {{ $appointment->timeSlot ? $appointment->timeSlot->formatted_time : ($appointment->medical_examination ?? 'Trong giờ') }}</div>
                </div>
            </div>
        </div>

        {{-- Service Package Description if any --}}
        @php
            $svc = $appointment->service ?? null;
        @endphp
        @if($svc && !empty($svc->description))
            @php
                $descLines = preg_split('/\r\n|\r|\n/', $svc->description ?? '');
                $descLines = array_values(array_filter($descLines, fn($line) => trim($line) !== ''));
            @endphp
            <div class="p-3 bg-white border rounded-3 mb-4">
                <h6 class="fw-bold text-dark mb-2 small"><i class="fas fa-list-check text-primary me-2"></i>Quy trình gói khám: {{ $svc->name }}</h6>
                <ul class="mb-0 small ps-3">
                    @foreach($descLines as $line)
                        <li class="mb-1 text-muted">{{ trim($line) }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Medical Record Details --}}
        @if($record)
            @php
                $labTests = $record->labTests ?? \App\Models\LabTest::where('medical_record_id', $record->id)->get();
                $presItems = $record->prescriptionItems ?? collect();

                // Tự động bóc tách Chỉ số sinh hiệu từ mô tả lâm sàng (nếu Bác sĩ đã nhập qua Vitals Builder)
                $rawDesc = (string) ($record->description ?? $record->symptoms ?? '');
                $extractedVitals = [];
                if (preg_match('/\[Sinh hiệu:\s*([^\]]+)\]/u', $rawDesc, $vMatch)) {
                    $parts = explode('|', $vMatch[1]);
                    foreach ($parts as $p) {
                        $p = trim($p);
                        if ($p !== '') {
                            $extractedVitals[] = $p;
                        }
                    }
                }

                // Tự động nhận diện ngày hẹn tái khám (dd/mm/yyyy) trong lời dặn của bác sĩ
                $rawConclusion = (string) ($record->doctor_conclusion ?? $record->treatment ?? '');
                $detectedFollowupDisplay = null;
                $detectedFollowupIso = null;
                if (preg_match('/(\d{1,2})\/(\d{1,2})\/(\d{4})/', $rawConclusion, $dMatch)) {
                    try {
                        $parsedFollowup = \Carbon\Carbon::createFromDate((int)$dMatch[3], (int)$dMatch[2], (int)$dMatch[1]);
                        $detectedFollowupDisplay = $parsedFollowup->format('d/m/Y');
                        $detectedFollowupIso = $parsedFollowup->format('Y-m-d');
                    } catch (\Throwable $e) {
                        $detectedFollowupDisplay = null;
                    }
                }
                $rebookDateParam = $detectedFollowupIso ?: now()->addDays(7)->format('Y-m-d');
                $gcalFollowupStart = \Carbon\Carbon::parse($rebookDateParam . ' 08:30:00')->format('Ymd\THis');
                $gcalFollowupEnd = \Carbon\Carbon::parse($rebookDateParam . ' 09:30:00')->format('Ymd\THis');
                $gcalFollowupUrl = 'https://calendar.google.com/calendar/render?' . http_build_query([
                    'action' => 'TEMPLATE',
                    'text' => 'Lịch tái khám tại PKĐK 125 Thái Thịnh - BS. ' . (optional(optional($appointment->doctor)->user)->name ?? 'Chuyên khoa'),
                    'dates' => $gcalFollowupStart . '/' . $gcalFollowupEnd,
                    'details' => 'Tái khám theo dõi chẩn đoán: ' . ($record->diagnosis ?? 'Khám chuyên khoa') . '. Địa chỉ: 125 Thái Thịnh, Đống Đa, Hà Nội.',
                    'location' => 'Phòng khám Đa khoa 125 Thái Thịnh, 125 Thái Thịnh, Đống Đa, Hà Nội',
                ]);
            @endphp

            {{-- Smart Follow-up Reminder Banner --}}
            <div id="smartFollowupBanner" class="p-3 mb-4 rounded-3 border border-primary-subtle no-print" style="background: linear-gradient(135deg, #eff6ff 0%, #f8fafc 100%);">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 42px; height: 42px;">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark" style="font-size: 14px;">
                                @if($detectedFollowupDisplay)
                                    Bác sĩ hẹn lịch tái khám vào ngày <span class="badge bg-primary text-white ms-1">{{ $detectedFollowupDisplay }}</span>
                                @else
                                    Khuyến nghị theo dõi &amp; đặt lịch tái khám định kỳ cùng Bác sĩ điều trị
                                @endif
                            </div>
                            <div class="small text-muted">
                                Đặt lịch tái khám 1 chạm với <strong>{{ optional($appointment->doctor)->formatted_name ?? optional(optional($appointment->doctor)->user)->name ?? 'Bác sĩ chuyên khoa' }}</strong> hoặc lưu lời nhắc vào lịch điện thoại.
                            </div>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ $gcalFollowupUrl }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                            <i class="far fa-bell me-1"></i>Nhắc lịch tái khám Google
                        </a>
                        <a href="{{ route('appointments.create', ['doctor_id' => $appointment->doctor_id, 'department_id' => optional($appointment->doctor)->department_id, 'service_id' => $appointment->service_id, 'date' => $rebookDateParam]) }}"
                           class="btn btn-sm btn-primary rounded-pill px-3 fw-semibold shadow-sm">
                            <i class="fas fa-redo-alt me-1"></i>Đặt lịch tái khám {{ $detectedFollowupDisplay ? '(' . $detectedFollowupDisplay . ')' : 'ngay' }}
                        </a>
                    </div>
                </div>
            </div>

            {{-- Extracted Vital Signs Badges (if present) --}}
            @if(count($extractedVitals) > 0)
                <div class="p-3 mb-4 rounded-3 border bg-white">
                    <div class="small fw-bold text-primary mb-2">
                        <i class="fas fa-heartbeat text-danger me-1.5"></i>CHỈ SỐ SINH HIỆU GHI NHẬN TẠI BUỒNG KHÁM
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($extractedVitals as $vitalItem)
                            <span class="badge bg-light text-dark border px-3 py-2" style="font-size: 12.5px; font-weight: 600;">
                                <i class="fas fa-Notes-medical text-info me-1"></i>{{ $vitalItem }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Doctor Diagnosis & Treatment --}}
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="p-3 border rounded-3 bg-white h-100">
                        <small class="text-muted fw-bold d-block mb-1.5" style="font-size: 12px;">
                            <i class="fas fa-notes-medical text-primary me-1"></i>TRIỆU CHỨNG &amp; KHÁM LÂM SÀNG
                        </small>
                        <p class="mb-0 text-dark" style="font-size: 13.5px; line-height: 1.6; white-space: pre-line;">{{ $record->description ?? $record->symptoms ?? ($appointment->note ?: 'Không ghi nhận triệu chứng bất thường.') }}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-3 border rounded-3 bg-white h-100">
                        <small class="text-muted fw-bold d-block mb-1.5" style="font-size: 12px;">
                            <i class="fas fa-stethoscope text-primary me-1"></i>CHẨN ĐOÁN CỦA BÁC SĨ (ICD-10)
                        </small>
                        <strong class="text-danger d-block fs-6 mb-1">{{ $record->diagnosis ?? 'Đang cập nhật chẩn đoán' }}</strong>
                        <span class="badge bg-primary-subtle text-primary">
                            <i class="fas fa-check-circle me-1"></i>Đã xác nhận bởi {{ optional($appointment->doctor)->formatted_name ?? optional(optional($appointment->doctor)->user)->name ?? 'Bác sĩ chuyên khoa' }}
                        </span>
                    </div>
                </div>
                <div class="col-12">
                    <div class="p-3 border rounded-3 bg-white">
                        <small class="text-muted fw-bold d-block mb-1.5" style="font-size: 12px;">
                            <i class="fas fa-comment-medical text-success me-1"></i>KẾT LUẬN &amp; LỜI DẶN ĐIỀU TRỊ CỦA BÁC SĨ
                        </small>
                        <div class="p-3 bg-success-subtle bg-opacity-25 rounded-3 border border-success-subtle text-dark" style="font-size: 13.5px; line-height: 1.6; white-space: pre-line;">{{ $record->doctor_conclusion ?? $record->treatment ?? 'Tuân thủ đúng liều lượng đơn thuốc và tái khám theo lịch hẹn hoặc khi có dấu hiệu bất thường.' }}</div>
                    </div>
                </div>
            </div>

            {{-- Lab Tests & Diagnostic Images Section --}}
            @if(!empty($record->image) || (!empty($record->images) && is_array($record->images) && count($record->images)) || ($labTests && $labTests->count()))
                <div class="p-3 border rounded-3 mb-4 bg-white">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <h6 class="fw-bold text-dark mb-0 small">
                            <i class="fas fa-vials text-primary me-2"></i>Kết Quả Xét Nghiệm &amp; Chẩn Đoán Hình Ảnh (Cận Lâm Sàng)
                        </h6>
                        <small class="text-muted no-print"><i class="fas fa-search-plus me-1"></i>Bấm vào hình ảnh để phóng to</small>
                    </div>

                    {{-- Direct Images attached to Medical Record --}}
                    @if(!empty($record->image) || (!empty($record->images) && is_array($record->images) && count($record->images)))
                        <div class="mb-3">
                            <small class="text-muted fw-semibold d-block mb-2">Hình ảnh lâm sàng đính kèm:</small>
                            <div class="d-flex flex-wrap gap-3">
                                @if(!empty($record->image))
                                    <div class="emr-image-card border" onclick="openLightbox('{{ asset('storage/' . $record->image) }}', 'Hình ảnh chẩn đoán lâm sàng')">
                                        <img src="{{ asset('storage/' . $record->image) }}" style="width: 130px; height: 130px; object-fit: cover;" alt="Hình ảnh chẩn đoán">
                                        <div class="hover-overlay"><i class="fas fa-search-plus"></i></div>
                                    </div>
                                @endif

                                @if(!empty($record->images) && is_array($record->images))
                                    @foreach($record->images as $idx => $img)
                                        <div class="emr-image-card border" onclick="openLightbox('{{ asset('storage/' . $img) }}', 'Hình ảnh lâm sàng #{{ $idx + 1 }}')">
                                            <img src="{{ asset('storage/' . $img) }}" style="width: 130px; height: 130px; object-fit: cover;" alt="Kết quả">
                                            <div class="hover-overlay"><i class="fas fa-search-plus"></i></div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- Lab Test Orders Table & Results --}}
                    @if($labTests && $labTests->count())
                        <div class="d-flex flex-column gap-2">
                            @foreach($labTests as $test)
                                <div class="p-3 rounded-3 bg-light border">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-1">
                                        <div>
                                            <strong class="text-dark"><i class="fas fa-flask text-primary me-1.5"></i>{{ $test->test_name ?? 'Xét nghiệm cận lâm sàng' }}</strong>
                                            @if($test->department)
                                                <span class="badge bg-white text-secondary border ms-1">{{ $test->department->name }}</span>
                                            @endif
                                        </div>
                                        @if($test->status === 'completed')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                <i class="fas fa-check-circle me-1"></i>Đã có kết quả
                                            </span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                                                <i class="fas fa-hourglass-half me-1"></i>Đang phân tích kết quả
                                            </span>
                                        @endif
                                    </div>

                                    @if(!empty($test->note))
                                        <div class="small text-dark mt-1 ps-2 border-start border-2 border-primary">
                                            <strong>Nhận xét / Kết quả:</strong> {{ $test->note }}
                                        </div>
                                    @endif

                                    @php
                                        $testImages = [];
                                        if (!empty($test->image)) {
                                            $testImages[] = $test->image;
                                        }
                                        if (!empty($test->images) && is_array($test->images)) {
                                            foreach ($test->images as $extraImg) {
                                                if ($extraImg && !in_array($extraImg, $testImages)) {
                                                    $testImages[] = $extraImg;
                                                }
                                            }
                                        }
                                    @endphp

                                    @if(count($testImages) > 0)
                                        <div class="d-flex flex-wrap gap-2 mt-2 pt-2 border-top">
                                            @foreach($testImages as $imgIdx => $imgPath)
                                                @php
                                                    $imgUrl = asset('storage/' . $imgPath);
                                                @endphp
                                                <div class="emr-image-card border bg-white" onclick="openLightbox('{{ $imgUrl }}', '{{ $test->test_name }} - Ảnh {{ $imgIdx + 1 }}')">
                                                    <img src="{{ $imgUrl }}" style="width: 110px; height: 110px; object-fit: cover;" alt="{{ $test->test_name }}">
                                                    <div class="hover-overlay"><i class="fas fa-search-plus"></i></div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            {{-- Prescription Table (Supports both relational PrescriptionItem and legacy JSON) --}}
            @php
                $legacyPrescription = $record->prescription ?? [];
                if (is_string($legacyPrescription)) {
                    $legacyPrescription = json_decode($legacyPrescription, true) ?? [];
                }
                $totalMedicineCount = $presItems->count() > 0 ? $presItems->count() : count($legacyPrescription);
            @endphp
            <div class="p-3 border rounded-3 bg-white mb-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold text-dark mb-0 small">
                        <i class="fas fa-pills text-primary me-2"></i>Đơn Thuốc Chỉ Định Điều Trị Ngoại Trú
                    </h6>
                    @if($totalMedicineCount > 0)
                        <span class="badge bg-success-subtle text-success fw-bold">Có {{ $totalMedicineCount }} loại thuốc</span>
                    @endif
                </div>

                @if($presItems->count() > 0)
                    @php $estimatedDrugTotal = 0; @endphp
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr class="text-center small">
                                    <th style="width: 45px;">#</th>
                                    <th class="text-start">Tên thuốc &amp; Hoạt chất</th>
                                    <th style="width: 110px;">Số lượng</th>
                                    <th class="text-start">Liều dùng &amp; Hướng dẫn sử dụng</th>
                                    <th style="width: 130px;" class="text-end">Đơn giá tham khảo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($presItems as $idx => $item)
                                    @php
                                        $unitPrice = optional($item->medicine)->price ?? 0;
                                        $lineTotal = $unitPrice * ($item->quantity ?? 1);
                                        $estimatedDrugTotal += $lineTotal;
                                    @endphp
                                    <tr style="font-size: 13px;">
                                        <td class="text-center fw-bold">{{ $idx + 1 }}</td>
                                        <td>
                                            <div class="fw-bold text-primary">{{ optional($item->medicine)->name ?? ('Thuốc #' . $item->medicine_id) }}</div>
                                            @if(optional($item->medicine)->generic_name || optional($item->medicine)->strength)
                                                <small class="text-muted">{{ optional($item->medicine)->generic_name }} {{ optional($item->medicine)->strength }}</small>
                                            @endif
                                        </td>
                                        <td class="text-center fw-bold">
                                            {{ $item->quantity }} {{ $item->unit ?? (optional($item->medicine)->unit ?? 'Viên') }}
                                        </td>
                                        <td>
                                            <div class="text-dark fw-semibold">{{ $item->usage ?: ($item->dosage ?: 'Theo chỉ định của bác sĩ') }}</div>
                                            @if($item->frequency || $item->duration)
                                                <small class="text-muted d-block">
                                                    {{ $item->frequency ? 'Tần suất: ' . $item->frequency : '' }}
                                                    {{ $item->duration ? ' • Thời gian: ' . $item->duration : '' }}
                                                </small>
                                            @endif
                                            @if($item->note)
                                                <small class="text-secondary fst-italic d-block">Lưu ý: {{ $item->note }}</small>
                                            @endif
                                        </td>
                                        <td class="text-end font-monospace">
                                            {{ $lineTotal > 0 ? number_format($lineTotal, 0, ',', '.') . ' đ' : 'Theo quầy dược' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            @if($estimatedDrugTotal > 0)
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="4" class="text-end fw-bold small">Tổng chi phí thuốc ước tính tại Nhà thuốc Phòng khám:</td>
                                        <td class="text-end fw-bold text-danger font-monospace">{{ number_format($estimatedDrugTotal, 0, ',', '.') }} đ</td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>

                    {{-- Bảng tóm tắt Lịch uống thuốc trong ngày (Sáng / Trưa / Chiều / Tối) --}}
                    @php
                        $scheduleSlots = [
                            'morning' => ['label' => 'Buổi Sáng (07:00 - 08:00)', 'icon' => 'fa-sun text-warning', 'meds' => []],
                            'noon'    => ['label' => 'Buổi Trưa (11:30 - 12:30)', 'icon' => 'fa-cloud-sun text-info', 'meds' => []],
                            'afternoon' => ['label' => 'Buổi Chiều (17:00 - 18:00)', 'icon' => 'fa-Stopwatch text-primary', 'meds' => []],
                            'evening' => ['label' => 'Buổi Tối (20:00 - 21:30)', 'icon' => 'fa-moon text-secondary', 'meds' => []],
                        ];
                        foreach ($presItems as $pItem) {
                            $mName = optional($pItem->medicine)->name ?? ('Thuốc #' . $pItem->medicine_id);
                            $textCheck = mb_strtolower(($pItem->usage ?? '') . ' ' . ($pItem->dosage ?? '') . ' ' . ($pItem->frequency ?? '') . ' ' . ($pItem->note ?? ''));
                            $matchedAny = false;
                            if (str_contains($textCheck, 'sáng')) { $scheduleSlots['morning']['meds'][] = $mName; $matchedAny = true; }
                            if (str_contains($textCheck, 'trưa')) { $scheduleSlots['noon']['meds'][] = $mName; $matchedAny = true; }
                            if (str_contains($textCheck, 'chiều')) { $scheduleSlots['afternoon']['meds'][] = $mName; $matchedAny = true; }
                            if (str_contains($textCheck, 'tối')) { $scheduleSlots['evening']['meds'][] = $mName; $matchedAny = true; }
                            if (!$matchedAny) {
                                if (str_contains($textCheck, '3 lần')) {
                                    $scheduleSlots['morning']['meds'][] = $mName;
                                    $scheduleSlots['noon']['meds'][] = $mName;
                                    $scheduleSlots['evening']['meds'][] = $mName;
                                } elseif (str_contains($textCheck, '2 lần')) {
                                    $scheduleSlots['morning']['meds'][] = $mName;
                                    $scheduleSlots['evening']['meds'][] = $mName;
                                } else {
                                    $scheduleSlots['morning']['meds'][] = $mName;
                                }
                            }
                        }
                    @endphp
                    <div class="mt-3 p-3 rounded-3 bg-light border">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                            <span class="small fw-bold text-primary">
                                <i class="fas fa-clock me-1"></i>LỊCH NHẮC UỐNG THUỐC TRONG NGÀY (TỔNG HỢP TỰ ĐỘNG)
                            </span>
                            <span class="small text-muted"><i class="fas fa-shield-alt text-success me-1"></i>Uống thuốc với nước ấm, không tự ý ngưng kháng sinh</span>
                        </div>
                        <div class="row g-2">
                            @foreach($scheduleSlots as $slotKey => $slotData)
                                @if(count($slotData['meds']) > 0)
                                    <div class="col-md-6 col-lg-3">
                                        <div class="p-2 rounded bg-white border h-100">
                                            <div class="small fw-bold text-dark mb-1">
                                                <i class="fas {{ $slotData['icon'] }} me-1"></i>{{ $slotData['label'] }}
                                            </div>
                                            <div class="d-flex flex-wrap gap-1">
                                                @foreach(array_unique($slotData['meds']) as $mLabel)
                                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 11px;">{{ $mLabel }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @elseif(!empty($legacyPrescription))
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr class="text-center small">
                                    <th style="width: 45px;">#</th>
                                    <th>Tên thuốc &amp; Hàm lượng</th>
                                    <th>Liều lượng</th>
                                    <th>Cách dùng</th>
                                    <th>Thời gian dùng</th>
                                    <th>Lưu ý dược sĩ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($legacyPrescription as $idx => $item)
                                    <tr style="font-size: 13px;">
                                        <td class="text-center fw-bold">{{ $idx + 1 }}</td>
                                        <td class="fw-bold text-primary">{{ $item['drug_name'] ?? '-' }}</td>
                                        <td>{{ $item['dosage'] ?? '-' }}</td>
                                        <td><span class="badge bg-light text-dark border">{{ $item['frequency'] ?? '-' }}</span></td>
                                        <td>{{ $item['duration'] ?? '-' }}</td>
                                        <td class="text-muted fst-italic">{{ $item['note'] ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted small mb-0 fst-italic">Bệnh nhân không có đơn thuốc kèm theo trong ca khám này.</p>
                @endif
            </div>

            {{-- Doctor Signature Area (Chỉ hiện khi In) --}}
            <div class="row pt-4 mt-4 border-top">
                <div class="col-6 text-center">
                    <small class="text-muted d-block">BỆNH NHÂN / ĐẠI DIỆN</small>
                    <small class="fst-italic text-secondary">(Ký và ghi rõ họ tên)</small>
                </div>
                <div class="col-6 text-center">
                    <small class="text-muted d-block">Hà Nội, ngày {{ now()->format('d') }} tháng {{ now()->format('m') }} năm {{ now()->format('Y') }}</small>
                    <small class="fw-bold text-dark text-uppercase d-block mt-1">BÁC SĨ ĐIỀU TRỊ</small>
                    <div style="height: 60px;"></div>
                    <strong class="text-dark">{{ optional(optional($appointment->doctor)->user)->name ?? 'Bác sĩ chuyên khoa' }}</strong>
                </div>
            </div>
        @else
            <div class="text-center py-5 text-muted bg-white border rounded-3">
                <i class="fas fa-hourglass-half fa-3x mb-3 text-warning opacity-75"></i>
                <h6 class="fw-bold text-dark">Hồ sơ bệnh án đang được bác sĩ hoàn thiện</h6>
                <p class="small text-muted mb-0">Sau khi hoàn tất quá trình thăm khám lâm sàng và có kết quả xét nghiệm, bác sĩ sẽ ký điện tử và hồ sơ sẽ tự động hiển thị tại đây.</p>
            </div>
        @endif
    </div>
</div>

{{-- LIGHTBOX MODAL FOR IMAGES --}}
<div class="modal fade no-print" id="lightboxModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg bg-dark">
            <div class="modal-header border-0 py-2 px-3 text-white">
                <h6 class="modal-title fw-bold text-truncate" id="lightboxTitle">Xem chi tiết chẩn đoán hình ảnh</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-2 text-center">
                <img id="lightboxImg" src="" class="img-fluid rounded" style="max-height: 80vh; object-fit: contain;" alt="Kết quả">
            </div>
            <div class="modal-footer border-0 py-2 px-3 justify-content-between">
                <span class="small text-white-50">Bệnh án điện tử Thái Thịnh</span>
                <a href="#" id="lightboxDownload" class="btn btn-outline-light btn-sm rounded-pill px-3" download>
                    <i class="fas fa-download me-1"></i>Tải ảnh gốc về máy
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openLightbox(imgUrl, title) {
    const modalEl = document.getElementById('lightboxModal');
    if (!modalEl) return;
    document.getElementById('lightboxImg').src = imgUrl;
    document.getElementById('lightboxTitle').textContent = title || 'Chi tiết hình ảnh';
    document.getElementById('lightboxDownload').href = imgUrl;
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
}
</script>
@endpush
@endsection
