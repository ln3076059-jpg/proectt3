<?php

use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PatientAuthController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);
});

// Login dùng chung: mặc định coi như đăng nhập bệnh nhân
Route::get('login', function () {
    return redirect()->route('patient.login');
})->name('login');

// Cho phép form nào còn POST tới /login vẫn dùng logic đăng nhập bệnh nhân
Route::post('login', [PatientAuthController::class, 'login']);

Route::middleware('auth')->group(function () {
    // removed: email verification routes
    // removed: confirm password routes

    // Update password in profile
    Route::put('password', [PasswordController::class, 'update'])->name('password.update');
});

// Logout chung (dùng cho tất cả layout đang gọi route('logout'))
Route::post('logout', function (Request $request) {
    // Xác định user đang đăng nhập thuộc guard nào để redirect đúng nơi
    $roleName = null;

    if (Auth::guard('web_admin')->check()) {
        $roleName = 'admin';
    } elseif (Auth::guard('web_doctor')->check()) {
        $roleName = 'doctor';
    } elseif (Auth::guard('web')->check()) {
        $roleName = 'patient';
    }

    foreach (['web', 'web_admin', 'web_doctor'] as $guard) {
        try {
            Auth::guard($guard)->logout();
        } catch (\Throwable $e) {
            // ignore
        }
    }

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    if ($roleName === 'admin') {
        return redirect()->route('admin.login');
    }

    if ($roleName === 'doctor') {
        return redirect()->route('doctor.login');
    }

    return redirect()->route('patient.login');
})->name('logout');
