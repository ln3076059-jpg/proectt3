@extends('home.frontend')

@section('title', 'Đặt lịch thành công - Phòng khám Thái Thịnh')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <div class="tiki-card text-center p-4 p-md-5">
                <div class="rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center mb-3" style="width: 72px; height: 72px; font-size: 32px;">
                    <i class="fas fa-check"></i>
                </div>
                <h3 class="fw-bold text-dark mb-2">Đặt Lịch Khám Thành Công!</h3>
                <p class="text-muted small mb-4">
                    Hệ thống đã ghi nhận thông tin đặt khám của bạn. Chúng tôi đã gửi thông báo xác nhận và mã QR check-in qua hệ thống.
                </p>

                <div class="p-3 bg-light rounded-3 text-start small mb-4 border">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Địa điểm khám:</span>
                        <strong class="text-dark">125 Thái Thịnh, Đống Đa, Hà Nội</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Thời gian làm việc:</span>
                        <strong class="text-dark">7h30 - 20h00 (Tất cả các ngày)</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Hotline hỗ trợ:</span>
                        <strong class="text-danger">1900 1234</strong>
                    </div>
                </div>

                {{-- Checklist chuẩn bị trước khi đến khám --}}
                <div class="p-3 bg-primary-subtle bg-opacity-25 rounded-3 text-start small mb-4 border border-primary-subtle">
                    <div class="fw-bold text-primary mb-2">
                        <i class="fas fa-clipboard-check me-1"></i> Lưu ý chuẩn bị trước khi đến khám:
                    </div>
                    <ul class="mb-0 ps-3 text-secondary d-flex flex-column gap-1">
                        <li>Mang theo <strong>CCCD gắn chip / VNeID</strong> và <strong>Thẻ BHYT</strong> (nếu khám bảo hiểm).</li>
                        <li>Có mặt trước giờ hẹn <strong>10 phút</strong> để quét Mã QR tại Quầy Tiếp Đón Tầng 1.</li>
                        <li>Nếu có xét nghiệm máu hoặc nội soi dạ dày, vui lòng <strong>nhịn ăn sáng 6 – 8 tiếng</strong> (chỉ uống nước lọc).</li>
                    </ul>
                </div>

                <div class="d-flex flex-wrap justify-content-center gap-2">
                    <a href="{{ route('appointments.index') }}" class="btn btn-tiki-primary px-4 py-2">
                        <i class="far fa-calendar-check me-1"></i> Xem lịch hẹn của tôi
                    </a>
                    <a href="{{ route('home') }}" class="btn btn-tiki-outline px-4 py-2">
                        Về trang chủ
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
