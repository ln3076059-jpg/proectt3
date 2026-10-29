<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::guard('web_admin')->user() ?? Auth::user();

        if (! $user) {
            return redirect()->route('admin.login');
        }

        $roleName = $user->role ? strtolower(trim($user->role->name)) : null;

        if (! in_array($roleName, ['admin', 'leader'], true) && (int) $user->role_id !== 3) {
            return redirect('/')->with('error', 'Bạn không có quyền truy cập vào khu vực quản trị.');
        }

        return $next($request);
    }
}
