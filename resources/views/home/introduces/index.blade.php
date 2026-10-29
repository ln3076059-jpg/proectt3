@extends('home.frontend')

@section('title', 'Giới thiệu · Phòng khám Đa khoa 125 Thái Thịnh')

@section('content')
<!-- Page Title -->
<div class="page-title" data-aos="fade">
  <nav class="breadcrumbs">
    <div class="container">
      <ol>
        <li><a href="{{ url('/') }}">Trang chủ</a></li>
        <li class="current">Giới thiệu Phòng khám</li>
      </ol>
    </div>
  </nav>
</div><!-- End Page Title -->

<section class="tiki-hero-section py-4">
    <div class="ts-frame">
        <div class="row g-4 align-items-center">
            <div class="col-lg-6">
                <div class="tiki-hero-badge">
                    <span class="tiki-hero-badge-dot"></span>
                    <span>Thành lập từ năm 1998 · Hơn 25 năm phụng sự</span>
                </div>
                <h1 class="tiki-hero-title" style="font-size: clamp(1.75rem, 3vw, 2.4rem);">
                    Phòng khám Đa khoa 125 Thái Thịnh — Địa chỉ Y tế Tin cậy tại Thủ đô
                </h1>
                <p class="tiki-hero-subtitle">
                    Toạ lạc tại trung tâm quận Đống Đa, Phòng khám Đa khoa 125 Thái Thịnh hoạt động với nguyên tắc cốt lõi: <strong>Chuyên môn vững vàng, Chi phí minh bạch và Tiếp đón đúng giờ</strong>.
                </p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('appointments.create') }}" class="ts-btn">
                        <i class="bi bi-calendar2-plus"></i>
                        <span>Đặt lịch khám ngay</span>
                    </a>
                    <a href="{{ route('doctors.index') }}" class="ts-btn-secondary">
                        <i class="bi bi-person-badge"></i>
                        <span>Xem đội ngũ bác sĩ</span>
                    </a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="tiki-hero-showcase">
                    <div class="tiki-hero-showcase-media" style="aspect-ratio: 16 / 9;">
                        <img src="{{ asset('image/clinic/clinic-reception-qr.jpg') }}" alt="Sảnh tiếp đón Phòng khám Đa khoa 125 Thái Thịnh">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="tiki-section">
    <div class="ts-frame">
        <div class="tiki-section-header">
            <div>
                <span class="tiki-section-eyebrow">
                    <i class="bi bi-cpu-fill"></i>
                    <span>Cơ sở vật chất &amp; Trang thiết bị</span>
                </span>
                <h2 class="tiki-section-title">Tiêu chuẩn lâm sàng &amp; Chẩn đoán công nghệ cao</h2>
                <p class="tiki-section-desc">
                    Hệ thống thiết bị nhập khẩu đồng bộ từ Hoa Kỳ, Nhật Bản và CHLB Đức giúp rút ngắn thời gian chờ và nâng cao độ chính xác.
                </p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <article class="tiki-item-card">
                    <div class="tiki-card-thumb">
                        <img src="{{ asset('image/clinic/spec-obgyn-5d.jpg') }}" alt="Hệ thống siêu âm chẩn đoán hình ảnh 5D">
                        <div class="tiki-card-badges">
                            <span class="tiki-badge tiki-badge-primary">Tầng 3 &amp; Tầng 4</span>
                        </div>
                    </div>
                    <div class="tiki-card-body">
                        <h3 class="tiki-card-title">Siêu âm 5D GE Voluson E10</h3>
                        <p class="tiki-card-desc">
                            Hệ thống siêu âm Doppler màu 4D/5D công nghệ Hoa Kỳ giúp khảo sát hình thái thai nhi và tim mạch với độ phân giải cao.
                        </p>
                    </div>
                </article>
            </div>

            <div class="col-md-4">
                <article class="tiki-item-card">
                    <div class="tiki-card-thumb">
                        <img src="{{ asset('image/clinic/spec-internal-lab.jpg') }}" alt="Trung tâm xét nghiệm tự động chuẩn ISO 15189">
                        <div class="tiki-card-badges">
                            <span class="tiki-badge tiki-badge-emerald">Chuẩn ISO 15189</span>
                        </div>
                    </div>
                    <div class="tiki-card-body">
                        <h3 class="tiki-card-title">Xét nghiệm tự động &amp; Nội soi NBI</h3>
                        <p class="tiki-card-desc">
                            Công nghệ nội soi dải tần ánh sáng hẹp NBI Olympus Nhật Bản và trung tâm xét nghiệm tự động trả kết quả nhanh trong 45 phút.
                        </p>
                    </div>
                </article>
            </div>

            <div class="col-md-4">
                <article class="tiki-item-card">
                    <div class="tiki-card-thumb">
                        <img src="{{ asset('image/clinic/spec-cardiology.jpg') }}" alt="Tầm soát tim mạch và chẩn đoán chức năng">
                        <div class="tiki-card-badges">
                            <span class="tiki-badge tiki-badge-primary">Tầng 2 · Khu A</span>
                        </div>
                    </div>
                    <div class="tiki-card-body">
                        <h3 class="tiki-card-title">Thăm dò chức năng &amp; Tim mạch</h3>
                        <p class="tiki-card-desc">
                            Hệ thống điện tâm đồ, Holter huyết áp 24h và siêu âm tim Doppler màu hỗ trợ phát hiện sớm các bệnh lý tim mạch phức tạp.
                        </p>
                    </div>
                </article>
            </div>
        </div>
    </div>
