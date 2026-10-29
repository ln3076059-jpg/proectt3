@extends('home.frontend')

@php
    $role = $role ?? 'patient';

    if ($role === 'admin') {
        $loginAction = route('admin.login.submit');
        $pageTitle = 'Đăng nhập Quản trị viên · Medilab 125 Thái Thịnh';
    } elseif ($role === 'doctor') {
        $loginAction = route('doctor.login.submit');
        $pageTitle = 'Đăng nhập Cổng Bác sĩ · Medilab 125 Thái Thịnh';
    } else {
        $loginAction = route('patient.login.submit');
        $pageTitle = 'Đăng nhập Cổng Bệnh nhân · Medilab 125 Thái Thịnh';
    }
@endphp

@section('title', $pageTitle)

@section('content')
<!-- Page Title -->
<div class="page-title" data-aos="fade">
  <nav class="breadcrumbs">
    <div class="container">
      <ol>
        <li><a href="{{ url('/') }}">Trang chủ</a></li>
        <li class="current">
          @if($role === 'admin')
            Đăng nhập Quản trị viên
          @elseif($role === 'doctor')
            Đăng nhập Cổng Bác sĩ
          @else
            Đăng nhập Cổng Bệnh nhân
          @endif
        </li>
      </ol>
    </div>
  </nav>
</div><!-- End Page Title -->

