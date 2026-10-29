<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AdminAuthController extends Controller
{
    // Hiển thị form đăng nhập cho quản trị viên
    public function showLoginForm(Request $request): View
    {
        return view('auth.login', ['role' => 'admin']);
    }

    // Xử lý đăng nhập cho quản trị viên (hỗ trợ tự động phân luồng theo vai trò)
    public function login(Request $request): RedirectResponse
    {
        // Validate dữ liệu đầu vào
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');

        // Tìm người dùng theo email
        $user = User::with(['role', 'doctor'])->where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors([
                'email' => 'Email hoặc mật khẩu không đúng.',
            ])->withInput();
        }

        $roleName = $user->role ? strtolower(trim($user->role->name)) : null;

        // Nếu là Admin
        if ($roleName === 'admin') {
            Auth::guard('web_admin')->login($user, $remember);
            Auth::guard('web')->login($user, $remember);
            $request->session()->regenerate();

            $intended = $request->session()->pull('url.intended');
            if ($intended && (str_contains($intended, '/admin') || str_contains($intended, 'admin.'))) {
                return redirect($intended)->with('success', 'Đăng nhập quản trị viên thành công!');
            }

            return redirect()->route('admin.dashboard')
                ->with('success', 'Đăng nhập quản trị viên thành công!');
        }

        // Nếu là Bác sĩ nhập tại form admin (tiện ích tự chuyển hướng đúng cổng)
        if ($roleName === 'doctor') {
            if (! $user->doctor) {
                return back()->withErrors([
                    'email' => 'Không tìm thấy thông tin bác sĩ. Vui lòng liên hệ quản trị viên.',
                ])->withInput();
            }

            Auth::guard('web_doctor')->login($user, $remember);
            Auth::guard('web')->login($user, $remember);
            $request->session()->regenerate();

            return redirect()->route('doctor.dashboard')
                ->with('success', 'Đăng nhập bác sĩ thành công!');
        }

        // Nếu là Bệnh nhân
        Auth::guard('web')->login($user, $remember);
        $request->session()->regenerate();

        return redirect()->route('home')
            ->with('success', 'Đăng nhập thành công!');
    }

    // Xử lý đăng xuất cho quản trị viên
    public function logout(Request $request): RedirectResponse
    {
        foreach (['web_admin', 'web', 'web_doctor'] as $guard) {
            try {
                Auth::guard($guard)->logout();
            } catch (\Throwable $e) {
                // ignore
            }
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
