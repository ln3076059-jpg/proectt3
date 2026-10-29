<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Tài khoản bệnh nhân') · Medilab 125 Thái Thịnh</title>

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
<body class="starter-page-page">
  @include('layouts.header')

  <main class="main">
    <!-- Page Title (Medilab Breadcrumbs) -->
    <div class="page-title" data-aos="fade">
      <nav class="breadcrumbs">
        <div class="container">
          <ol>
            <li><a href="{{ url('/') }}">Trang chủ</a></li>
            <li><a href="{{ route('appointments.index') }}">Tài khoản bệnh nhân</a></li>
            <li class="current">
              @hasSection('breadcrumb_current')
                @yield('breadcrumb_current')
              @else
                {{ request()->routeIs('appointments.*') ? 'Lịch hẹn của tôi' : 'Thông tin tài khoản' }}
              @endif
            </li>
          </ol>
        </div>
      </nav>
    </div><!-- End Page Title -->

    <section class="section pt-4">
      <div class="container">
        @php
          $patient = Auth::user()->patient ?? null;
          $avatarUrl = ($patient && !empty($patient->avatar))
              ? asset('storage/' . $patient->avatar)
              : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) . '&background=f1f7fc&color=1977cc&bold=true';

          $sidebarPendingCount = 0;
          if ($patient) {
              $sidebarPendingCount = \App\Models\Appointment::where('patient_id', $patient->id)
                  ->whereDate('appointment_date', '>=', \Carbon\Carbon::today())
                  ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
                  ->count();
          }
        @endphp

        {{-- MOBILE / TABLET ACCOUNT NAVIGATION PILLS --}}
        <div class="d-lg-none mb-4">
          <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('appointments.index') }}" class="ts-filter-pill {{ (request()->routeIs('appointments.*') && !request()->routeIs('appointments.create')) ? 'is-active' : '' }}">
              <span>Lịch hẹn của tôi</span>
              @if($sidebarPendingCount > 0)
                <span class="badge rounded-pill bg-white text-primary ms-1">{{ $sidebarPendingCount }}</span>
              @endif
            </a>
            <a href="{{ route('appointments.create') }}" class="ts-filter-pill {{ request()->routeIs('appointments.create') ? 'is-active' : '' }}">
              <span>Đặt lịch khám mới</span>
            </a>
            <a href="{{ route('profile.edit') }}" class="ts-filter-pill {{ request()->routeIs('profile.*') ? 'is-active' : '' }}">
              <span>Hồ sơ cá nhân</span>
            </a>
            <a href="{{ route('advisor.index') }}" class="ts-filter-pill">
              <span>Tư vấn triệu chứng AI</span>
            </a>
          </div>
        </div>

        <div class="row g-4">
          {{-- LEFT ACCOUNT SIDEBAR --}}
          <div class="col-lg-3 d-none d-lg-block">
            <div class="tiki-profile-sidebar sticky-top" style="top: 96px;">
              <div class="tiki-profile-user-card p-3 d-flex align-items-center gap-3" style="background-color: #f1f7fc; border-bottom: 1px solid #d6e8f8;">
                <img src="{{ $avatarUrl }}" class="tiki-profile-avatar" alt="Ảnh đại diện {{ Auth::user()->name }}">
                <div class="text-truncate">
                  <span class="ts-small d-block" style="color: var(--default-color);">Hồ sơ bệnh nhân</span>
                  <strong style="font-size: 15px; color: var(--heading-color); font-family: var(--heading-font);">{{ Auth::user()->name }}</strong>
                  @if($patient && $patient->insurance_number)
                    <span class="ts-small d-block" style="color: #047857;">
                      <i class="bi bi-shield-check me-1"></i>Đã liên kết thẻ BHYT
                    </span>
                  @endif
                </div>
              </div>

              <nav class="tiki-profile-nav p-2 d-flex flex-column gap-1" aria-label="Menu tài khoản bệnh nhân">
                <a href="{{ route('profile.edit') }}" class="tiki-profile-nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                  <i class="bi bi-person-vcard"></i>
                  <span>Thông tin tài khoản</span>
                </a>
                <a href="{{ route('appointments.index') }}" class="tiki-profile-nav-link {{ (request()->routeIs('appointments.*') && !request()->routeIs('appointments.create')) ? 'active' : '' }}">
                  <i class="bi bi-calendar2-check"></i>
                  <span>Lịch hẹn của tôi</span>
                  @if($sidebarPendingCount > 0)
                    <span class="badge rounded-pill bg-primary ms-auto">{{ $sidebarPendingCount }}</span>
                  @endif
                </a>
                <a href="{{ route('appointments.create') }}" class="tiki-profile-nav-link {{ request()->routeIs('appointments.create') ? 'active' : '' }}">
                  <i class="bi bi-calendar2-plus"></i>
                  <span>Đặt lịch khám mới</span>
                </a>
                <a href="{{ route('advisor.index') }}" class="tiki-profile-nav-link">
                  <i class="bi bi-robot"></i>
                  <span>Tư vấn triệu chứng AI</span>
                </a>
                <form method="POST" action="{{ route('logout') }}" class="mt-2 pt-2 border-top">
                  @csrf
                  <button type="submit" class="tiki-profile-nav-link border-0 bg-transparent w-100 text-start text-danger">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Đăng xuất tài khoản</span>
                  </button>
                </form>
              </nav>
            </div>
          </div>

          {{-- MAIN PATIENT CONTENT AREA --}}
          <div class="col-lg-9 col-12">
            @yield('content')
          </div>
        </div>
      </div>
    </section>
  </main>

  @php
    $adminChatUser = \App\Models\User::whereHas('role', function ($q) {
        $q->where('name', 'admin');
    })->first();
  @endphp
  @include('chat.user', ['receiverId' => $adminChatUser->id ?? null])

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
  <script src="{{ asset('js/custom.js') }}"></script>
  <script src="{{ asset('js/chat.js') }}"></script>
  @stack('scripts')
</body>
</html>
