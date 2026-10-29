<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    // Hiển thị form yêu cầu reset mật khẩu
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    // Gửi email chứa link reset mật khẩu đến người dùng
    public function sendResetLinkEmail(Request $request)
    {
        // Validate email đầu vào
        $request->validate([
            'email' => 'required|email',
        ]);

        // Gửi link reset mật khẩu qua email
        $status = Password::sendResetLink($request->only('email'));

        // Trả về thông báo thành công hoặc lỗi
        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }
}
