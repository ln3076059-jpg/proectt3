@extends('layouts.admin')

@section('title', 'Quản lý lịch hẹn khám')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800 font-weight-bold">
        <i class="fas fa-calendar-check text-primary mr-2"></i>Quản lý Lịch hẹn khám bệnh
    </h1>
    <div class="btn-group btn-group-sm">
        <a href="{{ route('admin.appointments.index') }}" class="btn {{ request('status') === null ? 'btn-primary' : 'btn-outline-primary' }}">Tất cả</a>
        <a href="{{ route('admin.appointments.index', ['status' => 'pending']) }}" class="btn {{ request('status') === 'pending' ? 'btn-warning text-dark' : 'btn-outline-warning text-dark' }}">Chờ duyệt</a>
        <a href="{{ route('admin.appointments.index', ['status' => 'confirmed']) }}" class="btn {{ request('status') === 'confirmed' ? 'btn-primary' : 'btn-outline-primary' }}">Đã duyệt</a>
        <a href="{{ route('admin.appointments.index', ['status' => 'checked_in']) }}" class="btn {{ request('status') === 'checked_in' ? 'btn-info' : 'btn-outline-info' }}">Tiếp nhận (QR)</a>
        <a href="{{ route('admin.appointments.index', ['status' => 'completed']) }}" class="btn {{ request('status') === 'completed' ? 'btn-success' : 'btn-outline-success' }}">Đã khám</a>
        <a href="{{ route('admin.appointments.index', ['status' => 'cancelled']) }}" class="btn {{ request('status') === 'cancelled' ? 'btn-danger' : 'btn-outline-danger' }}">Đã hủy</a>
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

