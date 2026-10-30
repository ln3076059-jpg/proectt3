@extends('layouts.admin')

@section('title', 'Chi tiết hồ sơ bệnh án')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-1 text-gray-800 font-weight-bold">
            <i class="fas fa-file-medical-alt text-primary mr-2"></i>Hồ sơ bệnh án điện tử: #EMR-{{ str_pad($medicalRecord->id, 5, '0', STR_PAD_LEFT) }}
        </h1>
        <p class="text-muted small mb-0">Thông tin khám lâm sàng, kết luận bác sĩ, đơn thuốc và kết quả cận lâm sàng</p>
    </div>
    <div class="mt-2 mt-sm-0">
        <button onclick="window.print()" class="btn btn-sm btn-primary shadow-sm mr-2">
            <i class="fas fa-print fa-sm text-white-50 mr-1"></i> In Bệnh án & Đơn thuốc
        </button>
        <a href="{{ route('admin.medical-records.index') }}" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50 mr-1"></i> Quay lại danh sách
        </a>
    </div>
</div>

@php
    $patient = $medicalRecord->patient ?? optional($medicalRecord->appointment)->patient;
    $doctor = optional($medicalRecord->appointment)->doctor;
    $presItems = $medicalRecord->prescriptionItems ?? collect();
    $labTests = $medicalRecord->labTests ?? collect();
@endphp

