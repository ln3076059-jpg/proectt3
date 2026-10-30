@extends('layouts.doctor')

@section('title', 'Hồ sơ khám bệnh & Bệnh án EMR')

@section('content')
@php
    $isReadOnly = $isReadOnly ?? false;
    $labTests = $record->labTests ?? collect();
@endphp

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-1 text-gray-800 font-weight-bold">
            <i class="fas fa-notes-medical text-primary mr-2"></i>Khám bệnh & Bệnh án điện tử EMR
        </h1>
        <p class="text-muted small mb-0">
            Mã lịch hẹn: <span class="font-weight-bold text-primary">{{ $appointment->appointment_code ?? '#'.str_pad($appointment->id, 5, '0', STR_PAD_LEFT) }}</span>
            | Ngày hẹn: <span class="font-weight-bold">{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d/m/Y H:i') }}</span>
            | Bác sĩ phụ trách: <span class="font-weight-bold text-gray-800">{{ $appointment->doctor->formatted_name ?? $appointment->doctor->user->name ?? 'N/A' }}</span>
            | Trạng thái: 
            @if($appointment->status === 'completed')
                <span class="badge badge-success">Đã hoàn thành khám</span>
            @elseif($appointment->status === 'checked_in')
                <span class="badge badge-info">Đã tiếp nhận (QR)</span>
            @else
                <span class="badge badge-warning text-dark">{{ $appointment->status }}</span>
            @endif
        </p>
    </div>
    <div class="mt-2 mt-sm-0">
        <button type="button" onclick="window.print()" class="btn btn-sm btn-info shadow-sm mr-2" title="In bệnh án / Đơn thuốc">
            <i class="fas fa-print fa-sm text-white-50 mr-1"></i> In bệnh án / Đơn thuốc
        </button>
        <a href="{{ route('doctor.dashboard') }}" class="btn btn-sm btn-secondary shadow-sm mr-2">
            <i class="fas fa-arrow-left fa-sm text-white-50 mr-1"></i> Về danh sách tiếp đón
        </a>
        @if(!$isReadOnly && $appointment->status !== 'completed')
            <form action="{{ route('doctor.appointments.complete', $appointment) }}" method="POST" class="d-inline" onsubmit="return confirm('Xác nhận hoàn thành ca khám bệnh và trừ kho dược phẩm tương ứng?');">
                @csrf
                <button type="submit" class="btn btn-sm btn-success shadow-sm">
                    <i class="fas fa-check-double fa-sm text-white-50 mr-1"></i> Hoàn thành buổi khám
                </button>
            </form>
        @endif
    </div>
</div>

@if($isReadOnly)
    <div class="alert alert-info alert-dismissible fade show shadow-sm" role="alert">
        <i class="fas fa-info-circle mr-2"></i>
        <strong>Chế độ xem tiền sử y khoa (Read-Only):</strong> Bạn đang xem hồ sơ bệnh án thuộc phụ trách của <strong>{{ $appointment->doctor->formatted_name ?? $appointment->doctor->user->name ?? 'bác sĩ khác' }}</strong> (Khoa {{ $appointment->doctor->department->name ?? 'Đa khoa' }}). Bạn có toàn quyền tra cứu tiền sử bệnh và kết quả cận lâm sàng của bệnh nhân nhưng không thể chỉnh sửa chẩn đoán của ca khám này.
    </div>
@endif

{{-- Flash Alert --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
        <i class="fas fa-exclamation-triangle mr-2"></i>{{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
        <h6 class="font-weight-bold mb-1"><i class="fas fa-exclamation-circle mr-1"></i> Có lỗi xảy ra trong dữ liệu nhập:</h6>
        <ul class="mb-0 pl-3">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@php
    $isFamily = ($appointment->booking_for === 'relative' && !empty($appointment->relative_name));
    $patientName = $isFamily ? $appointment->relative_name : ($patient->name ?? optional($patient->user)->name ?? 'Bệnh nhân');
    $dob = $isFamily ? $appointment->relative_dob : ($patient->birthdate ?? optional($patient->user)->birthdate ?? null);
    $rawGender = $isFamily ? $appointment->relative_gender : ($patient->gender ?? optional($patient->user)->gender ?? 'N/A');
    $genderLower = mb_strtolower(trim((string) $rawGender), 'UTF-8');
    $genderLabel = in_array($genderLower, ['male', 'nam']) ? 'Nam' : (in_array($genderLower, ['female', 'nữ', 'nu']) ? 'Nữ' : 'Khác');
    $phone = $patient->phone ?? optional($patient->user)->phone ?? 'Chưa cập nhật';
    $age = $dob ? \Carbon\Carbon::parse($dob)->age : 'N/A';
    $insurance = $isFamily ? 'Hồ sơ Người thân (Theo dõi riêng)' : ($patient->insurance_number ?? 'BHYT-CHUA-CAP-NHAT');
    $pendingLabTestsCount = $record->labTests ? $record->labTests->where('status', 'requested')->count() : 0;
@endphp

@if($pendingLabTestsCount > 0 && $appointment->status !== 'completed')
    <div class="alert alert-warning shadow-sm border-left-warning mb-3" role="alert">
        <div class="d-flex align-items-center justify-content-between flex-wrap">
            <div>
                <i class="fas fa-flask mr-2 text-warning"></i>
                <strong>Cảnh báo Cận lâm sàng (LIS):</strong> Bệnh nhân đang có <strong>{{ $pendingLabTestsCount }}</strong> chỉ định xét nghiệm ở trạng thái <em>Chờ kết quả (Requested)</em>. Vui lòng kiểm tra kết quả xét nghiệm trước khi chốt hoàn thành ca khám.
            </div>
            <span class="badge badge-warning text-dark px-2 py-1 mt-1 mt-sm-0">{{ $pendingLabTestsCount }} xét nghiệm chờ trả KQ</span>
        </div>
    </div>
@endif

@if($appointment->status === 'completed')
    <div class="alert alert-info shadow-sm border-left-info mb-3" role="alert">
        <i class="fas fa-shield-alt mr-2 text-info"></i>
        <strong>Chế độ Kiểm toán Bệnh án Điện tử (Thông tư 46/2018/TT-BYT):</strong> Ca khám này đã hoàn tất. Mọi chỉnh sửa bổ sung đối với chẩn đoán hoặc đơn thuốc sẽ được tự động ghi vết phiên bản vào Nhật ký Kiểm toán Y khoa (Audit Trail).
    </div>
@endif

<div class="row">
    <!-- Cột trái: Thông tin Bệnh nhân & Checkin -->
    <div class="col-lg-4 mb-4">
        <!-- Patient Info Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-light d-flex align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-user-circle mr-1"></i> Thông tin Bệnh nhân
                </h6>
                @if($isFamily)
                    <span class="badge badge-warning text-dark">Người thân ({{ $appointment->relative_relationship ?? 'Đặt hộ' }})</span>
                @endif
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-2 shadow-sm" style="width: 80px; height: 80px;">
                        <i class="fas fa-user-injured fa-3x"></i>
                    </div>
                    <h5 class="font-weight-bold text-gray-900 mb-0">{{ $patientName }}</h5>
                    <div class="text-xs text-muted">Mã BN: #PAT-{{ str_pad($patient->id ?? 1, 4, '0', STR_PAD_LEFT) }}</div>
                </div>

                <hr>

                <div class="small text-gray-700">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted"><i class="fas fa-venus-mars mr-1 text-info"></i>Giới tính:</span>
                        <span class="font-weight-bold">{{ $genderLabel }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted"><i class="fas fa-birthday-cake mr-1 text-info"></i>Ngày sinh:</span>
                        <span class="font-weight-bold">{{ $dob ? \Carbon\Carbon::parse($dob)->format('d/m/Y') : 'Chưa cập nhật' }} ({{ $age }} tuổi)</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted"><i class="fas fa-phone mr-1 text-info"></i>Điện thoại:</span>
                        <span class="font-weight-bold text-gray-900">{{ $phone }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted"><i class="fas fa-id-card mr-1 text-info"></i>Mã BHYT:</span>
                        <span class="font-weight-bold text-success">{{ $insurance }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted"><i class="fas fa-map-marker-alt mr-1 text-info"></i>Địa chỉ:</span>
                        <span class="font-weight-bold text-right">{{ $patient->address ?? 'Hà Nội' }}</span>
                    </div>
                    @if($appointment->note)
                        <div class="mt-3 p-2 bg-light rounded border">
                            <span class="text-xs font-weight-bold text-danger text-uppercase d-block mb-1">
                                <i class="fas fa-notes-medical mr-1"></i>Lý do / Triệu chứng khi đặt:
                            </span>
                            <span class="text-gray-800">{{ $appointment->note }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- QR Checkin Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-light">
                <h6 class="m-0 font-weight-bold text-secondary">
                    <i class="fas fa-qrcode mr-1"></i> Tiếp nhận / QR Check-in
                </h6>
            </div>
            <div class="card-body text-center">
                @if($appointment->status === 'checked_in')
                    <div class="text-success mb-2">
                        <i class="fas fa-check-circle fa-3x"></i>
                    </div>
                    <h6 class="font-weight-bold text-success">Đã tiếp nhận vào phòng khám</h6>
                    <small class="text-muted">Thời điểm: {{ $appointment->checked_in_at ? \Carbon\Carbon::parse($appointment->checked_in_at)->format('H:i - d/m/Y') : 'Hôm nay' }}</small>
                @elseif($appointment->status === 'completed')
                    <div class="text-success mb-2">
                        <i class="fas fa-clipboard-check fa-3x"></i>
                    </div>
                    <h6 class="font-weight-bold text-success">Buổi khám đã hoàn tất</h6>
                    <small class="text-muted">Hồ sơ đã được lưu trữ an toàn vào hệ thống EMR.</small>
                @else
                    <p class="small text-muted mb-3">Xác nhận bệnh nhân đã có mặt tại sảnh tiếp đón để bắt đầu khám.</p>
                    @if(!$isReadOnly)
                        <form action="{{ route('doctor.appointments.checkin', $appointment) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-block shadow-sm">
                                <i class="fas fa-qrcode mr-1"></i> Xác nhận Tiếp nhận (Check-in)
                            </button>
                        </form>
                    @else
                        <span class="badge badge-warning text-dark px-3 py-2">Đang chờ tiếp đón</span>
                    @endif
                @endif
            </div>
        </div>

        <!-- Tiền sử khám bệnh tại Phòng khám Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-light d-flex align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-history mr-1"></i> Tiền sử khám tại Thái Thịnh
                </h6>
                <span class="badge badge-secondary">{{ isset($previousRecords) ? $previousRecords->count() : 0 }} lần trước</span>
            </div>
            <div class="card-body p-3">
                @if(isset($previousRecords) && $previousRecords->isNotEmpty())
                    <div class="timeline-history">
                        @foreach($previousRecords as $prev)
                            <div class="pb-3 mb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="badge badge-light border text-primary font-weight-bold">
                                        <i class="far fa-calendar-alt mr-1"></i>{{ $prev->appointment ? \Carbon\Carbon::parse($prev->appointment->appointment_date)->format('d/m/Y') : $prev->created_at->format('d/m/Y') }}
                                    </span>
                                    @if($prev->appointment)
                                        <a href="{{ route('doctor.patient.record', $prev->appointment_id) }}" class="text-xs font-weight-bold text-info">
                                            Xem chi tiết <i class="fas fa-external-link-alt ml-1"></i>
                                        </a>
                                    @endif
                                </div>
                                <div class="small font-weight-bold text-gray-800 mb-1">
                                    Chẩn đoán: <span class="text-danger">{{ $prev->diagnosis }}</span>
                                </div>
                                <div class="text-xs text-muted mb-1">
                                    Khoa: {{ $prev->appointment->doctor->department->name ?? 'Đa khoa' }}
                                    • {{ $prev->appointment->doctor->user->name ?? 'Bác sĩ' }}
                                </div>
                                @if($prev->prescriptionItems && $prev->prescriptionItems->isNotEmpty())
                                    @php
                                        $prevItemsPayload = $prev->prescriptionItems->map(function ($it) {
                                            return [
                                                'medicine_id' => $it->medicine_id,
                                                'quantity' => $it->quantity ?? 1,
                                                'unit' => $it->unit ?? optional($it->medicine)->unit ?? 'Viên',
                                                'usage' => $it->usage ?? optional($it->medicine)->usage ?? 'Uống sau ăn',
                                            ];
                                        })->values();
                                    @endphp
                                    <div class="text-xs bg-light p-2 rounded border mt-1">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <strong>Đơn thuốc cũ:</strong>
                                            @if(!$isReadOnly)
                                                <button type="button"
                                                        class="btn btn-xs btn-outline-primary py-0 px-2 js-copy-old-prescription"
                                                        style="font-size: 11px;"
                                                        data-items='@json($prevItemsPayload)'
                                                        title="Sao chép toàn bộ đơn thuốc này sang ca khám hiện tại">
                                                    <i class="fas fa-copy mr-1"></i>Sao chép đơn này
                                                </button>
                                            @endif
                                        </div>
                                        {{ $prev->prescriptionItems->map(fn($it) => ($it->medicine->name ?? 'Thuốc') . ' (x' . $it->quantity . ')')->implode(', ') }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center text-muted small py-3">
                        <i class="fas fa-folder-open fa-2x text-gray-300 mb-2 d-block"></i>
                        Bệnh nhân chưa có tiền sử bệnh án trước đây tại phòng khám.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Cột phải: Form Khám bệnh & Chẩn đoán EMR -->
    <div class="col-lg-8 mb-4">
        <form action="{{ route('doctor.records.update', $record) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <!-- Chẩn đoán lâm sàng Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-stethoscope mr-1"></i> Chẩn đoán lâm sàng & Kết luận của Bác sĩ
                    </h6>
                    <span class="badge badge-light border">Hồ sơ #EMR-{{ str_pad($record->id, 4, '0', STR_PAD_LEFT) }}</span>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="font-weight-bold text-gray-700">
                            Chẩn đoán bệnh lý chính (ICD-10) <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="diagnosis" id="diagnosisInput" class="form-control @error('diagnosis') is-invalid @enderror" value="{{ old('diagnosis', $record->diagnosis) }}" placeholder="Ví dụ: Cảm cúm hô hấp trên / Tăng huyết áp độ 1 / Viêm loét dạ dày..." {{ $isReadOnly ? 'readonly' : 'required' }}>
                        @error('diagnosis')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        @if(!$isReadOnly && isset($commonDiseases) && $commonDiseases->isNotEmpty())
                            <div class="mt-2">
                                <span class="text-xs text-muted mr-1"><i class="fas fa-bolt text-warning mr-1"></i>Chẩn đoán nhanh:</span>
                                @foreach($commonDiseases as $diseaseName)
                                    <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2 mr-1 mb-1 quick-diagnosis-chip" style="font-size: 11.5px; border-radius: 12px;" data-text="{{ $diseaseName }}">
                                        + {{ $diseaseName }}
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    @if(!$isReadOnly)
                        {{-- Thanh nhập nhanh Chỉ số sinh tồn (Vitals Builder + Auto BMI) --}}
                        <div class="p-3 mb-3 rounded border bg-light">
                            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap">
                                <span class="text-xs font-weight-bold text-primary text-uppercase">
                                    <i class="fas fa-heartbeat mr-1 text-danger"></i>Chỉ số sinh tồn (Tự động tính BMI &amp; Chèn vào bệnh án)
                                </span>
                                <div class="d-flex align-items-center">
                                    <span class="badge badge-info mr-2" id="liveBmiBadge">BMI: --</span>
                                    <button type="button" id="applyVitalsBtn" class="btn btn-xs btn-primary py-1 px-2" style="font-size: 11.5px;">
                                        <i class="fas fa-arrow-down mr-1"></i>Chèn vào ô khám thực thể
                                    </button>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="col-6 col-md-2 mb-1">
                                    <label class="text-xs text-muted mb-0">Mạch (l/p)</label>
                                    <input type="number" id="vitalPulse" class="form-control form-control-sm" placeholder="78">
                                </div>
                                <div class="col-6 col-md-2 mb-1">
                                    <label class="text-xs text-muted mb-0">Huyết áp</label>
                                    <input type="text" id="vitalBp" class="form-control form-control-sm" placeholder="120/80">
                                </div>
                                <div class="col-6 col-md-2 mb-1">
                                    <label class="text-xs text-muted mb-0">Nhiệt độ (°C)</label>
                                    <input type="number" step="0.1" id="vitalTemp" class="form-control form-control-sm" placeholder="36.8">
                                </div>
                                <div class="col-6 col-md-2 mb-1">
                                    <label class="text-xs text-muted mb-0">SpO2 (%)</label>
                                    <input type="number" id="vitalSpo2" class="form-control form-control-sm" placeholder="98">
                                </div>
                                <div class="col-6 col-md-2 mb-1">
                                    <label class="text-xs text-muted mb-0">Cân nặng (kg)</label>
                                    <input type="number" step="0.5" id="vitalWeight" class="form-control form-control-sm" placeholder="60">
                                </div>
                                <div class="col-6 col-md-2 mb-1">
                                    <label class="text-xs text-muted mb-0">Chiều cao (cm)</label>
                                    <input type="number" id="vitalHeight" class="form-control form-control-sm" placeholder="165">
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="form-group">
                        <label class="font-weight-bold text-gray-700">Triệu chứng & Khám thực thể / Sinh hiệu</label>
                        <textarea name="description" id="descriptionTextarea" rows="3" class="form-control" placeholder="Mô tả thể trạng, mạch, huyết áp, nhiệt độ, triệu chứng cơ năng và thực thể..." {{ $isReadOnly ? 'readonly' : '' }}>{{ old('description', $record->description) }}</textarea>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold text-gray-700">Kết luận & Hướng điều trị, dặn dò</label>
                        <textarea name="doctor_conclusion" id="conclusionTextarea" rows="3" class="form-control" placeholder="Chế độ nghỉ ngơi, hẹn tái khám, cảnh báo triệu chứng bất thường..." {{ $isReadOnly ? 'readonly' : '' }}>{{ old('doctor_conclusion', $record->doctor_conclusion) }}</textarea>
                        @if(!$isReadOnly)
                            <div class="mt-2 d-flex flex-wrap align-items-center">
                                <span class="text-xs text-muted mr-2"><i class="far fa-calendar-plus text-primary mr-1"></i>Chèn lịch hẹn tái khám nhanh:</span>
                                <button type="button" class="btn btn-xs btn-outline-info py-0 px-2 mr-1 mb-1 js-followup-chip" style="font-size: 11.5px; border-radius: 12px;" data-days="7">
                                    + Sau 7 ngày ({{ now()->addDays(7)->format('d/m/Y') }})
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-info py-0 px-2 mr-1 mb-1 js-followup-chip" style="font-size: 11.5px; border-radius: 12px;" data-days="14">
                                    + Sau 14 ngày ({{ now()->addDays(14)->format('d/m/Y') }})
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-info py-0 px-2 mr-1 mb-1 js-followup-chip" style="font-size: 11.5px; border-radius: 12px;" data-days="30">
                                    + Sau 1 tháng ({{ now()->addDays(30)->format('d/m/Y') }})
                                </button>
                            </div>
                        @endif
                    </div>

                    @if(!$isReadOnly)
                        <div class="form-group">
                            <label class="font-weight-bold text-gray-700">Ảnh kết quả / Chụp cận lâm sàng (nếu có)</label>
                            <input type="file" name="image" id="image" class="form-control-file">
                        </div>
                    @endif

                    @if($record->image)
                        <div class="mt-2">
                            <span class="text-xs text-muted d-block mb-1">Ảnh hồ sơ đính kèm:</span>
                            <a href="{{ asset('storage/' . $record->image) }}" target="_blank">
                                <img src="{{ asset('storage/' . $record->image) }}" class="img-thumbnail" style="max-height: 120px;">
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Kê đơn thuốc điện tử Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between flex-wrap">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-pills mr-1"></i> Toa thuốc điều trị điện tử
                    </h6>
                    @if(!$isReadOnly)
                        <div class="d-flex align-items-center mt-2 mt-sm-0">
                            <input type="text" id="quickMedFilter" class="form-control form-control-sm mr-2" style="width: 210px;" placeholder="Gõ lọc nhanh tên thuốc...">
                            <button type="button" id="add-prescription-row" class="btn btn-sm btn-primary shadow-sm">
                                <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Thêm thuốc
                            </button>
                        </div>
                    @endif
                </div>
                <div id="stockWarningAlert" class="alert alert-danger m-3 py-2 small d-none">
                    <i class="fas fa-exclamation-triangle mr-1"></i> <span id="stockWarningText">Số lượng kê vượt quá tồn kho hiện tại!</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th style="width: 35%;">Tên thuốc (Kho dược)</th>
                                    <th style="width: 14%;" class="text-center">Số lượng</th>
                                    <th style="width: 14%;" class="text-center">Đơn vị</th>
                                    <th>Liều dùng & Cách sử dụng</th>
                                    @if(!$isReadOnly)
                                        <th class="text-center" style="width: 60px;">Xóa</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody id="prescription-items-body">
                                @php
                                    $allMeds = $allMeds ?? \App\Models\Medicine::orderBy('name')->get();
                                    $existingItems = $record->prescriptionItems ?? collect();
                                @endphp

                                @forelse($existingItems as $idx => $item)
                                    <tr class="prescription-row">
                                        <td>
                                            @if($isReadOnly)
                                                <input type="text" class="form-control bg-light" value="{{ $item->medicine->name ?? 'Thuốc #' . $item->medicine_id }}" readonly>
                                            @else
                                                <select name="prescription_items[{{ $idx }}][medicine_id]" class="form-control medicine-select">
                                                    <option value="">-- Chọn thuốc --</option>
                                                    @foreach($allMeds as $med)
                                                        <option value="{{ $med->id }}" data-stock="{{ $med->stock }}" data-unit="{{ $med->unit }}" data-usage="{{ $med->usage }}" data-price="{{ $med->price ?? 0 }}" {{ $item->medicine_id == $med->id ? 'selected' : '' }}>
                                                            {{ $med->name }} (Tồn: {{ $med->stock }} {{ $med->unit }} • {{ number_format($med->price ?? 0, 0, ',', '.') }}đ)
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @endif
                                        </td>
                                        <td>
                                            <input type="number" name="prescription_items[{{ $idx }}][quantity]" class="form-control text-center quantity-input" value="{{ $item->quantity ?? 1 }}" min="1" {{ $isReadOnly ? 'readonly' : '' }}>
                                        </td>
                                        <td>
                                            <input type="text" name="prescription_items[{{ $idx }}][unit]" class="form-control text-center unit-input" value="{{ $item->unit ?? 'Viên' }}" placeholder="Viên" {{ $isReadOnly ? 'readonly' : '' }}>
                                        </td>
                                        <td>
                                            <input type="text" name="prescription_items[{{ $idx }}][usage]" class="form-control usage-input" value="{{ $item->usage ?? 'Uống sau ăn' }}" placeholder="Cách dùng, ví dụ: 1 viên x 2 lần/ngày" {{ $isReadOnly ? 'readonly' : '' }}>
                                        </td>
                                        @if(!$isReadOnly)
                                            <td class="text-center">
                                                <button type="button" class="btn btn-danger btn-circle btn-sm btn-remove-row" title="Xóa dòng thuốc">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <!-- Hàng đầu tiên mặc định -->
                                    <tr class="prescription-row">
                                        <td>
                                            @if($isReadOnly)
                                                <span class="text-muted small">Không kê đơn thuốc</span>
                                            @else
                                                <select name="prescription_items[0][medicine_id]" class="form-control medicine-select">
                                                    <option value="">-- Chọn thuốc kê đơn --</option>
                                                    @foreach($allMeds as $med)
                                                        <option value="{{ $med->id }}" data-stock="{{ $med->stock }}" data-unit="{{ $med->unit }}" data-usage="{{ $med->usage }}" data-price="{{ $med->price ?? 0 }}">
                                                            {{ $med->name }} (Tồn: {{ $med->stock }} {{ $med->unit }} • {{ number_format($med->price ?? 0, 0, ',', '.') }}đ)
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @endif
                                        </td>
                                        <td>
                                            <input type="number" name="prescription_items[0][quantity]" class="form-control text-center quantity-input" value="1" min="1" {{ $isReadOnly ? 'readonly' : '' }}>
                                        </td>
                                        <td>
                                            <input type="text" name="prescription_items[0][unit]" class="form-control text-center unit-input" value="Viên" placeholder="Viên" {{ $isReadOnly ? 'readonly' : '' }}>
                                        </td>
                                        <td>
                                            <input type="text" name="prescription_items[0][usage]" class="form-control usage-input" value="" placeholder="Ví dụ: Uống 1 viên sau ăn sáng - tối" {{ $isReadOnly ? 'readonly' : '' }}>
                                        </td>
                                        @if(!$isReadOnly)
                                            <td class="text-center">
                                                <button type="button" class="btn btn-danger btn-circle btn-sm btn-remove-row" title="Xóa dòng thuốc">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        @endif
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-light py-2 d-flex justify-content-between align-items-center">
                    <span class="small text-muted"><i class="fas fa-info-circle mr-1"></i>Chọn thuốc để tự động điền đơn vị tính & liều dùng chuẩn</span>
                    <span class="small font-weight-bold text-gray-800">
                        Chi phí thuốc ước tính: <strong class="text-danger" id="prescriptionTotalCost">0 VNĐ</strong>
                    </span>
                </div>
            </div>

            @if(!$isReadOnly)
                <!-- Nút Lưu bệnh án & Hoàn thành khám 1-Click -->
                <div class="d-flex justify-content-end flex-wrap mb-4">
                    <button type="submit" id="mainEmrSaveBtn" class="btn btn-primary btn-icon-split shadow-sm mr-2 mb-2">
                        <span class="icon text-white-50"><i class="fas fa-save"></i></span>
                        <span class="text font-weight-bold">Lưu bệnh án & Đơn thuốc</span>
                    </button>

                    @if($appointment->status !== 'completed')
                        <button type="submit" id="mainEmrCompleteBtn" name="status" value="completed" class="btn btn-success btn-icon-split shadow-sm mb-2" onclick="window.__emrSubmitting = true; return confirm('Xác nhận lưu bệnh án, trừ kho thuốc và hoàn thành buổi khám ngay?');">
                            <span class="icon text-white-50"><i class="fas fa-check-double"></i></span>
                            <span class="text font-weight-bold">Lưu & Hoàn thành khám ngay</span>
                        </button>
                    @endif
                </div>

                {{-- Thanh Công cụ Lâm sàng Cố định Dưới đáy (Sticky Clinical Action Bar) --}}
                <div id="stickyEmrActionBar" class="card shadow-lg border-top-primary d-print-none mb-4" style="position: sticky; bottom: 12px; z-index: 1020; background: rgba(255, 255, 255, 0.96); backdrop-filter: blur(6px); border-left: 4px solid #0284c7;">
                    <div class="card-body py-2 px-3 d-flex align-items-center justify-content-between flex-wrap">
                        <div class="d-flex align-items-center my-1">
                            <span class="badge badge-light border text-gray-800 mr-2 px-2 py-1">
                                <i class="fas fa-user-injured text-primary mr-1"></i>{{ $patientName }}
                            </span>
                            <span id="emrDirtyStatusBadge" class="badge badge-success px-2 py-1">
                                <i class="fas fa-check-circle mr-1"></i>Đã đồng bộ
                            </span>
                        </div>
                        <div class="d-flex align-items-center flex-wrap my-1" style="gap: 8px;">
                            <button type="button" class="btn btn-sm btn-outline-dark font-weight-bold" onclick="window.print();">
                                <i class="fas fa-print mr-1"></i> In Phiếu Khám (A4)
                            </button>
                            <button type="submit" class="btn btn-sm btn-primary font-weight-bold shadow-sm" onclick="window.__emrSubmitting = true;">
                                <i class="fas fa-save mr-1"></i> Lưu nhanh EMR
                            </button>
                            @if($appointment->status !== 'completed')
                                <button type="submit" name="status" value="completed" class="btn btn-sm btn-success font-weight-bold shadow-sm" onclick="window.__emrSubmitting = true; return confirm('Xác nhận lưu bệnh án, trừ kho thuốc và hoàn thành buổi khám ngay?');">
                                    <i class="fas fa-check-double mr-1"></i> Chốt hoàn thành khám
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </form>

        <!-- Bảng danh sách Xét nghiệm đã chỉ định & Kết quả -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-vials mr-1"></i> Kết quả Cận lâm sàng & Xét nghiệm đã chỉ định
                </h6>
                <span class="badge badge-info" id="labTestsCountBadge">{{ $labTests->count() }} chỉ định</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0" width="100%" cellspacing="0">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-center" style="width: 50px;">STT</th>
                                <th>Tên xét nghiệm</th>
                                <th>Khoa / Phòng thực hiện</th>
                                <th class="text-center" style="width: 140px;">Trạng thái</th>
                                <th class="text-center" style="width: 130px;">Kết quả ảnh</th>
                            </tr>
                        </thead>
                        <tbody id="labTestsTableBody">
                            @forelse($labTests as $tIdx => $t)
                                <tr>
                                    <td class="text-center align-middle">{{ $tIdx + 1 }}</td>
                                    <td class="font-weight-bold text-gray-800 align-middle">
                                        {{ $t->test_name }}
                                        @if($t->note)
                                            <div class="small text-muted font-italic">Chỉ định: {{ $t->note }}</div>
                                        @endif
                                    </td>
                                    <td class="align-middle">{{ $t->department->name ?? 'Khoa Xét nghiệm' }}</td>
                                    <td class="text-center align-middle">
                                        @if($t->status === 'completed')
                                            <span class="badge badge-success px-2 py-1"><i class="fas fa-check mr-1"></i>Đã có kết quả</span>
                                        @else
                                            <span class="badge badge-warning text-dark px-2 py-1"><i class="fas fa-clock mr-1"></i>Đang chờ mẫu</span>
                                        @endif
                                    </td>
                                    <td class="text-center align-middle">
                                        @if($t->image)
                                            <a href="{{ asset('storage/' . $t->image) }}" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-2" title="Mở ảnh kết quả">
                                                <i class="fas fa-image mr-1"></i> Xem kết quả
                                            </a>
                                        @else
                                            <span class="text-muted small">Chưa có kết quả</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr id="noLabTestsRow">
                                    <td colspan="5" class="text-center text-muted py-3">Chưa có chỉ định xét nghiệm nào cho hồ sơ bệnh án này.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if(!$isReadOnly)
            <!-- Chỉ định xét nghiệm cận lâm sàng mới Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-vial mr-1"></i> Chỉ định xét nghiệm Cận lâm sàng mới
                    </h6>
                </div>
                <div class="card-body">
                    @php
                        $testTypes = \App\Models\TestType::with('department')->orderBy('name')->get();
                        $allDepartments = \App\Models\Department::orderBy('name')->get();
                    @endphp

                    <div id="labTestAlert" class="mb-3" style="display:none;"></div>

                    <form id="labTestForm" action="{{ route('doctor.lab_tests.store', ['record' => $record->id]) }}" method="POST">
                        @csrf
                        <div class="form-row">
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold text-gray-700">Chọn loại xét nghiệm chuẩn (Nếu có)</label>
                                <select id="testTypeSelect" class="form-control">
                                    <option value="" data-name="" data-dept="">-- Chọn loại xét nghiệm mẫu --</option>
                                    @foreach($testTypes as $tt)
                                        <option value="{{ $tt->id }}" data-name="{{ $tt->name }}" data-dept="{{ $tt->department_id }}">
                                            {{ $tt->name }} @if($tt->department) ({{ $tt->department->name }}) @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold text-gray-700">Tên xét nghiệm chỉ định <span class="text-danger">*</span></label>
                                <input type="text" name="test_name" id="testNameInput" class="form-control" placeholder="Nhập tên xét nghiệm" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold text-gray-700">Khoa / Phòng phụ trách <span class="text-danger">*</span></label>
                                <select name="department_id" id="departmentSelect" class="form-control" required>
                                    <option value="">-- Chọn khoa phòng --</option>
                                    @foreach($allDepartments as $dep)
                                        <option value="{{ $dep->id }}">{{ $dep->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold text-gray-700">Ghi chú lâm sàng cho kỹ thuật viên</label>
                                <input type="text" name="note" class="form-control" placeholder="Ví dụ: Nghi nhiễm trùng, kiểm tra đường huyết đói...">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-info btn-icon-split shadow-sm">
                            <span class="icon text-white-50"><i class="fas fa-paper-plane"></i></span>
                            <span class="text font-weight-bold">Gửi chỉ định xét nghiệm</span>
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>

{{-- BẢN IN ĐƠN THUỐC & PHIẾU KHÁM BỆNH CHUẨN HÀNH CHÍNH Y TẾ (CHỈ HIỆN KHI IN) --}}
<div id="officialMedicalPrintSheet" class="d-none d-print-block p-4" style="color: #000; font-family: 'Times New Roman', Times, serif;">
    <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-3">
        <div>
            <div class="font-weight-bold text-uppercase" style="font-size: 13pt;">SỞ Y TẾ HÀ NỘI</div>
            <div class="font-weight-bold text-uppercase" style="font-size: 14pt;">PHÒNG KHÁM ĐA KHOA 125 THÁI THỊNH</div>
            <div style="font-size: 11pt;">Địa chỉ: Số 125 Thái Thịnh, Quận Đống Đa, Hà Nội • ĐT: 1900 1234</div>
        </div>
        <div class="text-right">
            <div class="font-weight-bold" style="font-size: 12pt;">MÃ HỒ SƠ: #EMR-{{ str_pad($record->id, 4, '0', STR_PAD_LEFT) }}</div>
            <div style="font-size: 11pt;">Mã lịch hẹn: {{ $appointment->appointment_code ?? ('#' . $appointment->id) }}</div>
            <div style="font-size: 11pt;">Ngày khám: {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d/m/Y H:i') }}</div>
        </div>
    </div>

    <div class="text-center my-3">
        <h2 class="font-weight-bold text-uppercase mb-1" style="font-size: 18pt;">PHIẾU KHÁM BỆNH &amp; ĐƠN THUỐC ĐIỀU TRỊ</h2>
        <div style="font-size: 11pt;">Chuyên khoa: <strong>{{ $appointment->doctor->department->name ?? 'Đa khoa' }}</strong></div>
    </div>

    <div class="mb-3" style="font-size: 12pt; line-height: 1.6;">
        <div class="row">
            <div class="col-6">Họ và tên người bệnh: <strong>{{ $patientName }}</strong></div>
            <div class="col-3">Tuổi: <strong>{{ $age }}</strong></div>
            <div class="col-3">Giới tính: <strong>{{ $genderLabel }}</strong></div>
        </div>
        <div class="row">
            <div class="col-6">Số điện thoại: <strong>{{ $phone }}</strong></div>
            <div class="col-6">Mã thẻ BHYT: <strong>{{ $insurance }}</strong></div>
        </div>
        <div>Địa chỉ: {{ $patient->address ?? 'Hà Nội' }}</div>
        <div><strong>Khám lâm sàng &amp; Sinh hiệu:</strong> <span id="printDescriptionText">{{ $record->description ?: 'Tỉnh táo, tiếp xúc tốt' }}</span></div>
        <div><strong>Chẩn đoán xác định (ICD-10):</strong> <strong id="printDiagnosisText">{{ $record->diagnosis ?: 'Chưa cập nhật' }}</strong></div>
    </div>

    <h5 class="font-weight-bold text-uppercase mt-3 mb-2" style="font-size: 13pt;">Chỉ định Thuốc điều trị (Toa thuốc):</h5>
    <table class="table table-bordered" style="font-size: 11.5pt; color: #000;">
        <thead>
            <tr>
                <th style="width: 50px;" class="text-center">STT</th>
                <th>Tên thuốc / Hoạt chất</th>
                <th style="width: 90px;" class="text-center">Số lượng</th>
                <th style="width: 90px;" class="text-center">Đơn vị</th>
                <th>Hướng dẫn sử dụng</th>
            </tr>
        </thead>
        <tbody>
            @forelse($record->prescriptionItems as $pIdx => $pItem)
                <tr>
                    <td class="text-center">{{ $pIdx + 1 }}</td>
                    <td><strong>{{ $pItem->medicine->name ?? ('Thuốc #' . $pItem->medicine_id) }}</strong></td>
                    <td class="text-center">{{ $pItem->quantity }}</td>
                    <td class="text-center">{{ $pItem->unit ?? optional($pItem->medicine)->unit ?? 'Viên' }}</td>
                    <td>{{ $pItem->usage }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">Không kê đơn thuốc</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-3 mb-4" style="font-size: 12pt;">
        <strong>Lời dặn của Bác sĩ &amp; Hẹn tái khám:</strong>
        <div id="printConclusionText">{{ $record->doctor_conclusion ?: 'Uống thuốc đúng liều lượng. Tái khám ngay nếu có triệu chứng bất thường.' }}</div>
    </div>

    <div class="row mt-5" style="font-size: 12pt;">
        <div class="col-6 text-center">
            <div><strong>NGƯỜI BỆNH</strong></div>
            <div class="small font-italic">(Ký và ghi rõ họ tên)</div>
        </div>
        <div class="col-6 text-center">
            <div><em>Hà Nội, ngày {{ now()->format('d') }} tháng {{ now()->format('m') }} năm {{ now()->format('Y') }}</em></div>
            <div class="mt-1"><strong>BÁC SĨ KHÁM BỆNH</strong></div>
            <div style="height: 65px;"></div>
            <div><strong>{{ $appointment->doctor->formatted_name ?? $appointment->doctor->user->name ?? 'BS. Chuyên khoa' }}</strong></div>
        </div>
    </div>
</div>

<style>
@media print {
    body #wrapper #accordionSidebar,
    body #wrapper #content-wrapper nav.topbar,
    body #wrapper footer.sticky-footer,
    .d-sm-flex.align-items-center.justify-content-between.mb-4,
    .row > .col-lg-4,
    .row > .col-lg-8 {
        display: none !important;
    }
    #officialMedicalPrintSheet {
        display: block !important;
    }
}
</style>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Auto-fill test name and department from selected test type
        const sel = document.getElementById('testTypeSelect');
        const nameInput = document.getElementById('testNameInput');
        const deptSel = document.getElementById('departmentSelect');
        if (sel && nameInput && deptSel) {
            sel.addEventListener('change', function () {
                const opt = this.options[this.selectedIndex];
                const n = opt.getAttribute('data-name') || '';
                const d = opt.getAttribute('data-dept') || '';
                if (n) nameInput.value = n;
                if (d) deptSel.value = d;
            });
        }

        // Vitals Builder + Auto BMI Calculator
        const vitalWeight = document.getElementById('vitalWeight');
        const vitalHeight = document.getElementById('vitalHeight');
        const liveBmiBadge = document.getElementById('liveBmiBadge');
        const applyVitalsBtn = document.getElementById('applyVitalsBtn');
        const descTextarea = document.getElementById('descriptionTextarea');

        function calcBmi() {
            if (!vitalWeight || !vitalHeight || !liveBmiBadge) return null;
            const w = parseFloat(vitalWeight.value);
            const h = parseFloat(vitalHeight.value) / 100;
            if (!isNaN(w) && !isNaN(h) && w > 0 && h > 0) {
                const bmi = (w / (h * h)).toFixed(1);
                let label = 'Bình thường';
                if (bmi < 18.5) label = 'Gầy';
                else if (bmi >= 23 && bmi < 25) label = 'Thừa cân';
                else if (bmi >= 25) label = 'Béo phì';
                liveBmiBadge.textContent = `BMI: ${bmi} (${label})`;
                return `${bmi} (${label})`;
            }
            liveBmiBadge.textContent = 'BMI: --';
            return null;
        }

        if (vitalWeight && vitalHeight) {
            vitalWeight.addEventListener('input', calcBmi);
            vitalHeight.addEventListener('input', calcBmi);
        }

        if (applyVitalsBtn && descTextarea) {
            applyVitalsBtn.addEventListener('click', function () {
                const parts = [];
                const pulse = document.getElementById('vitalPulse')?.value;
                const bp = document.getElementById('vitalBp')?.value;
                const temp = document.getElementById('vitalTemp')?.value;
                const spo2 = document.getElementById('vitalSpo2')?.value;
                const w = vitalWeight?.value;
                const h = vitalHeight?.value;
                const bmiStr = calcBmi();

                if (pulse) parts.push(`Mạch: ${pulse} l/p`);
                if (bp) parts.push(`HA: ${bp} mmHg`);
                if (temp) parts.push(`Nhiệt độ: ${temp}°C`);
                if (spo2) parts.push(`SpO2: ${spo2}%`);
                if (w) parts.push(`Cân nặng: ${w}kg`);
                if (h) parts.push(`Chiều cao: ${h}cm`);
                if (bmiStr) parts.push(`BMI: ${bmiStr}`);

                if (!parts.length) return;
                const line = '[Sinh hiệu] ' + parts.join(' | ') + '.';
                const current = descTextarea.value.trim();
                descTextarea.value = current ? (line + '\n' + current) : line;
                descTextarea.focus();
            });
        }

        // Follow-up Date Quick Chips
        const conclusionTextarea = document.getElementById('conclusionTextarea');
        document.querySelectorAll('.js-followup-chip').forEach(btn => {
            btn.addEventListener('click', function () {
                if (!conclusionTextarea) return;
                const days = parseInt(this.getAttribute('data-days') || '7', 10);
                const d = new Date();
                d.setDate(d.getDate() + days);
                const dd = String(d.getDate()).padStart(2, '0');
                const mm = String(d.getMonth() + 1).padStart(2, '0');
                const yyyy = d.getFullYear();
                const text = `Hẹn tái khám sau ${days} ngày (Ngày ${dd}/${mm}/${yyyy}).`;
                const cur = conclusionTextarea.value.trim();
                conclusionTextarea.value = cur ? (cur + ' ' + text) : text;
                conclusionTextarea.focus();
            });
        });

        // AJAX submit for lab test request
        const labForm = document.getElementById('labTestForm');
        const alertBox = document.getElementById('labTestAlert');
        const labTableBody = document.getElementById('labTestsTableBody');
        const noLabRow = document.getElementById('noLabTestsRow');
        const countBadge = document.getElementById('labTestsCountBadge');

        if (labForm && alertBox) {
            labForm.addEventListener('submit', function (e) {
                e.preventDefault();
                alertBox.style.display = 'none';
                const formData = new FormData(labForm);

                fetch(labForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(resp => resp.json())
                .then(data => {
                    alertBox.className = 'alert alert-success shadow-sm';
                    alertBox.innerHTML = '<i class="fas fa-check-circle mr-2"></i>' + (data.message || 'Đã gửi yêu cầu xét nghiệm thành công.');
                    alertBox.style.display = 'block';

                    if (data.lab_test && labTableBody) {
                        if (noLabRow) noLabRow.remove();
                        const currentRows = labTableBody.querySelectorAll('tr').length;
                        const newTr = document.createElement('tr');
                        const deptName = data.lab_test.department ? data.lab_test.department.name : 'Khoa xét nghiệm';
                        const noteText = data.lab_test.note ? '<div class="small text-muted font-italic">Chỉ định: ' + data.lab_test.note + '</div>' : '';

                        newTr.innerHTML = `
                            <td class="text-center align-middle">${currentRows + 1}</td>
                            <td class="font-weight-bold text-gray-800 align-middle">
                                ${data.lab_test.test_name}
                                ${noteText}
                            </td>
                            <td class="align-middle">${deptName}</td>
                            <td class="text-center align-middle">
                                <span class="badge badge-warning text-dark px-2 py-1"><i class="fas fa-clock mr-1"></i>Đang chờ mẫu</span>
                            </td>
                            <td class="text-center align-middle">
                                <span class="text-muted small">Chưa có kết quả</span>
                            </td>
                        `;
                        labTableBody.appendChild(newTr);
                        if (countBadge) {
                            countBadge.textContent = `${currentRows + 1} chỉ định`;
                        }
                    }

                    labForm.reset();
                    if (sel) sel.selectedIndex = 0;
                })
                .catch(err => {
                    alertBox.className = 'alert alert-danger shadow-sm';
                    alertBox.innerHTML = '<i class="fas fa-exclamation-triangle mr-2"></i>Có lỗi khi gửi yêu cầu xét nghiệm.';
                    alertBox.style.display = 'block';
                });
            });
        }

        // Quick diagnosis chips
        const diagInput = document.getElementById('diagnosisInput');
        document.querySelectorAll('.quick-diagnosis-chip').forEach(chip => {
            chip.addEventListener('click', function () {
                if (!diagInput) return;
                const text = this.getAttribute('data-text') || '';
                if (!diagInput.value.trim()) {
                    diagInput.value = text;
                } else if (!diagInput.value.includes(text)) {
                    diagInput.value = diagInput.value.trim() + '; ' + text;
                }
                diagInput.focus();
            });
        });

        // Prescription Dynamic Rows Management + Live Stock Validation + Copy Old Prescription
        const body = document.getElementById('prescription-items-body');
        const addBtn = document.getElementById('add-prescription-row');
        const totalCostEl = document.getElementById('prescriptionTotalCost');
        const stockWarningAlert = document.getElementById('stockWarningAlert');
        const stockWarningText = document.getElementById('stockWarningText');
        const quickMedFilter = document.getElementById('quickMedFilter');
        let index = {{ max(1, count($existingItems ?? [])) }};

        function updatePrescriptionTotal() {
            if (!body || !totalCostEl) return;
            let sum = 0;
            let overStockMed = null;

            body.querySelectorAll('.prescription-row').forEach(row => {
                const sel = row.querySelector('.medicine-select');
                const qty = row.querySelector('.quantity-input');
                if (sel && sel.selectedIndex > 0 && qty) {
                    const opt = sel.options[sel.selectedIndex];
                    const price = parseFloat(opt.getAttribute('data-price') || '0');
                    const stock = parseInt(opt.getAttribute('data-stock') || '999999', 10);
                    const q = parseInt(qty.value || '1', 10);
                    if (!isNaN(price) && !isNaN(q) && q > 0) {
                        sum += price * q;
                    }
                    if (!isNaN(stock) && !isNaN(q) && q > stock) {
                        qty.classList.add('is-invalid', 'border-danger');
                        overStockMed = `${opt.text.split('(')[0].trim()} (Kê: ${q}, Tồn kho chỉ còn: ${stock})`;
                    } else {
                        qty.classList.remove('is-invalid', 'border-danger');
                    }
                }
            });

            if (stockWarningAlert && stockWarningText) {
                if (overStockMed) {
                    stockWarningText.textContent = `Cảnh báo vượt tồn kho: ${overStockMed}. Vui lòng giảm số lượng trước khi lưu!`;
                    stockWarningAlert.classList.remove('d-none');
                } else {
                    stockWarningAlert.classList.add('d-none');
                }
            }

            totalCostEl.textContent = sum.toLocaleString('vi-VN') + ' VNĐ';
        }

        // Quick medicine filter across select dropdowns
        if (quickMedFilter && body) {
            quickMedFilter.addEventListener('input', function () {
                const q = this.value.trim().toLowerCase();
                body.querySelectorAll('.medicine-select').forEach(sel => {
                    Array.from(sel.options).forEach((opt, idx) => {
                        if (idx === 0) return;
                        opt.style.display = (!q || opt.text.toLowerCase().includes(q)) ? '' : 'none';
                    });
                });
            });
        }

        function attachSelectChange(select) {
            if (!select) return;
            select.addEventListener('change', function () {
                const opt = this.options[this.selectedIndex];
                const unit = opt.getAttribute('data-unit');
                const usage = opt.getAttribute('data-usage');
                const row = this.closest('.prescription-row');
                if (row) {
                    const unitInput = row.querySelector('.unit-input');
                    if (unitInput && unit) {
                        unitInput.value = unit;
                    }
                    const usageInput = row.querySelector('.usage-input');
                    if (usageInput && usage && !usageInput.value.trim()) {
                        usageInput.value = usage;
                    }
                }
                updatePrescriptionTotal();
            });
        }

        function attachRemoveEvent(btn) {
            if (!btn || !body) return;
            btn.addEventListener('click', function () {
                const rows = body.querySelectorAll('.prescription-row');
                if (rows.length > 1) {
                    this.closest('.prescription-row').remove();
                } else {
                    const row = this.closest('.prescription-row');
                    const medSel = row.querySelector('.medicine-select');
                    if (medSel) medSel.selectedIndex = 0;
                    const qty = row.querySelector('.quantity-input');
                    if (qty) qty.value = '1';
                    const usage = row.querySelector('.usage-input');
                    if (usage) usage.value = '';
                }
                updatePrescriptionTotal();
            });
        }

        function createPrescriptionRowWithValues(itemData) {
            const rows = body.querySelectorAll('.prescription-row');
            const firstRow = rows[0];
            if (!firstRow) return;
            const newRow = firstRow.cloneNode(true);

            const medSelect = newRow.querySelector('.medicine-select');
            if (medSelect) {
                medSelect.setAttribute('name', `prescription_items[${index}][medicine_id]`);
                medSelect.value = itemData ? String(itemData.medicine_id || '') : '';
                attachSelectChange(medSelect);
            }

            const qtyInput = newRow.querySelector('.quantity-input');
            if (qtyInput) {
                qtyInput.setAttribute('name', `prescription_items[${index}][quantity]`);
                qtyInput.value = itemData ? String(itemData.quantity || 1) : '1';
                qtyInput.classList.remove('is-invalid', 'border-danger');
            }

            const unitInput = newRow.querySelector('.unit-input');
            if (unitInput) {
                unitInput.setAttribute('name', `prescription_items[${index}][unit]`);
                unitInput.value = itemData ? String(itemData.unit || 'Viên') : 'Viên';
            }

            const usageInput = newRow.querySelector('.usage-input');
            if (usageInput) {
                usageInput.setAttribute('name', `prescription_items[${index}][usage]`);
                usageInput.value = itemData ? String(itemData.usage || '') : '';
            }

            body.appendChild(newRow);
            index++;
            attachRemoveEvent(newRow.querySelector('.btn-remove-row'));
        }

        if (body) {
            body.querySelectorAll('.medicine-select').forEach(sel => attachSelectChange(sel));
            body.addEventListener('input', function (e) {
                if (e.target && e.target.classList.contains('quantity-input')) {
                    updatePrescriptionTotal();
                }
            });
            updatePrescriptionTotal();

            if (addBtn) {
                addBtn.addEventListener('click', function () {
                    createPrescriptionRowWithValues(null);
                });
            }

            body.querySelectorAll('.btn-remove-row').forEach(btn => attachRemoveEvent(btn));

            // 1-Click Copy Previous Prescription
            document.querySelectorAll('.js-copy-old-prescription').forEach(copyBtn => {
                copyBtn.addEventListener('click', function () {
                    try {
                        const items = JSON.parse(this.getAttribute('data-items') || '[]');
                        if (!items.length) return;
                        const existingRows = body.querySelectorAll('.prescription-row');
                        // Clear existing rows except the first template row
                        existingRows.forEach((r, rIdx) => {
                            if (rIdx > 0) r.remove();
                        });
                        items.forEach((item, idxItem) => {
                            if (idxItem === 0 && existingRows[0]) {
                                const r0 = existingRows[0];
                                const m0 = r0.querySelector('.medicine-select');
                                if (m0) m0.value = String(item.medicine_id || '');
                                const q0 = r0.querySelector('.quantity-input');
                                if (q0) q0.value = String(item.quantity || 1);
                                const u0 = r0.querySelector('.unit-input');
                                if (u0) u0.value = String(item.unit || 'Viên');
                                const us0 = r0.querySelector('.usage-input');
                                if (us0) us0.value = String(item.usage || '');
                            } else {
                                createPrescriptionRowWithValues(item);
                            }
                        });
                        updatePrescriptionTotal();
                    } catch (e) {}
                });
            });
        }
        // Dirty Form Guard (Cảnh báo bác sĩ khi rời trang mà chưa lưu bệnh án EMR)
        window.__emrIsDirty = false;
        window.__emrSubmitting = false;
        const dirtyBadge = document.getElementById('emrDirtyStatusBadge');
        const mainSaveBtn = document.getElementById('mainEmrSaveBtn');
        if (mainSaveBtn) {
            mainSaveBtn.addEventListener('click', function () {
                window.__emrSubmitting = true;
            });
        }
        document.querySelectorAll('form input, form textarea, form select').forEach(function (inputEl) {
            if (inputEl.id === 'medicineQuickSearch') return;
            inputEl.addEventListener('input', function () {
                window.__emrIsDirty = true;
                if (dirtyBadge) {
                    dirtyBadge.className = 'badge badge-warning text-dark px-2 py-1';
                    dirtyBadge.innerHTML = '<i class="fas fa-pen mr-1"></i>Có thay đổi chưa lưu';
                }
            });
        });
        window.addEventListener('beforeunload', function (e) {
            if (window.__emrIsDirty && !window.__emrSubmitting) {
                e.preventDefault();
                e.returnValue = 'Bạn có thông tin bệnh án / đơn thuốc chưa lưu. Bạn có chắc muốn rời trang?';
            }
        });
    });
</script>
@endpush