<!-- Kiosk QR Check-in 1-Click Card -->
<div class="card shadow mb-4 border-left-info">
    <div class="card-body py-3">
        <form action="{{ route('admin.appointments.quick_checkin') }}" method="POST" id="quickCheckinForm" class="form-row align-items-center">
            @csrf
            <div class="col-lg-3 mb-2 mb-lg-0">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center mr-3" style="width: 42px; height: 42px; flex-shrink: 0;">
                        <i class="fas fa-qrcode fa-lg"></i>
                    </div>
                    <div>
                        <h6 class="m-0 font-weight-bold text-info">Quầy Tiếp Đón QR (1-Chạm)</h6>
                        <small class="text-muted">Máy quét Barcode 2D / Nhập mã phiếu</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 mb-2 mb-lg-0">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-white text-info border-info"><i class="fas fa-barcode"></i></span>
                    </div>
                    <input type="text" name="qr_code" id="qrCodeInput" class="form-control border-info font-weight-bold" value="{{ old('qr_code') }}" placeholder="Quét QR (PK125THAITHINH|...) hoặc nhập mã phiếu TT-2026..." required autocomplete="off">
                </div>
            </div>
            <div class="col-lg-4 d-flex" style="gap: 8px;">
                <button type="submit" class="btn btn-info font-weight-bold flex-fill shadow-sm">
                    <i class="fas fa-check-circle mr-1"></i> Tiếp đón (Check-in) ngay
                </button>
                <button type="button" class="btn btn-outline-primary font-weight-bold shadow-sm" data-toggle="modal" data-target="#qrScannerModal" title="Quét bằng Camera hoặc tải ảnh phiếu QR">
                    <i class="fas fa-camera mr-1"></i> Quét ảnh / Camera
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Search Filter Card -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-search mr-1"></i> Tìm kiếm lịch hẹn nâng cao
        </h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.appointments.index') }}" method="GET" class="form-row align-items-end">
            <div class="col-md-3 mb-2">
                <label class="font-weight-bold text-gray-700 small">TỪ KHÓA TÌM KIẾM</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Mã lịch, tên bệnh nhân, SĐT...">
            </div>
            <div class="col-md-2 mb-2">
                <label class="font-weight-bold text-gray-700 small">NGÀY HẸN KHÁM</label>
                <input type="date" name="date" class="form-control" value="{{ request('date') }}">
            </div>
            <div class="col-md-3 mb-2">
                <label class="font-weight-bold text-gray-700 small">TRẠNG THÁI LỊCH</label>
                <select name="status" class="form-control">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
                    <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Đã xác nhận</option>
                    <option value="checked_in" {{ request('status') === 'checked_in' ? 'selected' : '' }}>Đã tiếp nhận (QR)</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Đã hoàn thành</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <label class="font-weight-bold text-gray-700 small">THANH TOÁN</label>
                <select name="payment_status" class="form-control">
                    <option value="">-- Tất cả --</option>
                    <option value="success" {{ request('payment_status') === 'success' ? 'selected' : '' }}>Đã thu phí</option>
                    <option value="failed" {{ request('payment_status') === 'failed' ? 'selected' : '' }}>Chưa thu phí</option>
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-search mr-1"></i> Lọc
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Appointments Table (5 CỘT BAO GỒM 1 CỘT CHỨC NĂNG) -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-wrap align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-table mr-1"></i> Danh sách hồ sơ lịch hẹn khám bệnh
        </h6>
        <div class="d-flex align-items-center mt-2 mt-sm-0" style="gap: 6px;">
            <a href="{{ route('admin.appointments.index', ['date' => now()->toDateString()]) }}" class="btn btn-xs btn-outline-info py-1 px-2">
                <i class="fas fa-calendar-day mr-1"></i>Lịch hôm nay ({{ now()->format('d/m') }})
            </a>
            <a href="{{ route('admin.appointments.index', ['date' => now()->addDay()->toDateString()]) }}" class="btn btn-xs btn-outline-secondary py-1 px-2">
                Ngày mai ({{ now()->addDay()->format('d/m') }})
            </a>
            <button type="button" id="exportAdminApptsCsvBtn" class="btn btn-xs btn-outline-success py-1 px-2">
                <i class="fas fa-file-csv mr-1"></i>Xuất CSV danh sách
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered dataTable no-datatable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th style="width: 150px;">Mã & Ngày khám</th>
                        <th>Bệnh nhân / Người thân</th>
                        <th>Bác sĩ & Chuyên khoa</th>
                        <th style="width: 180px;">Trạng thái & Viện phí</th>
                        <th class="text-center" style="width: 170px;">Chức năng</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($appointments as $app)
                        <tr>
                            <!-- Cột 1: Mã & Thời gian -->
                            <td>
                                <span class="font-weight-bold text-primary">#{{ $app->appointment_code ?? str_pad($app->id, 6, '0', STR_PAD_LEFT) }}</span>
                                <div class="small text-gray-800">
                                    <i class="far fa-calendar-alt text-primary mr-1"></i>{{ \Carbon\Carbon::parse($app->appointment_date)->format('d/m/Y') }}
                                </div>
                                <div class="small text-muted">
                                    <i class="far fa-clock mr-1"></i>{{ $app->timeSlot ? $app->timeSlot->formatted_time : $app->medical_examination }}
                                </div>
                            </td>

                            <!-- Cột 2: Bệnh nhân -->
                            <td>
                                <div class="font-weight-bold text-gray-800">{{ $app->patient_display_name }}</div>
                                <div class="small text-gray-600">
                                    <i class="fas fa-phone mr-1 text-success"></i>{{ $app->patient->phone ?? 'Chưa có SĐT' }}
                                </div>
                                @if($app->booking_for === 'relative')
                                    <span class="badge badge-warning text-dark"><i class="fas fa-child mr-1"></i>{{ $app->relative_relationship ?? 'Người thân' }}</span>
                                @endif
                            </td>

                            <!-- Cột 3: Bác sĩ & Khoa -->
                            <td>
                                @php
                                    $docName = $app->doctor->user->name ?? 'N/A';
                                    if ($docName !== 'N/A' && !\Illuminate\Support\Str::startsWith($docName, 'BS.')) {
                                        $docName = 'BS. ' . $docName;
                                    }
                                @endphp
                                <div class="font-weight-bold text-gray-800">
                                    <i class="fas fa-user-md mr-1 text-primary"></i>{{ $docName }}
                                </div>
                                <div class="small text-muted">{{ $app->doctor->department->name ?? 'Đa khoa' }}</div>
                                <div class="small text-info font-weight-bold">{{ $app->service->name ?? 'Gói khám' }}</div>
                            </td>

                            <!-- Cột 4: Trạng thái & Viện phí -->
                            <td>
                                <div>
                                    @if($app->status === 'pending')
                                        <span class="badge badge-warning text-dark">Chờ duyệt</span>
                                    @elseif($app->status === 'confirmed')
                                        <span class="badge badge-primary">Đã xác nhận</span>
                                    @elseif($app->status === 'checked_in')
                                        <span class="badge badge-info">Đã tiếp nhận (QR)</span>
                                    @elseif($app->status === 'completed')
                                        <span class="badge badge-success">Đã hoàn thành</span>
                                    @elseif($app->status === 'cancelled')
                                        <span class="badge badge-danger">Đã hủy</span>
                                    @else
                                        <span class="badge badge-secondary">{{ $app->status }}</span>
                                    @endif

                                    @if($app->payment_status === 'success')
                                        <span class="badge badge-success ml-1"><i class="fas fa-check-circle"></i> Đã thu</span>
                                        @if($app->refund_status === 'refunded')
                                            <span class="badge badge-info ml-1"><i class="fas fa-undo-alt"></i> Đã hoàn tiền</span>
                                        @elseif($app->refund_status === 'pending' || $app->status === 'cancelled')
                                            <span class="badge badge-warning text-dark ml-1"><i class="fas fa-hourglass-half"></i> Chờ hoàn tiền</span>
                                        @endif
                                    @else
                                        <span class="badge badge-light border text-muted ml-1">Chưa thu</span>
                                    @endif
                                </div>
                                <div class="small font-weight-bold text-success mt-1">
                                    {{ number_format($app->total_amount ?? $app->total ?? ($app->service->price ?? 0), 0, ',', '.') }} VNĐ
                                </div>
                            </td>

                            <!-- Cột 5: Chức năng -->
                            <td class="text-center">
                                <a href="{{ route('admin.appointments.show', $app->id) }}" class="btn btn-info btn-circle btn-sm mr-1" title="Xem chi tiết lịch hẹn">
                                    <i class="fas fa-eye"></i>
                                </a>

                                <!-- Xác nhận thu tiền tại quầy -->
                                @if($app->payment_status !== 'success' && $app->status !== 'cancelled')
                                    <form action="{{ route('admin.appointments.payment', $app->id) }}" method="POST" class="d-inline mr-1" onsubmit="return confirm('Xác nhận đã thu viện phí tại quầy cho lịch hẹn này?');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-warning btn-circle btn-sm text-dark" title="Xác nhận thu viện phí tại quầy">
                                            <i class="fas fa-dollar-sign"></i>
                                        </button>
                                    </form>
                                @endif

                                <!-- Xác nhận hoàn tiền khi lịch đã thu phí bị hủy -->
                                @if($app->payment_status === 'success' && ($app->status === 'cancelled' || $app->refund_status === 'pending') && $app->refund_status !== 'refunded')
                                    <form action="{{ route('admin.appointments.refund', $app->id) }}" method="POST" class="d-inline mr-1" onsubmit="return confirm('Xác nhận đã hoàn trả viện phí cho bệnh nhân này?');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-warning btn-circle btn-sm text-dark" title="Xác nhận hoàn tiền viện phí">
                                            <i class="fas fa-undo-alt"></i>
                                        </button>
                                    </form>
                                @endif

                                <!-- Phê duyệt -->
                                @if($app->status === 'pending')
                                    <form action="{{ route('admin.appointments.status', $app->id) }}" method="POST" class="d-inline mr-1">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="confirmed">
                                        <button type="submit" class="btn btn-success btn-circle btn-sm" title="Duyệt lịch hẹn">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                @endif

                                <!-- Tiếp đón Check-in QR 1-Click tại quầy lễ tân -->
                                @if($app->status === 'confirmed')
                                    <form action="{{ route('admin.appointments.status', $app->id) }}" method="POST" class="d-inline mr-1" onsubmit="return confirm('Xác nhận bệnh nhân đã có mặt tại quầy tiếp đón (Check-in)?');">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="checked_in">
                                        <button type="submit" class="btn btn-primary btn-circle btn-sm" title="Tiếp nhận bệnh nhân (Check-in QR)">
                                            <i class="fas fa-qrcode"></i>
                                        </button>
                                    </form>
                                @endif

                                <!-- Hủy lịch kèm nhập lý do qua Modal -->
                                @if($app->status !== 'completed' && $app->status !== 'cancelled')
                                    <button type="button"
                                            class="btn btn-danger btn-circle btn-sm btn-open-cancel-modal"
                                            data-toggle="modal"
                                            data-target="#adminCancelModal"
                                            data-action="{{ route('admin.appointments.status', $app->id) }}"
                                            data-code="{{ $app->appointment_code ?? ('#' . $app->id) }}"
                                            data-patient="{{ $app->patient_display_name }}"
                                            title="Hủy lịch hẹn (Nhập lý do)">
                                        <i class="fas fa-times"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Chưa có lịch hẹn nào phù hợp.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($appointments, 'links'))
            <div class="mt-3">
                {{ $appointments->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal Quét Mã QR bằng Camera hoặc Tải ảnh phiếu khám PNG -->
<div class="modal fade" id="qrScannerModal" tabindex="-1" role="dialog" aria-labelledby="qrScannerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-info text-white">
                <h6 class="modal-title font-weight-bold" id="qrScannerModalLabel">
                    <i class="fas fa-qrcode mr-2"></i>Quét Mã QR Phiếu Khám 125 Thái Thịnh
                </h6>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <p class="small text-muted mb-2">
                    Đưa mã QR trên điện thoại của bệnh nhân vào khung Camera hoặc chọn file ảnh phiếu khám (<code>Phieu-Kham-125ThaiThinh-...png</code>) để tự động Check-in.
                </p>
                <div id="qrReaderViewport" class="border rounded bg-light mx-auto mb-3" style="width: 100%; max-width: 340px; min-height: 240px;"></div>
                <div class="custom-file text-left mb-2">
                    <input type="file" class="custom-file-input" id="qrFileInput" accept="image/*">
                    <label class="custom-file-label" for="qrFileInput">Chọn ảnh phiếu khám QR từ máy tính...</label>
                </div>
                <div id="qrScanStatus" class="small font-weight-bold text-info mt-2"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Xác nhận Hủy lịch hẹn kèm Lý do lưu vào Audit Trail -->
<div class="modal fade" id="adminCancelModal" tabindex="-1" role="dialog" aria-labelledby="adminCancelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg">
            <form action="" method="POST" id="adminCancelForm">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="cancelled">
                <div class="modal-header bg-danger text-white">
                    <h6 class="modal-title font-weight-bold" id="adminCancelModalLabel">
                        <i class="fas fa-calendar-times mr-2"></i>Xác nhận Hủy Lịch Hẹn Khám Bệnh
                    </h6>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="text-gray-800 mb-3">
                        Bạn đang thực hiện hủy lịch hẹn <strong class="text-danger" id="cancelModalCode">#---</strong> của bệnh nhân <strong id="cancelModalPatient">---</strong>.
                    </p>
                    <div class="form-group mb-2">
                        <label class="font-weight-bold text-gray-700 small text-uppercase">Chọn nhanh lý do hủy:</label>
                        <div class="d-flex flex-wrap" style="gap: 6px;">
                            <button type="button" class="btn btn-xs btn-outline-secondary py-1 px-2 small quick-cancel-reason" data-reason="Bệnh nhân liên hệ tổng đài báo bận đột xuất">BN báo bận đột xuất</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary py-1 px-2 small quick-cancel-reason" data-reason="Bác sĩ có lịch hội chẩn / công tác đột xuất">BS bận hội chẩn</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary py-1 px-2 small quick-cancel-reason" data-reason="Hồ sơ đặt trùng khung giờ, đã gộp lịch">Đặt trùng lịch</button>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold text-gray-700 small" for="adminCancelNote">Ghi chú / Lý do hủy chi tiết <span class="text-danger">*</span>:</label>
                        <textarea name="note" id="adminCancelNote" rows="3" class="form-control" placeholder="Nhập lý do hủy lịch hẹn (lưu vào lịch sử Audit Trail & hiển thị cho bệnh nhân)..." required>Bệnh nhân liên hệ tổng đài yêu cầu hủy lịch</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Quay lại</button>
                    <button type="submit" class="btn btn-danger btn-sm font-weight-bold">
                        <i class="fas fa-times-circle mr-1"></i> Xác nhận hủy lịch
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
$(document).ready(function() {
    let html5QrCode = null;

    function handleDecodedQr(decodedText) {
        if (!decodedText) return;
        $('#qrScanStatus').removeClass('text-danger').addClass('text-success')
            .html('<i class="fas fa-check-circle mr-1"></i>Đã nhận diện mã: <strong>' + decodedText + '</strong>. Đang tiếp đón...');
        $('#qrCodeInput').val(decodedText);
        if (html5QrCode && html5QrCode.isScanning) {
            html5QrCode.stop().catch(() => {});
        }
        setTimeout(function() {
            $('#quickCheckinForm').submit();
        }, 250);
    }

    $('#qrScannerModal').on('shown.bs.modal', function() {
        $('#qrScanStatus').text('Đang khởi tạo Camera quét mã QR...');
        if (typeof Html5Qrcode === 'undefined') {
            $('#qrScanStatus').text('Vui lòng chọn file ảnh QR hoặc nhập mã trực tiếp.');
            return;
        }
        html5QrCode = new Html5Qrcode("qrReaderViewport");
        html5QrCode.start(
            { facingMode: "environment" },
            { fps: 10, qrbox: { width: 220, height: 220 } },
            function(decodedText) {
                handleDecodedQr(decodedText);
            },
            function() {}
        ).then(function() {
            $('#qrScanStatus').text('Camera đã sẵn sàng. Hãy đưa mã QR vào khung hình.');
        }).catch(function() {
            $('#qrScanStatus').text('Không tìm thấy Webcam. Bạn có thể chọn ảnh phiếu QR bên trên để quét.');
        });
    });

    $('#qrScannerModal').on('hidden.bs.modal', function() {
        if (html5QrCode && html5QrCode.isScanning) {
            html5QrCode.stop().catch(() => {});
        }
    });

    $('#qrFileInput').on('change', function(e) {
        if (!e.target.files || e.target.files.length === 0) return;
        const imageFile = e.target.files[0];
        $('.custom-file-label').text(imageFile.name);
        if (typeof Html5Qrcode === 'undefined') return;
        if (!html5QrCode) {
            html5QrCode = new Html5Qrcode("qrReaderViewport");
        }
        html5QrCode.scanFile(imageFile, true)
            .then(function(decodedText) {
                handleDecodedQr(decodedText);
            })
            .catch(function() {
                $('#qrScanStatus').removeClass('text-info').addClass('text-danger')
                    .text('Không đọc được mã QR trong ảnh này. Vui lòng thử ảnh rõ nét hơn hoặc nhập mã TT-...');
            });
    });

    // Modal Hủy lịch hẹn kèm lý do
    $('.btn-open-cancel-modal').on('click', function() {
        const actionUrl = $(this).data('action');
        const code = $(this).data('code');
        const patient = $(this).data('patient');
        $('#adminCancelForm').attr('action', actionUrl);
        $('#cancelModalCode').text(code);
        $('#cancelModalPatient').text(patient);
    });

    $('.quick-cancel-reason').on('click', function() {
        $('#adminCancelNote').val($(this).data('reason'));
    });

    $('#exportAdminApptsCsvBtn').on('click', function() {
        var rows = [['Mã & Ngày khám', 'Bệnh nhân / Người thân', 'Bác sĩ & Chuyên khoa', 'Trạng thái & Viện phí']];
        $('table.table tbody tr').each(function() {
            var cols = $(this).find('td');
            if (cols.length >= 4) {
                rows.push([
                    $(cols[0]).text().replace(/\s+/g, ' ').trim(),
                    $(cols[1]).text().replace(/\s+/g, ' ').trim(),
                    $(cols[2]).text().replace(/\s+/g, ' ').trim(),
                    $(cols[3]).text().replace(/\s+/g, ' ').trim()
                ]);
            }
        });
        var csvContent = '\uFEFF' + rows.map(function(r) {
            return r.map(function(c) { return '"' + String(c).replace(/"/g, '""') + '"'; }).join(',');
        }).join('\n');
        var blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        var link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'danh-sach-lich-hen-' + new Date().toISOString().slice(0, 10) + '.csv';
        link.click();
    });
});
</script>
@endpush