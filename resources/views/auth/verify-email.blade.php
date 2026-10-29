<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Xác thực Email - Phòng khám Đa khoa 125 Thái Thịnh</title>
    <link rel="icon" type="image/png" href="{{ asset('image/favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('image/favicon.png') }}">

    {{-- Bootstrap 5.3.2 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Font Awesome & Icons --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    {{-- Google Fonts - Inter --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #F8FAFC;
            color: #1E293B;
        }
        .auth-card {
            max-width: 480px;
            width: 100%;
            background: #FFFFFF;
            border-radius: 20px;
            box-shadow: 0 10px 30px -5px rgba(2, 132, 199, 0.08), 0 4px 6px -2px rgba(0, 0, 0, 0.03);
            border: 1px solid #E2E8F0;
        }
        .btn-brand-primary {
            background-color: #0284C7;
            border-color: #0284C7;
            color: #FFFFFF;
            transition: all 0.2s ease;
        }
        .btn-brand-primary:hover, .btn-brand-primary:focus {
            background-color: #0369A1;
            border-color: #0369A1;
            color: #FFFFFF;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.25);
        }
    </style>
</head>
<body class="min-vh-100 d-flex align-items-center justify-content-center p-3 p-md-4">
    <div class="auth-card p-4 p-md-5">
        {{-- Header / Logo --}}
        <div class="text-center mb-4">
            <a href="{{ url('/') }}" class="text-decoration-none d-inline-flex flex-column align-items-center mb-2">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center text-white mb-2 shadow-sm"
                     style="width: 58px; height: 58px; background: linear-gradient(135deg, #0284C7 0%, #0369A1 100%); font-size: 24px;">
                    <i class="fa-solid fa-hospital"></i>
                </div>
                <h4 class="fw-bold mb-0 text-primary" style="letter-spacing: -0.01em;">
                    THÁI THỊNH CLINIC
                </h4>
            </a>
            <p class="text-muted small mb-0">Xác thực tài khoản khách hàng</p>
        </div>

        {{-- Title & Info --}}
        <div class="text-center pb-2 mb-3 border-bottom">
            <h5 class="fw-bold text-dark mb-2">Xác thực địa chỉ Email</h5>
            <p class="text-muted small mb-0" style="line-height: 1.6;">
                Cảm ơn bạn đã đăng ký tài khoản! Chúng tôi đã gửi một liên kết xác thực tới hòm thư của bạn. Vui lòng kiểm tra email để kích hoạt tài khoản khám bệnh.
            </p>
        </div>

        {{-- Status Alert --}}
        @if (session('status') == 'verification-link-sent')
            <div class="alert alert-success alert-dismissible fade show border-0 rounded-3 shadow-xs small mb-3 text-center" role="alert">
                <i class="fa-solid fa-circle-check text-success me-1"></i>
                <span>Liên kết xác thực mới đã được gửi lại vào email của bạn!</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Actions --}}
        <div class="d-flex flex-column gap-2 mt-3">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="btn btn-brand-primary w-100 rounded-pill py-2.5 fw-semibold">
                    <i class="fa-solid fa-paper-plane me-1.5"></i> Gửi lại email xác thực
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-secondary w-100 rounded-pill py-2 small">
                    <i class="fa-solid fa-right-from-bracket me-1.5"></i> Đăng xuất tài khoản
                </button>
            </form>
        </div>
    </div>

    {{-- Bootstrap Bundle JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
