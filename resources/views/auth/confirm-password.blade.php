<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Xác nhận mật khẩu - Phòng khám Đa khoa 125 Thái Thịnh</title>
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
            max-width: 460px;
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
        .input-group-text {
            background-color: #F8FAFC;
            border-color: #CBD5E1;
            color: #64748B;
        }
        .form-control {
            border-color: #CBD5E1;
            font-size: 0.95rem;
        }
        .form-control:focus {
            border-color: #0284C7;
            box-shadow: 0 0 0 0.2rem rgba(2, 132, 199, 0.15);
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
            <p class="text-muted small mb-0">Khu vực bảo mật tài khoản y tế</p>
        </div>

        {{-- Form Title --}}
        <div class="text-center pb-2 mb-3 border-bottom">
            <h5 class="fw-bold text-dark mb-1">Xác nhận mật khẩu</h5>
            <p class="text-muted small mb-0">Vui lòng nhập mật khẩu hiện tại của bạn để tiếp tục thao tác an toàn</p>
        </div>

        {{-- Validation Errors --}}
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show border-0 rounded-3 shadow-xs small mb-3" role="alert">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Form --}}
        <form method="POST" action="{{ route('password.confirm') }}">
            @csrf

            <div class="mb-3">
                <label for="password" class="form-label text-dark small fw-semibold mb-1">
                    <i class="fa-solid fa-lock text-primary me-1"></i> Mật khẩu của bạn
                </label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                    <input id="password"
                           type="password"
                           name="password"
                           required
                           autocomplete="current-password"
                           autofocus
                           class="form-control"
                           placeholder="••••••••">
                </div>
            </div>

            <button type="submit" class="btn btn-brand-primary w-100 rounded-pill py-2.5 fw-semibold mb-3">
                <i class="fa-solid fa-shield-check me-1.5"></i> Xác nhận an toàn
            </button>
        </form>

        <div class="pt-3 border-top text-center small">
            <a href="{{ url()->previous() }}" class="text-secondary text-decoration-none">
                <i class="fa-solid fa-arrow-left me-1"></i> Quay lại trang trước
            </a>
        </div>
    </div>

    {{-- Bootstrap Bundle JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
