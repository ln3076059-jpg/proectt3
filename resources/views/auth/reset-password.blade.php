@extends('home.frontend')

@section('title', 'Đặt lại mật khẩu · Medilab 125 Thái Thịnh')

@section('content')
<!-- Page Title -->
<div class="page-title" data-aos="fade">
  <nav class="breadcrumbs">
    <div class="container">
      <ol>
        <li><a href="{{ url('/') }}">Trang chủ</a></li>
        <li class="current">Đặt lại mật khẩu</li>
      </ol>
    </div>
  </nav>
</div><!-- End Page Title -->

<section class="section light-background py-5">
  <div class="container" style="max-width: 560px;">
    <section class="ts-auth-form-card" aria-label="Đặt lại mật khẩu mới">
      <div>
        <div class="mb-3">
          <h1 style="font-size: 24px; font-weight: 700; color: var(--heading-color); margin-bottom: 6px;">
            Đặt lại mật khẩu mới
          </h1>
          <p style="font-size: 14px; color: var(--default-color); margin-bottom: 0;">
            Vui lòng thiết lập mật khẩu mới an toàn cho tài khoản của bạn.
          </p>
        </div>

        <form method="POST" action="{{ route('password.store') }}">
          @csrf
          <input type="hidden" name="token" value="{{ $token }}">

          {{-- Email --}}
          <div class="mb-3">
            <label for="email" class="tiki-form-label">Địa chỉ Email</label>
            <input id="email"
                   type="email"
                   name="email"
                   class="tiki-form-control w-100 @error('email') is-invalid @enderror"
                   value="{{ old('email', $request->email ?? '') }}"
                   placeholder="name@example.com"
                   required autofocus>
            @error('email')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </div>

          {{-- New Password --}}
          <div class="mb-3">
            <label for="password" class="tiki-form-label">Mật khẩu mới</label>
            <div class="ts-password-group">
              <input id="password"
                     type="password"
                     name="password"
                     class="tiki-form-control w-100 @error('password') is-invalid @enderror"
                     placeholder="Tối thiểu 8 ký tự"
                     required>
              <button class="ts-password-toggle"
                      type="button"
                      onclick="togglePassword('password', this)">
                <i class="fa-regular fa-eye"></i>
              </button>
            </div>
            @error('password')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </div>

          {{-- Confirm Password --}}
          <div class="mb-4">
            <label for="password_confirmation" class="tiki-form-label">Xác nhận mật khẩu mới</label>
            <div class="ts-password-group">
              <input id="password_confirmation"
                     type="password"
                     name="password_confirmation"
                     class="tiki-form-control w-100"
                     placeholder="Nhập lại mật khẩu mới"
                     required>
              <button class="ts-password-toggle"
                      type="button"
                      onclick="togglePassword('password_confirmation', this)">
                <i class="fa-regular fa-eye"></i>
              </button>
            </div>
          </div>

          <button type="submit" class="ts-btn w-100 mb-3" style="min-height: 44px;">
            <i class="fa-solid fa-check"></i>
            <span>Lưu mật khẩu &amp; Đăng nhập</span>
          </button>
        </form>
      </div>

      <div class="ts-auth-footer-box">
        <div class="d-flex justify-content-between align-items-center">
          <a href="{{ route('login') }}" class="ts-link-action" style="font-size: 13.5px;">
            <span>← Quay lại đăng nhập</span>
          </a>
        </div>
      </div>
    </section>
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
</script>
@endpush
