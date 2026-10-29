<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     * @param string                                                                           ...$roles // nhận nhiều role
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Lấy thông tin user từ request hoặc từ các guard đang hoạt động
        $user = $request->user()
            ?? Auth::guard('web_admin')->user()
            ?? Auth::guard('web_doctor')->user()
            ?? Auth::guard('web')->user();

        // Kiểm tra đăng nhập
        if (! $user) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để tiếp tục');
        }

        // Kiểm tra role
        if (! $user->role) {
            Auth::logout();

            return redirect()->route('login')
                ->withErrors(['email' => 'Tài khoản của bạn chưa được gán vai trò. Vui lòng liên hệ quản trị viên.'])
                ->withInput();
        }

        // Kiểm tra quyền truy cập dựa trên role
        $userRole = strtolower(trim($user->role->name));
        $allowedRoles = array_map('strtolower', $roles);

        if (in_array($userRole, $allowedRoles)) {
            return $next($request);
        }

        // Nếu không có quyền
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Truy cập bị từ chối. Bạn không có quyền thực hiện thao tác này.'], 403);
        }

        return redirect()->back()->with('error', 'Bạn không có quyền truy cập khu vực này.');
    }
}
