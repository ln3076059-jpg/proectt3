@extends('home.frontend')

@section('title', 'Đăng ký Hồ sơ Bệnh nhân · Medilab 125 Thái Thịnh')

@section('content')
<!-- Page Title -->
<div class="page-title" data-aos="fade">
  <nav class="breadcrumbs">
    <div class="container">
      <ol>
        <li><a href="{{ url('/') }}">Trang chủ</a></li>
        <li class="current">Đăng ký Hồ sơ Bệnh nhân</li>
      </ol>
    </div>
  </nav>
</div><!-- End Page Title -->

<section class="section light-background py-5">
  <div class="container">
    <div class="ts-auth-grid">
      {{-- Left Column: Clinical Context --}}
      <section class="ts-auth-context-card" aria-label="Quyền lợi Hồ sơ Bệnh nhân 125 Thái Thịnh">
        <div>
          <div class="ts-auth-photo-wrap">
            <img src="{{ asset('assets/img/about.jpg') }}"
                 alt="Sảnh tiếp đón Phòng khám Đa khoa 125 Thái Thịnh"
                 width="720"
                 height="380">
            <div class="tiki-booking-photo-overlay">
              <span class="tiki-live-pill">
                <span class="tiki-hero-badge-dot"></span>
                Cấp mã hồ sơ bệnh nhân điện tử
              </span>
              <span class="tiki-time-pill ts-num">Miễn phí 100%</span>
            </div>
          </div>

          <h2 style="font-size: 20px; font-weight: 700; margin-bottom: 6px; color: var(--heading-color);">
            Mở Hồ sơ Khám bệnh Trực tuyến 125 Thái Thịnh
          </h2>
          <p style="font-size: 14px; color: var(--default-color); margin-bottom: 0;">
            Tạo tài khoản một lần để đặt lịch khám nhanh không chờ đợi và lưu trữ trọn đời lịch sử khám chữa bệnh tại Phòng khám Đa khoa 125 Thái Thịnh.
          </p>

          <div class="ts-auth-feature-list">
            <div class="ts-auth-feature-item">
              <span class="ts-auth-feature-icon"><i class="fa-solid fa-qrcode"></i></span>
              <div>
                <div style="font-size: 14px; font-weight: 700; color: var(--heading-color); margin-bottom: 2px;">
                  Cấp Mã QR Ưu tiên ngay khi đặt lịch
                </div>
                <div style="font-size: 13px; color: var(--default-color); line-height: 1.45;">
                  Điền sẵn thông tin hành chính để nhận ngay mã QR check-in tại quầy tiếp đón tầng 1 chỉ trong 30 giây.
                </div>
              </div>
            </div>

            <div class="ts-auth-feature-item">
              <span class="ts-auth-feature-icon"><i class="fa-solid fa-vial-circle-check"></i></span>
              <div>
                <div style="font-size: 14px; font-weight: 700; color: var(--heading-color); margin-bottom: 2px;">
                  Nhận Kết quả Xét nghiệm &amp; Đơn thuốc Online
                </div>
                <div style="font-size: 13px; color: var(--default-color); line-height: 1.45;">
                  Tự động đồng bộ kết quả xét nghiệm máu ISO 15189, siêu âm 5D và lịch tái khám theo từng đợt điều trị.
                </div>
              </div>
            </div>

            <div class="ts-auth-feature-item">
              <span class="ts-auth-feature-icon"><i class="fa-solid fa-user-shield"></i></span>
              <div>
                <div style="font-size: 14px; font-weight: 700; color: var(--heading-color); margin-bottom: 2px;">
                  Bảo mật Hồ sơ Y tế Chuẩn Bộ Y tế
                </div>
                <div style="font-size: 13px; color: var(--default-color); line-height: 1.45;">
                  Thông tin cá nhân và bệnh án được phân quyền nghiêm ngặt chỉ dành cho bệnh nhân và bác sĩ điều trị.
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

      {{-- Right Column: Register Form --}}
      <section class="ts-auth-form-card" aria-label="Biểu mẫu đăng ký tài khoản bệnh nhân">
        <div>
          <div class="mb-3">
            <h1 style="font-size: 24px; font-weight: 700; color: var(--heading-color); margin-bottom: 6px;">
              Đăng ký tài khoản bệnh nhân
            </h1>
            <p style="font-size: 14px; color: var(--default-color); margin-bottom: 0;">
              Điền thông tin hành chính khớp với Căn cước công dân / Thẻ BHYT để thuận tiện làm thủ tục khám.
            </p>
          </div>

          {{-- Validation Errors Alert --}}
          @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show border rounded-2 small mb-3" role="alert">
              <div class="d-flex align-items-center mb-1">
                <strong>Vui lòng kiểm tra lại thông tin:</strong>
              </div>
              <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endif

          {{-- Register Form --}}
          <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="row g-3 mb-2">
              {{-- Full Name --}}
              <div class="col-12 col-sm-7">
                <label for="name" class="tiki-form-label">Họ và tên <span class="text-danger">*</span></label>
                <input id="name"
                       name="name"
                       type="text"
                       value="{{ old('name') }}"
                       required
                       autocomplete="name"
                       placeholder="Nhập họ và tên đầy đủ"
                       class="tiki-form-control w-100 @error('name') is-invalid @enderror">
                @error('name')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>

              {{-- Phone Number --}}
              <div class="col-12 col-sm-5">
                <label for="phone" class="tiki-form-label">Số điện thoại</label>
                <input id="phone"
                       name="phone"
                       type="tel"
                       value="{{ old('phone') }}"
                       placeholder="0912 345 678"
                       class="tiki-form-control w-100 @error('phone') is-invalid @enderror">
                @error('phone')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
            </div>

            {{-- Birthdate --}}
            <div class="mb-3">
              <label class="tiki-form-label">Ngày sinh <span class="text-danger">*</span></label>
              <div class="row g-2">
                <div class="col-4">
                  <select id="birth_day" class="tiki-form-select w-100" aria-label="Ngày sinh">
                    <option value="">Ngày</option>
                    @for ($d = 1; $d <= 31; $d++)
                      <option value="{{ $d }}" {{ old('birth_day') == $d ? 'selected' : '' }}>Ngày {{ $d }}</option>
                    @endfor
                  </select>
                </div>
                <div class="col-4">
                  <select id="birth_month" class="tiki-form-select w-100" aria-label="Tháng sinh">
                    <option value="">Tháng</option>
                    @for ($m = 1; $m <= 12; $m++)
                      <option value="{{ $m }}" {{ old('birth_month') == $m ? 'selected' : '' }}>Tháng {{ $m }}</option>
                    @endfor
                  </select>
                </div>
                <div class="col-4">
                  <input id="birth_year"
                         type="number"
                         min="1900"
                         max="{{ now()->year }}"
                         placeholder="Năm (VD: 1992)"
                         value="{{ old('birth_year') }}"
                         aria-label="Năm sinh"
                         class="tiki-form-control w-100">
                </div>
              </div>
              <input type="hidden" id="birthdate" name="birthdate" value="{{ old('birthdate') }}">
              <p id="birthdate-client-error" class="text-danger small mt-1 d-none">
                Vui lòng chọn đầy đủ Ngày, Tháng và nhập Năm sinh.
              </p>
              @error('birthdate')
                <div class="text-danger small mt-1">{{ $message }}</div>
              @enderror
            </div>

            {{-- Email --}}
            <div class="mb-3">
              <label for="email" class="tiki-form-label">Địa chỉ Email <span class="text-danger">*</span></label>
              <input id="email"
                     name="email"
                     type="email"
                     value="{{ old('email') }}"
                     required
                     autocomplete="username"
                     placeholder="Nhập email nhận phiếu khám QR"
                     class="tiki-form-control w-100 @error('email') is-invalid @enderror">
              @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>

            <div class="row g-2 mb-3">
              {{-- Password --}}
              <div class="col-12 col-sm-6">
                <label for="password" class="tiki-form-label">Mật khẩu <span class="text-danger">*</span></label>
                <div class="ts-password-group">
                  <input id="password"
                         name="password"
                         type="password"
                         required
                         autocomplete="new-password"
                         placeholder="Tối thiểu 8 ký tự"
                         class="tiki-form-control w-100 @error('password') is-invalid @enderror">
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

              {{-- Confirm Password --}}
              <div class="col-12 col-sm-6">
                <label for="password_confirmation" class="tiki-form-label">Nhập lại mật khẩu <span class="text-danger">*</span></label>
                <div class="ts-password-group">
                  <input id="password_confirmation"
                         name="password_confirmation"
                         type="password"
                         required
                         autocomplete="new-password"
                         placeholder="Xác nhận mật khẩu"
                         class="tiki-form-control w-100">
                  <button class="ts-password-toggle"
                          type="button"
                          onclick="togglePassword('password_confirmation', this)"
                          aria-label="Hiện hoặc ẩn mật khẩu xác nhận">
                    <i class="fa-regular fa-eye"></i>
                  </button>
                </div>
              </div>
            </div>

            {{-- Terms Checkbox --}}
            <div class="form-check mb-3">
              <input id="terms" name="terms" type="checkbox" required class="form-check-input">
              <label for="terms" class="form-check-label" style="font-size: 13px; color: var(--default-color);">
                Tôi đồng ý với <a href="{{ route('introduces.index') }}" target="_blank" style="color: var(--accent-color); font-weight: 600;">Điều khoản dịch vụ</a> &amp;
                <a href="{{ route('introduces.index') }}" target="_blank" style="color: var(--accent-color); font-weight: 600;">Chính sách bảo mật hồ sơ y tế</a>
              </label>
            </div>

            {{-- Submit Button --}}
            <button type="submit" class="ts-btn w-100 mb-3" style="min-height: 44px;">
              <i class="fa-solid fa-user-plus"></i>
              <span>Tạo hồ sơ &amp; Đăng ký tài khoản</span>
            </button>
          </form>
        </div>

        {{-- Login Link --}}
        <div class="ts-auth-footer-box">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
            <span style="font-size: 13.5px; color: var(--default-color);">
              Đã có tài khoản khám bệnh?
            </span>
            <a href="{{ route('patient.login') }}" style="font-size: 13.5px; font-weight: 700; color: var(--accent-color);">
              Đăng nhập Cổng Bệnh nhân →
            </a>
          </div>
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 pt-2" style="border-top: 1px solid var(--border-color);">
            <a href="{{ url('/') }}" class="ts-link-action" style="font-size: 13px;">
              <span>← Về trang chủ 125 Thái Thịnh</span>
            </a>
            <a href="{{ route('advisor.index') }}" class="ts-link-action" style="font-size: 13px;">
              <span>Tư vấn triệu chứng</span>
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

  $(document).ready(function() {
    function pad(n) {
      return (n < 10 ? '0' + n : n);
    }

    function updateHiddenBirthdate() {
      const d = parseInt($('#birth_day').val(), 10);
      const m = parseInt($('#birth_month').val(), 10);
      const y = parseInt($('#birth_year').val(), 10);
      if (d && m && y) {
        $('#birthdate').val(`${y}-${pad(m)}-${pad(d)}`);
      }
    }

    const oldVal = $('#birthdate').val();
    if (oldVal) {
      let y, m, d;
      if (/^\d{2}\/\d{2}\/\d{4}$/.test(oldVal)) {
        const parts = oldVal.split('/');
        d = parseInt(parts[0], 10);
        m = parseInt(parts[1], 10);
        y = parseInt(parts[2], 10);
      } else if (/^\d{4}-\d{2}-\d{2}$/.test(oldVal)) {
        const parts = oldVal.split('-');
        y = parseInt(parts[0], 10);
        m = parseInt(parts[1], 10);
        d = parseInt(parts[2], 10);
      }
      if (y) $('#birth_year').val(y);
      if (m) $('#birth_month').val(m);
      if (d) $('#birth_day').val(d);
    }

    $('#birth_day, #birth_month, #birth_year').on('change keyup', updateHiddenBirthdate);

    $('form[action="{{ route('register') }}"]').on('submit', function(e) {
      updateHiddenBirthdate();
      if (!$('#birthdate').val()) {
        e.preventDefault();
        $('#birthdate-client-error').removeClass('d-none');
        $('#birth_day').focus();
      } else {
        $('#birthdate-client-error').addClass('d-none');
      }
    });
  });
</script>
@endpush