<div class="row">
    <!-- Left Column: Patient & Doctor Info -->
    <div class="col-lg-4 mb-4">
        <!-- Patient Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-user-injured mr-1"></i> Thông tin bệnh nhân
                </h6>
                @if($patient)
                    <a href="{{ route('admin.patients.show', $patient->id) }}" class="btn btn-sm btn-outline-primary py-0 px-2">Hồ sơ</a>
                @endif
            </div>
            <div class="card-body">
                <p class="mb-2"><strong class="text-gray-700">Họ tên:</strong> <span class="font-weight-bold text-primary">{{ $patient->name ?? '-' }}</span></p>
                <p class="mb-2"><strong class="text-gray-700">Số điện thoại:</strong> {{ $patient->phone ?: '-' }}</p>
                <p class="mb-2"><strong class="text-gray-700">Giới tính:</strong> {{ in_array($patient->gender ?? '', ['male', 'Nam']) ? 'Nam' : (in_array($patient->gender ?? '', ['female', 'Nữ']) ? 'Nữ' : 'Khác') }}</p>
                <p class="mb-2"><strong class="text-gray-700">Ngày sinh:</strong> {{ !empty($patient->birthdate) ? \Carbon\Carbon::parse($patient->birthdate)->format('d/m/Y') : '-' }}</p>
                <p class="mb-2"><strong class="text-gray-700">Mã BHYT:</strong> <span class="badge badge-success">{{ $patient->insurance_number ?: 'Chưa cập nhật' }}</span></p>
                <p class="mb-0"><strong class="text-gray-700">Địa chỉ:</strong> {{ $patient->address ?: 'Chưa cập nhật' }}</p>
            </div>
        </div>

        <!-- Doctor Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-user-md mr-1"></i> Bác sĩ điều trị & Ca khám
                </h6>
            </div>
            <div class="card-body">
                <p class="mb-2"><strong class="text-gray-700">Bác sĩ:</strong> <span class="font-weight-bold text-gray-800">{{ $doctor->formatted_name ?? $doctor->user->name ?? 'N/A' }}</span></p>
                <p class="mb-2"><strong class="text-gray-700">Khoa chuyên môn:</strong> <span class="badge badge-info">{{ $doctor->department->name ?? 'Đa khoa' }}</span></p>
                <p class="mb-2"><strong class="text-gray-700">Gói dịch vụ:</strong> {{ optional($medicalRecord->appointment)->service->name ?? 'Khám chuyên khoa' }}</p>
                <p class="mb-2"><strong class="text-gray-700">CCHN:</strong> {{ $doctor->license_number ?: 'Đang cập nhật' }}</p>
                <p class="mb-0"><strong class="text-gray-700">Ngày khám:</strong> {{ \Carbon\Carbon::parse($medicalRecord->created_at ?? optional($medicalRecord->appointment)->appointment_date)->format('d/m/Y H:i') }}</p>
            </div>
        </div>
    </div>

    <!-- Right Column: Medical Examination & Diagnosis & Prescription -->
    <div class="col-lg-8 mb-4">
        <!-- Diagnosis Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-stethoscope mr-1"></i> Khám lâm sàng & Chẩn đoán
                </h6>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="font-weight-bold text-gray-700">Chẩn đoán xác định của bác sĩ (ICD-10)</label>
                    <div class="p-3 bg-light rounded border-left-danger text-danger font-weight-bold">{{ $medicalRecord->diagnosis ?: 'Đang theo dõi phác đồ điều trị' }}</div>
                </div>

                @php
                    $adminExtractedVitals = [];
                    if (preg_match('/\[Sinh hiệu:\s*([^\]]+)\]/u', (string) $medicalRecord->description, $vMatch)) {
                        foreach (explode('|', $vMatch[1]) as $vPart) {
                            if (trim($vPart) !== '') {
                                $adminExtractedVitals[] = trim($vPart);
                            }
                        }
                    }
                @endphp
                @if(count($adminExtractedVitals) > 0)
                    <div class="form-group">
                        <label class="font-weight-bold text-gray-700">
                            <i class="fas fa-heartbeat text-danger mr-1"></i> Chỉ số sinh hiệu ghi nhận
                        </label>
                        <div class="d-flex flex-wrap" style="gap: 6px;">
                            @foreach($adminExtractedVitals as $vBadge)
                                <span class="badge badge-info px-3 py-2" style="font-size: 12px;">{{ $vBadge }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="form-group">
                    <label class="font-weight-bold text-gray-700">Triệu chứng & Khám thực thể</label>
                    <div class="p-3 bg-light rounded text-gray-800" style="white-space: pre-line;">{{ $medicalRecord->description ?: 'Không có ghi nhận triệu chứng bất thường.' }}</div>
                </div>

                <div class="form-group mb-0">
                    <label class="font-weight-bold text-gray-700">Kết luận & Hướng dẫn điều trị / Lời dặn</label>
                    <div class="p-3 bg-light rounded text-gray-800" style="white-space: pre-line;">{{ $medicalRecord->doctor_conclusion ?: 'Tuân thủ đơn thuốc và tái khám khi có dấu hiệu bất thường.' }}</div>
                </div>

                @if($medicalRecord->image)
                    <div class="form-group mt-3 mb-0">
                        <label class="font-weight-bold text-gray-700">Ảnh kết quả lâm sàng đính kèm</label>
                        <div>
                            <a href="{{ asset('storage/' . $medicalRecord->image) }}" target="_blank">
                                <img src="{{ asset('storage/' . $medicalRecord->image) }}" class="img-thumbnail" style="max-height: 180px;">
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Prescriptions Table Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-pills mr-1"></i> Đơn thuốc điều trị ngoại trú
                </h6>
                <span class="badge badge-primary">{{ $presItems->count() }} loại thuốc</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0" width="100%" cellspacing="0">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-center" style="width: 50px;">STT</th>
                                <th>Tên thuốc & Hoạt chất</th>
                                <th style="width: 130px;" class="text-center">Số lượng</th>
                                <th>Liều dùng & Hướng dẫn sử dụng</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($presItems as $pIdx => $pres)
                                <tr>
                                    <td class="text-center align-middle">{{ $pIdx + 1 }}</td>
                                    <td class="align-middle">
                                        <div class="font-weight-bold text-primary">{{ $pres->medicine->name ?? 'Thuốc #' . $pres->medicine_id }}</div>
                                        @if(!empty($pres->medicine->generic_name))
                                            <small class="text-muted">{{ $pres->medicine->generic_name }} {{ $pres->medicine->strength ?? '' }}</small>
                                        @endif
                                    </td>
                                    <td class="text-center align-middle font-weight-bold">
                                        {{ $pres->quantity }} {{ $pres->unit ?? ($pres->medicine->unit ?? 'Viên') }}
                                    </td>
                                    <td class="align-middle text-gray-800">
                                        {{ $pres->usage ?: ($pres->dosage ?: 'Theo chỉ định bác sĩ') }}
                                        @if($pres->note)
                                            <div class="small text-muted font-italic">Ghi chú: {{ $pres->note }}</div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">Không có đơn thuốc nào được kê cho ca khám này.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Lab Tests Card -->
        @if($labTests->isNotEmpty())
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-vial mr-1"></i> Kết quả Xét nghiệm & Cận lâm sàng
                    </h6>
                    <span class="badge badge-info">{{ $labTests->count() }} chỉ định</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0" width="100%" cellspacing="0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="text-center" style="width: 50px;">STT</th>
                                    <th>Tên xét nghiệm & Nhận xét</th>
                                    <th>Khoa phụ trách</th>
                                    <th class="text-center" style="width: 130px;">Trạng thái</th>
                                    <th class="text-center" style="width: 150px;">Kết quả</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($labTests as $tIdx => $t)
                                    <tr>
                                        <td class="text-center align-middle">{{ $tIdx + 1 }}</td>
                                        <td class="align-middle">
                                            <div class="font-weight-bold text-gray-800">{{ $t->test_name }}</div>
                                            @if($t->note)
                                                <small class="text-muted d-block">{{ $t->note }}</small>
                                            @endif
                                        </td>
                                        <td class="align-middle">{{ $t->department->name ?? 'Xét nghiệm' }}</td>
                                        <td class="text-center align-middle">
                                            @if($t->status === 'completed' || !empty($t->image))
                                                <span class="badge badge-success">Đã có kết quả</span>
                                            @else
                                                <span class="badge badge-warning text-dark">Đang phân tích</span>
                                            @endif
                                        </td>
                                        <td class="text-center align-middle">
                                            @if($t->image)
                                                <a href="{{ asset('storage/' . $t->image) }}" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-2 mb-1">
                                                    <i class="fas fa-image mr-1"></i> Ảnh chính
                                                </a>
                                            @endif
                                            @if(!empty($t->images) && is_array($t->images))
                                                @foreach($t->images as $idxImg => $extraImg)
                                                    <a href="{{ asset('storage/' . $extraImg) }}" target="_blank" class="btn btn-sm btn-outline-info py-0 px-2 mb-1">
                                                        Ảnh #{{ $idxImg + 2 }}
                                                    </a>
                                                @endforeach
                                            @endif
                                            @if(empty($t->image) && empty($t->images))
                                                <a href="{{ route('admin.lab_tests.upload', $t->id) }}" class="btn btn-sm btn-warning text-dark py-0 px-2">
                                                    <i class="fas fa-upload mr-1"></i> Trả KQ
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
