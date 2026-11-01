@extends('layouts.admin')

@section('title', 'Chi tiết lịch hẹn #' . ($appointment->appointment_code ?? $appointment->id))

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800 font-weight-bold">
        <i class="fas fa-calendar-check text-primary mr-2"></i>Chi tiết Lịch hẹn #{{ $appointment->appointment_code ?? str_pad($appointment->id, 6, '0', STR_PAD_LEFT) }}
    </h1>
    <div class="mt-2 mt-sm-0 d-flex align-items-center" style="gap: 8px;">
        <button type="button" onclick="window.print()" class="btn btn-sm btn-outline-primary shadow-sm">
            <i class="fas fa-print fa-sm mr-1"></i> In Phiếu Tiếp Đón &amp; Biên Lai
        </button>
        <a href="{{ route('admin.appointments.index') }}" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50 mr-1"></i> Quay lại danh sách
        </a>
    </div>
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
    $activeAdminApptTab = request('tab', 'detail');
@endphp

<!-- Thanh chuyển màn hình quản trị Lịch hẹn (Tách riêng Hồ sơ Ca khám & Bảng kê Viện phí / Audit Trail) -->
<div class="d-flex flex-wrap mb-3" style="gap: 10px;">
    <a href="{{ route('admin.appointments.show', ['appointment' => $appointment->id, 'tab' => 'detail']) }}"
       class="btn btn-sm {{ $activeAdminApptTab !== 'billing' ? 'btn-primary shadow-sm font-weight-bold' : 'btn-outline-primary bg-white' }} px-3 py-2">
        <i class="fas fa-user-md mr-1"></i> 1. Hồ sơ Người khám, Bác sĩ &amp; Thao tác tiếp đón
    </a>
    <a href="{{ route('admin.appointments.show', ['appointment' => $appointment->id, 'tab' => 'billing']) }}"
       class="btn btn-sm {{ $activeAdminApptTab === 'billing' ? 'btn-primary shadow-sm font-weight-bold' : 'btn-outline-primary bg-white' }} px-3 py-2">
        <i class="fas fa-file-invoice-dollar mr-1"></i> 2. Bảng kê Viện phí Đa tầng BHYT &amp; Nhật ký Audit Trail
    </a>
</div>