<section class="section light-background py-5">
  <div class="container">
    <div class="ts-auth-grid">
      {{-- Left Column: Authentic 125 Thái Thịnh Clinical Context & Patient Benefits --}}
      <section class="ts-auth-context-card" aria-label="Thông tin Cổng Bệnh nhân 125 Thái Thịnh">
        <div>
          <div class="ts-auth-photo-wrap">
            <img src="{{ asset('assets/img/about.jpg') }}"
                 alt="Sảnh tiếp đón Phòng khám Đa khoa 125 Thái Thịnh"
                 width="720"
                 height="380">
            <div class="tiki-booking-photo-overlay">
              <span class="tiki-live-pill">
                <span class="tiki-hero-badge-dot"></span>
                Đang tiếp đón · Quầy ưu tiên QR
              </span>
              <span class="tiki-time-pill ts-num">07:30 – 20:00</span>
            </div>
          </div>

          @if($role === 'doctor')
            <h2 style="font-size: 20px; font-weight: 700; margin-bottom: 6px; color: var(--heading-color);">
              Cổng Lâm sàng &amp; Hồ sơ Bệnh án Bác sĩ
            </h2>
            <p style="font-size: 14px; color: var(--default-color); margin-bottom: 0;">
              Dành cho đội ngũ Bác sĩ Chuyên khoa tại 125 Thái Thịnh theo dõi lịch khám trong ngày, cập nhật chẩn đoán lâm sàng và chỉ định cận lâm sàng.
            </p>
          @elseif($role === 'admin')
            <h2 style="font-size: 20px; font-weight: 700; margin-bottom: 6px; color: var(--heading-color);">
              Trung tâm Điều hành &amp; Quản trị Hệ thống
            </h2>
            <p style="font-size: 14px; color: var(--default-color); margin-bottom: 0;">
              Quản lý lịch trực chuyên khoa, tiếp nhận đặt lịch trực tuyến, xác nhận thu ngân viện phí và đồng bộ danh mục xét nghiệm.
            </p>
          @else
            <h2 style="font-size: 20px; font-weight: 700; margin-bottom: 6px; color: var(--heading-color);">
              Cổng Hồ sơ Bệnh nhân &amp; Đặt khám Điện tử
            </h2>
            <p style="font-size: 14px; color: var(--default-color); margin-bottom: 0;">
              Đồng bộ toàn bộ lịch hẹn, mã phiếu khám ưu tiên và kết quả cận lâm sàng tại Phòng khám Đa khoa 125 Thái Thịnh trên một tài khoản duy nhất.
            </p>
          @endif

          <div class="ts-auth-feature-list">
            <div class="ts-auth-feature-item">
              <span class="ts-auth-feature-icon"><i class="fa-solid fa-qrcode"></i></span>
              <div>
                <div style="font-size: 14px; font-weight: 700; color: var(--heading-color); margin-bottom: 2px;">
                  Check-in Quầy Ưu tiên bằng Mã QR
                </div>
                <div style="font-size: 13px; color: var(--default-color); line-height: 1.45;">
                  Nhận mã phiếu khám điện tử ngay sau khi đặt lịch, vào thẳng buồng khám chuyên khoa theo đúng khung giờ hẹn.
                </div>
              </div>
            </div>

            <div class="ts-auth-feature-item">
              <span class="ts-auth-feature-icon"><i class="fa-solid fa-file-waveform"></i></span>
              <div>
                <div style="font-size: 14px; font-weight: 700; color: var(--heading-color); margin-bottom: 2px;">
                  Tra cứu Kết quả Xét nghiệm &amp; Siêu âm 5D
                </div>
                <div style="font-size: 13px; color: var(--default-color); line-height: 1.45;">
                  Lưu trữ bảo mật kết quả xét nghiệm máu chuẩn ISO 15189, chẩn đoán hình ảnh và đơn thuốc điện tử của bác sĩ.
                </div>
              </div>
            </div>

            <div class="ts-auth-feature-item">
              <span class="ts-auth-feature-icon"><i class="fa-solid fa-calendar-check"></i></span>
              <div>
                <div style="font-size: 14px; font-weight: 700; color: var(--heading-color); margin-bottom: 2px;">
                  Chủ động Quản lý Lịch hẹn &amp; Chi phí Niêm yết
                </div>
                <div style="font-size: 13px; color: var(--default-color); line-height: 1.45;">
                  Đổi giờ hẹn linh hoạt, chọn trực tiếp Phó Giáo sư, Tiến sĩ, Bác sĩ Chuyên khoa I &amp; II và hỗ trợ bảo lãnh BHYT.
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="ts-auth-kpi-strip">
          <div class="ts-auth-kpi-cell">
            <div class="ts-auth-kpi-label">Đội ngũ chuyên gia</div>
            <div class="ts-auth-kpi-val ts-num">45+ Bác sĩ CK</div>
          </div>
          <div class="ts-auth-kpi-cell">
            <div class="ts-auth-kpi-label">Hệ thống lâm sàng</div>
            <div class="ts-auth-kpi-val ts-num">12 Chuyên khoa</div>
          </div>
          <div class="ts-auth-kpi-cell">
            <div class="ts-auth-kpi-label">Tổng đài hỗ trợ</div>
            <div class="ts-auth-kpi-val ts-num">1900 1234</div>
          </div>
        </div>
      </section>

      {{-- Right Column: Login Form Workbench --}}
      <section class="ts-auth-form-card" aria-label="Biểu mẫu đăng nhập">
        <div>
          <div class="mb-3">
            <h1 style="font-size: 24px; font-weight: 700; color: var(--heading-color); margin-bottom: 6px;">
              @if($role === 'admin')
                Đăng nhập Quản trị viên
              @elseif($role === 'doctor')
                Đăng nhập Cổng Bác sĩ
              @else
                Đăng nhập Cổng Bệnh nhân
              @endif
            </h1>
            <p style="font-size: 14px; color: var(--default-color); margin-bottom: 0;">
              Nhập thông tin tài khoản hoặc chọn tài khoản mẫu bên dưới để trải nghiệm ngay.
            </p>
          </div>

          {{-- Session Flash Alerts --}}
          @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show border rounded-2 small mb-3" role="alert">
              <span>{{ session('success') }}</span>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endif

          @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show border rounded-2 small mb-3" role="alert">
              <span>{{ session('error') }}</span>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endif

          {{-- Segmented Role Tabs --}}
          <nav class="ts-auth-role-tabs" aria-label="Chọn phân quyền đăng nhập">
            <a href="{{ route('patient.login') }}"
               class="ts-auth-role-tab {{ $role === 'patient' ? 'is-active' : '' }}">
              <i class="fa-regular fa-user"></i>
              <span>Bệnh nhân</span>
            </a>
            <a href="{{ route('doctor.login') }}"
               class="ts-auth-role-tab {{ $role === 'doctor' ? 'is-active' : '' }}">
              <i class="fa-solid fa-user-doctor"></i>
              <span>Bác sĩ</span>
            </a>
            <a href="{{ route('admin.login') }}"
               class="ts-auth-role-tab {{ $role === 'admin' ? 'is-active' : '' }}">
              <i class="fa-solid fa-shield-halved"></i>
              <span>Quản trị</span>
            </a>
          </nav>

          {{-- 1-Click Demo Accounts Helper --}}
          <div class="ts-demo-strip">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <span style="font-size: 13px; font-weight: 700; color: var(--heading-color);">
                Tài khoản mẫu dùng thử nhanh
              </span>
              <span class="med-badge-spec" id="demoFillStatus">Điền tự động 1 chạm</span>
            </div>
            <div class="ts-demo-grid">
              <button type="button"
                      onclick="fillDemo('patient1@thaithinh.vn', 'demo123', this, 'Bệnh nhân')"
                      class="ts-spec-btn {{ $role === 'patient' ? 'is-active' : '' }}">
                <span>Bệnh nhân</span>
              </button>
              <button type="button"
                      onclick="fillDemo('hung.nguyen@thaithinh.vn', 'doctor123', this, 'Bác sĩ')"
                      class="ts-spec-btn {{ $role === 'doctor' ? 'is-active' : '' }}">
                <span>Bác sĩ</span>
              </button>
              <button type="button"
                      onclick="fillDemo('admin@thaithinh.vn', 'admin123', this, 'Quản trị')"
                      class="ts-spec-btn {{ $role === 'admin' ? 'is-active' : '' }}">
                <span>Quản trị</span>
              </button>
            </div>
          </div>

          {{-- Main Login Form --}}
          <form method="POST" action="{{ $loginAction }}" id="mainLoginForm">
            @csrf
            <input type="hidden" name="requested_role" value="{{ $role }}">

            {{-- Email Input --}}
            <div class="mb-3">
              <label for="email" class="tiki-form-label">Địa chỉ Email</label>
              <input id="email"
                     type="email"
                     class="tiki-form-control w-100 @error('email') is-invalid @enderror"
                     name="email"
                     value="{{ old('email') }}"
                     required
                     autocomplete="username"
                     placeholder="Ví dụ: patient1@thaithinh.vn">
              @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>

            {{-- Password Input --}}
            <div class="mb-3">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <label for="password" class="tiki-form-label mb-0">Mật khẩu</label>
                @if (Route::has('password.request'))
                  <a href="{{ route('password.request') }}"
                     style="font-size: 13px; font-weight: 600; color: var(--accent-color);">
                    Quên mật khẩu?
                  </a>
                @endif
              </div>
              <div class="ts-password-group">
                <input id="password"
                       type="password"
                       class="tiki-form-control w-100 @error('password') is-invalid @enderror"
                       name="password"
                       required
                       autocomplete="current-password"
                       placeholder="Nhập mật khẩu của bạn">
                <button class="ts-password-toggle"
                        type="button"
                        onclick="togglePassword('password', this)"
                        aria-label="Hiện hoặc ẩn mật khẩu">
                  <i class="fa-regular fa-eye"></i>
                </button>
              </div>
              @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>

            {{-- Remember Me --}}
            <div class="form-check mb-4">
              <input class="form-check-input" type="checkbox" name="remember" id="remember">
              <label class="form-check-label" for="remember" style="font-size: 13.5px; color: var(--default-color);">
                Ghi nhớ đăng nhập trên thiết bị này
              </label>
            </div>

            {{-- Submit Button --}}
            <button type="submit" class="ts-btn w-100 mb-3" style="min-height: 44px;">
              <i class="fa-solid fa-right-to-bracket"></i>
              <span>
                @if($role === 'admin')
                  Đăng nhập Quản trị viên
                @elseif($role === 'doctor')
                  Đăng nhập Cổng Bác sĩ
                @else
                  Đăng nhập Cổng Bệnh nhân
                @endif
              </span>
            </button>
          </form>
        </div>

        {{-- Register & Quick Action Footer --}}
        <div class="ts-auth-footer-box">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
            <span style="font-size: 13.5px; color: var(--default-color);">
              Chưa có hồ sơ khám bệnh trực tuyến?
            </span>
            <a href="{{ route('register') }}" style="font-size: 13.5px; font-weight: 700; color: var(--accent-color);">
              Đăng ký tài khoản mới →
            </a>
          </div>
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 pt-2" style="border-top: 1px solid var(--border-color);">
            <a href="{{ url('/') }}" class="ts-link-action" style="font-size: 13px;">
              <span>← Về trang chủ 125 Thái Thịnh</span>
            </a>
            <a href="{{ route('advisor.index') }}" class="ts-link-action" style="font-size: 13px;">
              <span>Tư vấn triệu chứng AI</span>
            </a>
          </div>
        </div>
      </section>
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script>
  function togglePassword(id, btn) {
    const input = document.getElementById(id);
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
      input.type = 'text';
      icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
      input.type = 'password';
      icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
  }

  function fillDemo(email, password, btn, roleLabel) {
    const emailInput = document.getElementById('email');
    const passInput = document.getElementById('password');
    const statusBadge = document.getElementById('demoFillStatus');

    document.querySelectorAll('.ts-demo-grid .ts-spec-btn').forEach(function (el) {
      el.classList.remove('is-active');
    });
    if (btn) {
      btn.classList.add('is-active');
    }

    if (emailInput) {
      emailInput.value = email;
      emailInput.focus();
    }
    if (passInput) {
      passInput.value = password;
    }
    if (statusBadge && roleLabel) {
      statusBadge.textContent = 'Đã điền: ' + roleLabel;
    }
  }
</script>
@endpush
