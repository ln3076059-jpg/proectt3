<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class DoctorAuthController extends Controller
{
    // Hiển thị form đăng nhập cho bác sĩ
    public function showLoginForm(Request $request): View
    {
        return view('auth.login', ['role' => 'doctor']);
    }

    // Xử lý đăng nhập cho bác sĩ (hỗ trợ tự động phân luồng theo vai trò)
    public function login(Request $request): RedirectResponse
    {
        // Validate dữ liệu đầu vào
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $throttleKey = \Illuminate\Support\Str::transliterate(\Illuminate\Support\Str::lower($credentials['email']) . '|' . $request->ip());

        // Chống tấn công Brute-force mật khẩu (tối đa 5 lần thử / phút)
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'email' => "Bạn đã đăng nhập sai quá nhiều lần. Vui lòng thử lại sau {$seconds} giây.",
            ])->withInput();
        }

        $remember = $request->boolean('remember');

        // Tìm người dùng theo email
        $user = User::with(['role', 'doctor'])->where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 60);
            return back()->withErrors([
                'email' => 'Email hoặc mật khẩu không đúng.',
            ])->withInput();
        }

        \Illuminate\Support\Facades\RateLimiter::clear($throttleKey);

        $roleName = $user->role ? strtolower(trim($user->role->name)) : null;

        // Nếu là Bác sĩ
        if ($roleName === 'doctor') {
            if (! $user->doctor) {
                return back()->withErrors([
                    'email' => 'Không tìm thấy hồ sơ bác sĩ. Vui lòng liên hệ quản trị viên.',
                ])->withInput();
            }

            Auth::guard('web_doctor')->login($user, $remember);
            Auth::guard('web')->login($user, $remember);
            $request->session()->regenerate();

            $intended = $request->session()->pull('url.intended');
            if ($intended && (str_contains($intended, '/doctor') || str_contains($intended, 'doctor.'))) {
                return redirect($intended)->with('success', 'Đăng nhập bác sĩ thành công!');
            }

            return redirect()->route('doctor.dashboard')
                ->with('success', 'Đăng nhập bác sĩ thành công!');
        }

        // Nếu là Admin
        if ($roleName === 'admin') {
            Auth::guard('web_admin')->login($user, $remember);
            Auth::guard('web')->login($user, $remember);
            $request->session()->regenerate();

            return redirect()->route('admin.dashboard')
                ->with('success', 'Đăng nhập quản trị viên thành công!');
        }

        // Nếu là Bệnh nhân
        Auth::guard('web')->login($user, $remember);
        $request->session()->regenerate();

        return redirect()->route('home')
            ->with('success', 'Đăng nhập thành công!');
    }

    // Xử lý đăng xuất cho bác sĩ
    public function logout(Request $request): RedirectResponse
    {
        foreach (['web_doctor', 'web', 'web_admin'] as $guard) {
            try {
                Auth::guard($guard)->logout();
            } catch (\Throwable $e) {
                // ignore
            }
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('doctor.login');
    }
}