<div class="row">
    <!-- Left Column: Patient & Appointment Details -->
    <div class="col-lg-6 mb-4">
        <!-- Patient Card -->
        <div class="card shadow mb-4 {{ $activeAdminApptTab === 'billing' ? 'd-none' : '' }}">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-user-injured mr-1"></i> Thông tin Người khám bệnh
                </h6>
                @if($appointment->booking_for === 'relative')
                    <span class="badge badge-warning text-dark"><i class="fas fa-child mr-1"></i>Đặt cho người thân</span>
                @else
                    <span class="badge badge-primary"><i class="fas fa-user mr-1"></i>Bản thân</span>
                @endif
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-6 mb-2">
                        <small class="text-gray-600 d-block">Họ tên người khám:</small>
                        <strong class="text-gray-900 fs-5">{{ $appointment->patient_display_name }}</strong>
                    </div>
                    <div class="col-sm-6 mb-2">
                        <small class="text-gray-600 d-block">Số điện thoại liên hệ:</small>
                        <strong class="text-gray-900">{{ $appointment->patient->phone ?? 'N/A' }}</strong>
                    </div>
                </div>

                @if($appointment->booking_for === 'relative')
                    <div class="p-3 bg-light rounded mt-2 border">
                        <div class="font-weight-bold text-primary small text-uppercase mb-2">Hồ sơ người thân</div>
                        <p class="mb-1"><strong>Mối quan hệ:</strong> {{ $appointment->relative_relationship ?? 'Người thân' }}</p>
                        <p class="mb-1"><strong>Ngày sinh:</strong> {{ $appointment->relative_dob ? \Carbon\Carbon::parse($appointment->relative_dob)->format('d/m/Y') : 'Chưa rõ' }}</p>
                        <p class="mb-0"><strong>Giới tính:</strong> {{ $appointment->relative_gender ?? 'N/A' }}</p>
                    </div>
                    <div class="small text-muted mt-2">
                        Tài khoản đặt hẹn: <strong>{{ $appointment->patient->name ?? 'N/A' }}</strong> ({{ $appointment->patient->email ?? '' }})
                    </div>
                @else
                    <div class="row mt-2">
                        <div class="col-sm-6 mb-2">
                            <small class="text-gray-600 d-block">Email tài khoản:</small>
                            <span>{{ $appointment->patient->email ?? 'N/A' }}</span>
                        </div>
                        <div class="col-sm-6 mb-2">
                            <small class="text-gray-600 d-block">Địa chỉ:</small>
                            <span>{{ $appointment->patient->address ?? 'Hà Nội' }}</span>
                        </div>
                    </div>
                @endif

                @if($appointment->note)
                    <div class="mt-3">
                        <small class="text-gray-600 d-block">Ghi chú triệu chứng:</small>
                        <div class="p-2 bg-light rounded small text-gray-800">{{ $appointment->note }}</div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Doctor & Schedule Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-stethoscope mr-1"></i> Bác sĩ & Khung giờ khám
                </h6>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-sm-6">
                        <small class="text-gray-600 d-block">Bác sĩ phụ trách:</small>
                        <strong class="text-gray-900">{{ $appointment->doctor->formatted_name ?? $appointment->doctor->user->name ?? 'N/A' }}</strong>
                        <div class="small text-muted">{{ $appointment->doctor->specialization ?? '' }}</div>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-gray-600 d-block">Chuyên khoa:</small>
                        <span class="badge badge-info">{{ $appointment->doctor->department->name ?? 'Đa khoa' }}</span>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-6">
                        <small class="text-gray-600 d-block">Ngày hẹn khám:</small>
                        <strong class="text-primary font-weight-bold">
                            <i class="far fa-calendar-alt mr-1"></i>{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d/m/Y') }}
                        </strong>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-gray-600 d-block">Khung giờ tiếp nhận:</small>
                        <strong class="text-gray-900">
                            <i class="far fa-clock mr-1 text-primary"></i>{{ $appointment->timeSlot ? $appointment->timeSlot->formatted_time : $appointment->medical_examination }}
                        </strong>
                    </div>
                </div>

                <hr>

                @php
                    $costBreakdown = $appointment->calculateCostBreakdown();
                @endphp
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <small class="text-gray-600 d-block">Gói dịch vụ:</small>
                        <strong>{{ $appointment->service->name ?? 'Khám chuyên khoa' }}</strong>
                    </div>
                    <div class="text-right">
                        <small class="text-gray-600 d-block">Phí khám ban đầu:</small>
                        <h5 class="text-success font-weight-bold mb-0">
                            {{ number_format($appointment->total_amount ?? ($appointment->service->price ?? 0), 0, ',', '.') }} VNĐ
                        </h5>
                    </div>
                </div>

                <div class="p-3 bg-light rounded border small mt-2 {{ $activeAdminApptTab === 'billing' ? 'border-primary shadow-sm' : 'd-none' }}">
                    <div class="font-weight-bold text-primary text-uppercase mb-2">
                        <i class="fas fa-file-invoice-dollar mr-1"></i> Bảng kê Viện phí Đa tầng (Khám + CLS + Đơn thuốc & BHYT)
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">1. Công khám lâm sàng:</span>
                        <strong>{{ number_format($costBreakdown['consultation_fee'], 0, ',', '.') }} VNĐ</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">2. Cận lâm sàng ({{ $costBreakdown['lab_test_count'] }} chỉ định xét nghiệm):</span>
                        <strong>{{ number_format($costBreakdown['lab_test_fee'], 0, ',', '.') }} VNĐ</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">3. Tiền thuốc theo đơn EMR:</span>
                        <strong>{{ number_format($costBreakdown['prescription_fee'], 0, ',', '.') }} VNĐ</strong>
                    </div>
                    <div class="d-flex justify-content-between pt-1 border-top font-weight-bold text-dark">
                        <span>Tổng chi phí phát sinh:</span>
                        <span>{{ number_format($costBreakdown['gross_total'], 0, ',', '.') }} VNĐ</span>
                    </div>
                    @if($costBreakdown['has_bhyt'])
                        <div class="d-flex justify-content-between text-info mt-1">
                            <span><i class="fas fa-id-card-alt mr-1"></i>Quỹ BHYT đồng chi trả ({{ $costBreakdown['bhyt_rate_percent'] }}% - Thẻ {{ $costBreakdown['bhyt_number'] }}):</span>
                            <strong>-{{ number_format($costBreakdown['bhyt_covered_amount'], 0, ',', '.') }} VNĐ</strong>
                        </div>
                        <div class="d-flex justify-content-between text-danger font-weight-bold mt-1">
                            <span>Bệnh nhân đồng chi trả (Thực thu BHYT):</span>
                            <span>{{ number_format($costBreakdown['patient_copay_amount'], 0, ',', '.') }} VNĐ</span>
                        </div>
                    @endif
                    <div class="d-flex justify-content-between text-success mt-2 pt-2 border-top">
                        <span>Đã thu tạm ứng / phí khám:</span>
                        <strong>{{ number_format($costBreakdown['paid_amount'] ?? 0, 0, ',', '.') }} VNĐ</strong>
                    </div>
                    @if(($costBreakdown['balance_due'] ?? 0) > 0)
                        <div class="d-flex justify-content-between text-danger font-weight-bold mt-1">
                            <span>Còn cần thu thêm (CLS + Thuốc):</span>
                            <span>{{ number_format($costBreakdown['balance_due'], 0, ',', '.') }} VNĐ</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Status, Action & Audit History -->
    <div class="col-lg-6 mb-4">
        <!-- Status & Operations Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-tasks mr-1"></i> Trạng thái & Thao tác quản trị
                </h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="font-weight-bold text-gray-700">Trạng thái hiện tại:</span>
                    @if($appointment->status === 'pending')
                        <span class="badge badge-warning text-dark px-3 py-2 fs-6">Chờ xác nhận</span>
                    @elseif($appointment->status === 'confirmed')
                        <span class="badge badge-primary px-3 py-2 fs-6">Đã xác nhận</span>
                    @elseif($appointment->status === 'checked_in')
                        <span class="badge badge-info px-3 py-2 fs-6">Đã tiếp nhận tại quầy (QR)</span>
                    @elseif($appointment->status === 'completed')
                        <span class="badge badge-success px-3 py-2 fs-6">Đã hoàn thành khám</span>
                    @elseif($appointment->status === 'cancelled')
                        <span class="badge badge-danger px-3 py-2 fs-6">Đã hủy lịch</span>
                    @else
                        <span class="badge badge-secondary px-3 py-2 fs-6">{{ $appointment->status }}</span>
                    @endif
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap" style="gap: 8px;">
                    <span class="font-weight-bold text-gray-700">Trạng thái thanh toán:</span>
                    @if(($costBreakdown['settlement_status'] ?? '') === 'partially_paid')
                        <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                            <span class="badge badge-warning text-dark px-3 py-2">
                                <i class="fas fa-exclamation-circle mr-1"></i>{{ $costBreakdown['settlement_label'] }}
                            </span>
                            @if($appointment->status !== 'cancelled')
                                <form action="{{ route('admin.appointments.payment', $appointment->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Xác nhận tất toán bổ sung viện phí CLS/Đơn thuốc tại quầy thu ngân?');">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="include_full_clinical_costs" value="1">
                                    <button type="submit" class="btn btn-sm btn-warning text-dark font-weight-bold shadow-sm">
                                        <i class="fas fa-cash-register mr-1"></i> Thu thêm CLS/Thuốc (+{{ number_format($costBreakdown['balance_due'], 0, ',', '.') }}đ)
                                    </button>
                                </form>
                            @endif
                        </div>
                    @elseif(in_array($appointment->payment_status, ['paid', 'success']))
                        <span class="badge badge-success px-3 py-2">
                            <i class="fas fa-check-circle mr-1"></i>Đã tất toán ({{ strtoupper($appointment->payment_method ?? 'Đã thu') }})
                        </span>
                    @else
                        <div class="d-flex align-items-center">
                            <span class="badge badge-secondary px-3 py-2 mr-2">Chưa thanh toán</span>
                            @if($appointment->status !== 'cancelled')
                                <form action="{{ route('admin.appointments.payment', $appointment->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Xác nhận đã thu viện phí tại quầy tiếp đón?');">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="include_full_clinical_costs" value="1">
                                    <button type="submit" class="btn btn-sm btn-success shadow-sm">
                                        <i class="fas fa-cash-register mr-1"></i> Thu tiền tại quầy
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endif
                </div>

                @if(in_array($appointment->payment_status, ['paid', 'success']) && ($appointment->status === 'cancelled' || in_array($appointment->refund_status, ['pending', 'refunded'])))
                    <div class="p-3 rounded border mb-3 {{ $appointment->refund_status === 'refunded' ? 'bg-info-light border-info' : 'bg-warning-light border-warning' }}" style="background-color: {{ $appointment->refund_status === 'refunded' ? '#f0f9ff' : '#fffbeb' }};">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <div>
                                <span class="font-weight-bold text-gray-800 d-block small text-uppercase mb-1">
                                    <i class="fas fa-undo-alt mr-1 {{ $appointment->refund_status === 'refunded' ? 'text-info' : 'text-warning' }}"></i>Đối soát hoàn tiền viện phí:
                                </span>
                                @if($appointment->refund_status === 'refunded')
                                    <span class="badge badge-info px-3 py-2">
                                        <i class="fas fa-check-double mr-1"></i>Đã hoàn tiền ({{ number_format($appointment->refund_amount ?? $appointment->total_amount ?? $appointment->total ?? ($appointment->service->price ?? 0), 0, ',', '.') }} VNĐ)
                                    </span>
                                @else
                                    <span class="badge badge-warning text-dark px-3 py-2">
                                        <i class="fas fa-hourglass-half mr-1"></i>Chờ kế toán hoàn tiền ({{ number_format($appointment->total_amount ?? $appointment->total ?? ($appointment->service->price ?? 0), 0, ',', '.') }} VNĐ)
                                    </span>
                                @endif
                            </div>

                            @if($appointment->refund_status !== 'refunded')
                                <form action="{{ route('admin.appointments.refund', $appointment->id) }}" method="POST" class="mt-2 mt-sm-0" onsubmit="return confirm('Xác nhận đã hoàn trả viện phí cho bệnh nhân này?');">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="refund_note" value="Quản trị viên xác nhận đã hoàn trả 100% viện phí cho bệnh nhân">
                                    <button type="submit" class="btn btn-sm btn-warning text-dark font-weight-bold shadow-sm">
                                        <i class="fas fa-undo-alt mr-1"></i> Xác nhận hoàn tiền
                                    </button>
                                </form>
                            @endif
                        </div>
                        @if($appointment->refund_note)
                            <div class="small text-muted mt-2 pt-2 border-top">
                                <i class="fas fa-comment-dollar mr-1"></i>{{ $appointment->refund_note }}
                            </div>
                        @endif
                    </div>
                @endif

                @if($appointment->checked_in_at)
                    <div class="small text-success mb-2">
                        <i class="fas fa-qrcode mr-1"></i> Đã quét mã QR tiếp đón lúc: <strong>{{ \Carbon\Carbon::parse($appointment->checked_in_at)->format('H:i - d/m/Y') }}</strong>
                    </div>
                @endif

                {{-- Thao tác nhanh 1-Click cho Lễ tân / Quản trị --}}
                <div class="p-3 bg-light rounded border mb-3 {{ $activeAdminApptTab === 'billing' ? 'd-none' : '' }}">
                    <div class="small font-weight-bold text-primary text-uppercase mb-2">
                        <i class="fas fa-bolt mr-1 text-warning"></i> Thao tác nghiệp vụ nhanh (1-Click)
                    </div>
                    <div class="d-flex flex-wrap" style="gap: 8px;">
                        @if($appointment->status === 'pending')
                            <form action="{{ route('admin.appointments.status', $appointment->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="confirmed">
                                <input type="hidden" name="note" value="Admin xác nhận lịch hẹn nhanh">
                                <button type="submit" class="btn btn-sm btn-primary shadow-sm">
                                    <i class="fas fa-check mr-1"></i> Duyệt lịch ngay
                                </button>
                            </form>
                        @endif

                        @if(in_array($appointment->status, ['pending', 'confirmed']))
                            <form action="{{ route('admin.appointments.status', $appointment->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="checked_in">
                                <input type="hidden" name="note" value="Tiếp nhận bệnh nhân check-in tại quầy lễ tân">
                                <button type="submit" class="btn btn-sm btn-info shadow-sm">
                                    <i class="fas fa-qrcode mr-1"></i> Check-in Tiếp đón
                                </button>
                            </form>
                        @endif

                        @if(in_array($appointment->status, ['confirmed', 'checked_in']))
                            <form action="{{ route('admin.appointments.status', $appointment->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Xác nhận hoàn tất ca khám này?');">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="completed">
                                <input type="hidden" name="note" value="Hoàn thành ca khám">
                                <button type="submit" class="btn btn-sm btn-success shadow-sm">
                                    <i class="fas fa-check-double mr-1"></i> Hoàn thành khám
                                </button>
                            </form>
                        @endif

                        @if($appointment->medicalRecord)
                            <a href="{{ route('admin.medical-records.show', $appointment->medicalRecord->id) }}" class="btn btn-sm btn-outline-primary shadow-sm">
                                <i class="fas fa-file-medical-alt mr-1"></i> Xem Bệnh án EMR
                            </a>
                        @endif
                    </div>
                </div>

                <hr>

                <!-- Update Status Form -->
                <form action="{{ route('admin.appointments.status', $appointment->id) }}" method="POST" class="{{ $activeAdminApptTab === 'billing' ? '' : 'd-none' }}">
                    @csrf
                    @method('PATCH')
                    <div class="form-group">
                        <label class="font-weight-bold text-gray-700">Cập nhật trạng thái lịch:</label>
                        <select name="status" class="form-control">
                            <option value="pending" {{ $appointment->status === 'pending' ? 'selected' : '' }}>Chờ duyệt (Pending)</option>
                            <option value="confirmed" {{ $appointment->status === 'confirmed' ? 'selected' : '' }}>Xác nhận lịch (Confirmed)</option>
                            <option value="checked_in" {{ $appointment->status === 'checked_in' ? 'selected' : '' }}>Tiếp nhận tại quầy (Checked-in)</option>
                            <option value="completed" {{ $appointment->status === 'completed' ? 'selected' : '' }}>Hoàn thành ca khám (Completed)</option>
                            <option value="no_show" {{ $appointment->status === 'no_show' ? 'selected' : '' }}>Vắng mặt / Bỏ hẹn (No-Show)</option>
                            <option value="cancelled" {{ $appointment->status === 'cancelled' ? 'selected' : '' }}>Hủy lịch khám (Cancelled)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold text-gray-700">Ghi chú quản trị / Lý do:</label>
                        <input type="text" name="note" class="form-control" placeholder="Ghi chú lý do thay đổi trạng thái (lưu vào Audit Trail)">
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-sync-alt mr-1"></i> Lưu thay đổi trạng thái
                    </button>
                </form>
            </div>
        </div>

        <!-- Audit Trail Card -->
        <div class="card shadow mb-4 {{ $activeAdminApptTab === 'billing' ? '' : 'd-none' }}">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-history mr-1"></i> Lịch sử thay đổi trạng thái (Audit Trail)
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0" width="100%" cellspacing="0">
                        <thead class="bg-light">
                            <tr>
                                <th>Thời gian</th>
                                <th>Chuyển trạng thái</th>
                                <th>Người thực hiện</th>
                                <th>Ghi chú</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($appointment->statusHistories as $history)
                                <tr>
                                    <td class="small">{{ \Carbon\Carbon::parse($history->created_at)->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <span class="badge badge-light border">{{ $history->from_status ?? 'Mới' }}</span>
                                        <i class="fas fa-arrow-right text-muted mx-1"></i>
                                        <span class="badge badge-primary">{{ $history->to_status }}</span>
                                    </td>
                                    <td class="small">{{ $history->changedByUser->name ?? 'Hệ thống' }}</td>
                                    <td class="small text-muted">{{ $history->note ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted small py-3">Chưa có lịch sử thay đổi trạng thái.</td>
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
