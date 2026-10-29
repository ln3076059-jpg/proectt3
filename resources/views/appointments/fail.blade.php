@extends('home.frontend')

@section('title', 'Thanh toán không thành công - Phòng khám Thái Thịnh')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <div class="tiki-card text-center p-4 p-md-5">
                <div class="rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center mb-3" style="width: 72px; height: 72px; font-size: 32px;">
                    <i class="fas fa-times"></i>
                </div>
                <h3 class="fw-bold text-dark mb-2">Thanh Toán Chưa Hoàn Tất</h3>
                <p class="text-muted small mb-4">
                    Giao dịch thanh toán trực tuyến bị gián đoạn hoặc bạn đã hủy giao dịch. Lịch hẹn của bạn vẫn được lưu ở trạng thái chờ thanh toán hoặc thanh toán tại quầy.
                </p>

                <div class="p-3 bg-light rounded-3 text-start small mb-4 border">
                    <div class="fw-bold text-dark mb-1">
                        <i class="fas fa-info-circle text-primary me-1"></i> Hướng dẫn xử lý nhanh:
                    </div>
                    <ul class="mb-0 ps-3 text-secondary d-flex flex-column gap-1">
                        <li>Bạn có thể vào mục <strong>Quản lý lịch hẹn</strong> để thực hiện thanh toán lại qua cổng VNPay.</li>
                        <li>Hoặc giữ nguyên phiếu hẹn để <strong>thanh toán trực tiếp tại Quầy Thu Ngân Tầng 1</strong> khi đến khám.</li>
                        <li>Cần hỗ trợ khẩn cấp, vui lòng liên hệ Tổng đài: <strong class="text-danger">1900 1234</strong>.</li>
                    </ul>
                </div>

                <div class="d-flex flex-wrap justify-content-center gap-2">
                    <a href="{{ route('appointments.index') }}" class="btn btn-tiki-primary px-4 py-2">
                        <i class="far fa-calendar-check me-1"></i> Quản lý lịch hẹn
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
