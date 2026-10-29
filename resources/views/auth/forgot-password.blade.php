@extends('home.frontend')

@section('title', 'Khôi phục mật khẩu · Medilab 125 Thái Thịnh')

@section('content')
<!-- Page Title -->
<div class="page-title" data-aos="fade">
  <nav class="breadcrumbs">
    <div class="container">
      <ol>
        <li><a href="{{ url('/') }}">Trang chủ</a></li>
        <li class="current">Khôi phục mật khẩu</li>
      </ol>
    </div>
  </nav>
</div><!-- End Page Title -->

<section class="section light-background py-5">
  <div class="container" style="max-width: 560px;">
    <section class="ts-auth-form-card" aria-label="Khôi phục mật khẩu">
      <div>
        <div class="mb-3">
          <h1 style="font-size: 24px; font-weight: 700; color: var(--heading-color); margin-bottom: 6px;">
            Khôi phục mật khẩu tài khoản
          </h1>
          <p style="font-size: 14px; color: var(--default-color); margin-bottom: 0;">
            Nhập địa chỉ email đã đăng ký hồ sơ khám bệnh để nhận liên kết đặt lại mật khẩu mới.
          </p>
        </div>

        {{-- Status Alert --}}
        @if (session('status'))
          <div class="alert alert-success alert-dismissible fade show border rounded-2 small mb-3" role="alert">
            <span>{{ session('status') }}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
          @csrf

          <div class="mb-4">
            <label for="email" class="tiki-form-label">Địa chỉ Email đã đăng ký</label>
            <input id="email"
                   type="email"
                   name="email"
                   class="tiki-form-control w-100 @error('email') is-invalid @enderror"
                   placeholder="Ví dụ: patient1@thaithinh.vn"
                   value="{{ old('email') }}"
                   required
                   autofocus>
            @error('email')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </div>

          <button type="submit" class="ts-btn w-100 mb-3" style="min-height: 44px;">
            <i class="fa-regular fa-paper-plane"></i>
            <span>Gửi liên kết đặt lại mật khẩu</span>
          </button>
        </form>
      </div>

      <div class="ts-auth-footer-box">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
          <a href="{{ route('patient.login') }}" class="ts-link-action" style="font-size: 13.5px;">
            <span>← Quay lại Đăng nhập</span>
          </a>
          <a href="{{ route('register') }}" class="ts-link-action" style="font-size: 13.5px;">
            <span>Đăng ký tài khoản mới →</span>
          </a>
        </div>
      </div>
    </section>
  </div>
</section>
@endsection
