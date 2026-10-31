@php
    $pendingAppointmentsCount = $pendingAppointmentsCount ?? 0;
@endphp

<header id="header" class="header sticky-top">

  <div class="topbar d-flex align-items-center">
    <div class="container d-flex justify-content-center justify-content-md-between">
      <div class="contact-info d-flex align-items-center">
        <i class="bi bi-envelope d-flex align-items-center"><a href="mailto:cskh@thaithinhmedic.vn">cskh@thaithinhmedic.vn</a></i>
        <i class="bi bi-phone d-flex align-items-center ms-4"><span>1900 1234 · 125 Thái Thịnh, Đống Đa, Hà Nội</span></i>
      </div>
      <div class="social-links d-none d-md-flex align-items-center">
        <a href="{{ route('appointments.index') }}" class="me-3 text-white text-decoration-none" style="font-size: 13px;">
          <i class="bi bi-qr-code-scan me-1"></i>Tra cứu phiếu khám
        </a>
        <a href="#" class="twitter"><i class="bi bi-twitter-x"></i></a>
        <a href="#" class="facebook"><i class="bi bi-facebook"></i></a>
        <a href="#" class="instagram"><i class="bi bi-instagram"></i></a>
        <a href="#" class="linkedin"><i class="bi bi-linkedin"></i></a>
      </div>
    </div>
  </div><!-- End Top Bar -->

  <div class="branding d-flex align-items-center">

    <div class="container position-relative d-flex align-items-center justify-content-between">
      <a href="{{ url('/') }}" class="logo d-flex align-items-center me-auto">
        <!-- Uncomment the line below if you also wish to use an image logo -->
        <!-- <img src="{{ asset('assets/img/logo.png') }}" alt=""> -->
        <h1 class="sitename">125 Thái Thịnh</h1>
      </a>

      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="{{ url('/') }}" class="{{ request()->is('/') ? 'active' : '' }}">Trang chủ<br></a></li>
          <li><a href="{{ route('introduces.index') }}" class="{{ request()->routeIs('introduces.*') ? 'active' : '' }}">Giới thiệu</a></li>
          <li><a href="{{ route('services.index') }}" class="{{ request()->routeIs('services.*') ? 'active' : '' }}">Dịch vụ</a></li>
          <li><a href="{{ route('departments.index') }}" class="{{ request()->routeIs('departments.*') ? 'active' : '' }}">Chuyên khoa</a></li>
          <li><a href="{{ route('doctors.index') }}" class="{{ request()->routeIs('doctors.*') ? 'active' : '' }}">Bác sĩ</a></li>
          <li><a href="{{ route('advisor.index') }}" class="{{ request()->routeIs('advisor.*') ? 'active' : '' }}">Tư vấn triệu chứng</a></li>
          <li class="d-xl-none"><a href="{{ route('appointments.create') }}" class="fw-bold text-primary">Đặt lịch khám ngay</a></li>
          <li class="dropdown">
            @auth
              <a href="{{ route('profile.edit') }}" class="{{ request()->routeIs('profile.*', 'appointments.*') ? 'active' : '' }}">
                <span>{{ \Illuminate\Support\Str::limit(Auth::user()->name, 16) }}</span>
                @if($pendingAppointmentsCount > 0)
                  <span class="badge rounded-pill bg-primary ms-1">{{ $pendingAppointmentsCount }}</span>
                @endif
                <i class="bi bi-chevron-down toggle-dropdown"></i>
              </a>
              <ul>
                @if(Auth::user()->hasRole(['admin', 'leader']))
                  <li><a href="{{ route('admin.dashboard') }}">Trang Quản trị / Lãnh đạo</a></li>
                @endif
                @if(Auth::user()->hasRole('doctor'))
                  <li><a href="{{ route('doctor.dashboard') }}">Bàn làm việc Bác sĩ</a></li>
                @endif
                <li><a href="{{ route('appointments.index') }}">Lịch hẹn của tôi ({{ $pendingAppointmentsCount }})</a></li>
                <li><a href="{{ route('profile.edit') }}">Hồ sơ bệnh nhân</a></li>
                <li>
                  <form method="POST" action="{{ route('logout') }}" class="m-0 p-0">
                    @csrf
                    <button type="submit" class="dropdown-item text-danger fw-semibold px-3 py-2 border-0 bg-transparent w-100 text-start" style="font-size: 15px;">Đăng xuất</button>
                  </form>
                </li>
              </ul>
            @else
              <a href="{{ route('login') }}"><span>Tài khoản</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
              <ul>
                <li><a href="{{ route('login') }}">Đăng nhập</a></li>
                <li><a href="{{ route('register') }}">Đăng ký hồ sơ mới</a></li>
                <li><a href="{{ route('patient.login') }}">Cổng Bệnh nhân</a></li>
                <li><a href="{{ route('doctor.login') }}">Cổng Bác sĩ khám bệnh</a></li>
                <li><a href="{{ route('admin.login') }}">Cổng Quản trị viên</a></li>
              </ul>
            @endauth
          </li>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>

      <a class="cta-btn" href="{{ route('appointments.create') }}">Đặt lịch khám</a>

    </div>

  </div>

</header>
