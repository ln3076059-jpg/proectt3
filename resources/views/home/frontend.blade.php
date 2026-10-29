<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Medilab 125 Thái Thịnh · Hệ thống Đặt lịch Khám Chuyên khoa')</title>

  <script>document.documentElement.classList.add('js');</script>

  <!-- Favicons -->
  <link href="{{ asset('assets/img/favicon.png') }}" rel="icon">
  <link href="{{ asset('assets/img/apple-touch-icon.png') }}" rel="apple-touch-icon">

  <!-- Fonts (Medilab Template: Roboto, Poppins, Raleway) -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" crossorigin rel="preconnect">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/aos/aos.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/glightbox/css/glightbox.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/swiper/swiper-bundle.min.css') }}" rel="stylesheet">

  <!-- Main Medilab CSS File -->
  <link href="{{ asset('assets/css/main.css') }}" rel="stylesheet">
  <link href="{{ asset('css/custom.css') }}" rel="stylesheet">

  @stack('styles')
</head>
<body class="index-page">
  @include('layouts.header')

  <main class="main" id="top">
    @yield('content')
  </main>

  @include('chat.user', ['receiverId' => $defaultChatAdminId ?? null])

  @if(session('success') || session('error') || session('warning') || session('info'))
    @php
      $toastType = session('error') ? 'danger' : (session('warning') ? 'warning' : (session('info') ? 'info' : 'success'));
      $toastMsg = session('error') ?? session('warning') ?? session('info') ?? session('success');
    @endphp
    <div id="globalToastContainer" class="alert alert-{{ $toastType }} shadow-lg d-flex align-items-center justify-content-between py-3 px-4 mb-0" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 1090; min-width: 300px; max-width: 440px; border-radius: 8px;">
      <span class="fw-semibold small pe-2">{{ $toastMsg }}</span>
      <button type="button" class="btn-close ms-2" onclick="document.getElementById('globalToastContainer').remove();" aria-label="Đóng"></button>
    </div>
    <script>
      setTimeout(function () {
        var t = document.getElementById('globalToastContainer');
        if (t) t.remove();
      }, 5000);
    </script>
  @endif

  @include('layouts.footer')

  <!-- Vendor JS Files -->
  <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('assets/vendor/aos/aos.js') }}"></script>
  <script src="{{ asset('assets/vendor/glightbox/js/glightbox.min.js') }}"></script>
  <script src="{{ asset('assets/vendor/purecounter/purecounter_vanilla.js') }}"></script>
  <script src="{{ asset('assets/vendor/swiper/swiper-bundle.min.js') }}"></script>

  <!-- Main Medilab JS File -->
  <script src="{{ asset('assets/js/main.js') }}"></script>
  @stack('scripts')
</body>
</html>