</section>

{{-- Quy trình khám bệnh 4 bước thông minh (QR Check-in) --}}
<section class="tiki-section pt-0">
    <div class="ts-frame">
        <div class="tiki-section-header">
            <div>
                <span class="tiki-section-eyebrow">
                    <i class="bi bi-qr-code-scan"></i>
                    <span>Quy trình Tiếp đón Số hóa</span>
                </span>
                <h2 class="tiki-section-title">Quy trình Khám bệnh 4 Bước Thông minh (Không chờ đợi)</h2>
                <p class="tiki-section-desc">
                    Tối ưu hóa thời gian của người bệnh bằng hệ thống đặt lịch hẹn trước và tiếp đón tự động bằng Mã QR tại sảnh Tầng 1.
                </p>
            </div>
        </div>

        <div class="row g-3 mb-5">
            <div class="col-md-6 col-lg-3">
                <div class="ts-surface-card p-4 h-100 border">
                    <div class="badge bg-primary-subtle text-primary fw-bold mb-2 px-3 py-1 rounded-pill">BƯỚC 01</div>
                    <h3 class="h6 fw-bold text-dark mb-2">Đặt lịch &amp; Nhận Mã QR</h3>
                    <p class="small text-muted mb-0">
                        Chọn chuyên khoa, bác sĩ và khung giờ khám trực tuyến. Nhận ngay Phiếu khám điện tử kèm mã QR định danh.
                    </p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="ts-surface-card p-4 h-100 border">
                    <div class="badge bg-info-subtle text-info fw-bold mb-2 px-3 py-1 rounded-pill">BƯỚC 02</div>
                    <h3 class="h6 fw-bold text-dark mb-2">Check-in 1 chạm tại Quầy</h3>
                    <p class="small text-muted mb-0">
                        Đưa mã QR tại Quầy Tiếp Đón Ưu Tiên (Tầng 1) trước giờ hẹn 10 phút để kích hoạt thứ tự vào thẳng buồng khám.
                    </p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="ts-surface-card p-4 h-100 border">
                    <div class="badge bg-warning-subtle text-warning-emphasis fw-bold mb-2 px-3 py-1 rounded-pill">BƯỚC 03</div>
                    <h3 class="h6 fw-bold text-dark mb-2">Khám Lâm sàng &amp; Xét nghiệm</h3>
                    <p class="small text-muted mb-0">
                        Bác sĩ chuyên khoa thăm khám, chỉ định cận lâm sàng (nếu cần). Kết quả xét nghiệm đồng bộ tự động lên hệ thống EMR.
                    </p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="ts-surface-card p-4 h-100 border">
                    <div class="badge bg-success-subtle text-success fw-bold mb-2 px-3 py-1 rounded-pill">BƯỚC 04</div>
                    <h3 class="h6 fw-bold text-dark mb-2">Nhận Bệnh án EMR &amp; Thuốc</h3>
                    <p class="small text-muted mb-0">
                        Bác sĩ kết luận, kê đơn thuốc điện tử. Bệnh nhân nhận thuốc tại Nhà thuốc GPP và tra cứu hồ sơ bệnh án trọn đời trên web.
                    </p>
                </div>
            </div>
        </div>

        {{-- Hướng dẫn Thủ tục BHYT & Sơ đồ Tầng Chuyên khoa --}}
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="ts-surface-card p-4 h-100 border">
                    <h3 class="h5 fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-shield-check text-success"></i>
                        <span>Hướng dẫn Khám Bảo hiểm Y tế (BHYT) &amp; Bảo lãnh</span>
                    </h3>
                    <ul class="list-unstyled d-flex flex-column gap-2 small text-secondary mb-3">
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i><strong>Giấy tờ cần chuẩn bị:</strong> Thẻ CCCD gắn chip (hoặc tài khoản VNeID mức 2) hoặc Thẻ BHYT bản giấy/ứng dụng VssID kèm giấy tờ tùy thân có ảnh.</li>
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i><strong>Thanh toán BHYT đúng tuyến:</strong> Hưởng đầy đủ quyền lợi theo quy định của BHXH Việt Nam đối với danh mục khám, xét nghiệm và thuốc BHYT.</li>
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i><strong>Bảo lãnh viện phí thương mại:</strong> Hỗ trợ xuất hóa đơn điện tử VAT và hồ sơ y tế đầy đủ cho các hãng bảo hiểm sức khỏe (Bảo Việt, PVI, Manulife, Prudential...).</li>
                    </ul>
                    <a href="{{ route('advisor.index') }}" class="ts-btn-secondary ts-btn-sm">
                        <i class="bi bi-robot"></i>
                        <span>Tra cứu triệu chứng &amp; Gợi ý khoa khám</span>
                    </a>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="ts-surface-card p-4 h-100 border">
                    <h3 class="h5 fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-building text-primary"></i>
                        <span>Sơ đồ Phân khu Chuyên khoa tại 125 Thái Thịnh</span>
                    </h3>
                    <div class="d-flex flex-column gap-2 small">
                        <div class="d-flex justify-content-between align-items-center p-2 rounded bg-light border">
                            <strong class="text-primary">Tầng 1 · Sảnh chính</strong>
                            <span class="text-secondary">Quầy Tiếp Đón QR, Thu ngân BHYT &amp; Nhà thuốc GPP</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center p-2 rounded bg-light border">
                            <strong class="text-primary">Tầng 2 · Khu A &amp; B</strong>
                            <span class="text-secondary">Khoa Nội Tổng Quát, Khoa Tim Mạch &amp; Điện tâm đồ</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center p-2 rounded bg-light border">
                            <strong class="text-primary">Tầng 3 · Khu A &amp; B</strong>
                            <span class="text-secondary">Trung tâm Xét nghiệm ISO 15189, Siêu âm 5D &amp; X-Quang</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center p-2 rounded bg-light border">
                            <strong class="text-primary">Tầng 4 · Khu Chuyên khoa</strong>
                            <span class="text-secondary">Khoa Sản Phụ Khoa, Khoa Nhi &amp; Tai Mũi Họng nội soi</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center p-2 rounded bg-light border">
                            <strong class="text-primary">Tầng 5 · Khu Kỹ thuật cao</strong>
                            <span class="text-secondary">Trung tâm Nội soi Tiêu hóa NBI &amp; Khám Sức khỏe Doanh nghiệp</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
