<footer id="footer" class="footer light-background">

  <div class="container footer-top">
    <div class="row gy-4">
      <div class="col-lg-4 col-md-6 footer-about">
        <a href="{{ url('/') }}" class="logo d-flex align-items-center">
          <span class="sitename">125 Thái Thịnh</span>
        </a>
        <div class="footer-contact pt-3">
          <p>Số 125 phố Thái Thịnh, phường Thịnh Quang</p>
          <p>Quận Đống Đa, Hà Nội (GPHĐ: 125/BYT-GPHĐ)</p>
          <p class="mt-3"><strong>Phone:</strong> <span>1900 1234 (07:30 – 20:00)</span></p>
          <p><strong>Email:</strong> <span>cskh@thaithinhmedic.vn</span></p>
        </div>
        <div class="social-links d-flex mt-4">
          <a href="#"><i class="bi bi-twitter-x"></i></a>
          <a href="#"><i class="bi bi-facebook"></i></a>
          <a href="#"><i class="bi bi-instagram"></i></a>
          <a href="#"><i class="bi bi-linkedin"></i></a>
        </div>
      </div>

      <div class="col-lg-2 col-md-3 footer-links">
        <h4>Liên kết nhanh</h4>
        <ul>
          <li><a href="{{ url('/') }}">Trang chủ</a></li>
          <li><a href="{{ route('introduces.index') }}">Giới thiệu</a></li>
          <li><a href="{{ route('services.index') }}">Dịch vụ &amp; Bảng giá</a></li>
          <li><a href="{{ route('departments.index') }}">Chuyên khoa</a></li>
          <li><a href="{{ route('doctors.index') }}">Đội ngũ Bác sĩ</a></li>
        </ul>
      </div>

      <div class="col-lg-2 col-md-3 footer-links">
        <h4>Dịch vụ mũi nhọn</h4>
        <ul>
          <li><a href="{{ route('services.index', ['symptom' => 'Tổng quát']) }}">Khám sức khỏe tổng quát</a></li>
          <li><a href="{{ route('services.index', ['symptom' => 'Siêu âm']) }}">Siêu âm thai 5D</a></li>
          <li><a href="{{ route('services.index', ['symptom' => 'Tim mạch']) }}">Tầm soát Tim mạch</a></li>
          <li><a href="{{ route('services.index', ['symptom' => 'Xét nghiệm']) }}">Xét nghiệm ISO 15189</a></li>
          <li><a href="{{ route('advisor.index') }}">Tư vấn triệu chứng AI</a></li>
        </ul>
      </div>

      <div class="col-lg-2 col-md-3 footer-links">
        <h4>Hỗ trợ bệnh nhân</h4>
        <ul>
          <li><a href="{{ route('appointments.create') }}">Đặt lịch khám online</a></li>
          <li><a href="{{ route('appointments.index') }}">Tra cứu phiếu khám QR</a></li>
          <li><a href="{{ route('introduces.index') }}">Quy trình khám BHYT</a></li>
          <li><a href="{{ route('profile.edit') }}">Hồ sơ bệnh án điện tử</a></li>
          <li><a href="{{ route('register') }}">Đăng ký tài khoản</a></li>
        </ul>
      </div>

      <div class="col-lg-2 col-md-3 footer-links">
        <h4>Cổng nghiệp vụ</h4>
        <ul>
          <li><a href="{{ route('patient.login') }}">Cổng Bệnh nhân</a></li>
          <li><a href="{{ route('doctor.login') }}">Cổng Bác sĩ khám bệnh</a></li>
          <li><a href="{{ route('admin.login') }}">Cổng Quản trị viên</a></li>
          <li><a href="tel:19001234">Tổng đài: 1900 1234</a></li>
          <li><a href="tel:115">Cấp cứu y tế: 115</a></li>
        </ul>
      </div>

    </div>
  </div>

  <div class="container copyright text-center mt-4">
    <p>© <span>Copyright</span> <strong class="px-1 sitename">Medilab 125 Thái Thịnh</strong> <span>All Rights Reserved</span></p>
    <div class="credits">
      Designed by <a href="https://bootstrapmade.com/">BootstrapMade</a> Distributed by <a href="https://themewagon.com">ThemeWagon</a>
    </div>
  </div>

</footer>

<!-- Scroll Top -->
<a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
