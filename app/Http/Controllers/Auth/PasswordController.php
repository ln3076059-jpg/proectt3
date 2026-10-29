<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    // Cập nhật mật khẩu của người dùng hiện tại (yêu cầu mật khẩu hiện tại và mật khẩu mới phải được xác nhận)
    public function update(Request $request): RedirectResponse
    {
        // Validate dữ liệu đầu vào với error bag riêng
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'], // Kiểm tra mật khẩu hiện tại
            'password' => ['required', Password::defaults(), 'confirmed'], // Mật khẩu mới phải được xác nhận
        ]);

        // Cập nhật mật khẩu mới (đã được hash)
        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        // Trả về thông báo thành công
        return back()->with('status', 'password-updated');
    }
}
