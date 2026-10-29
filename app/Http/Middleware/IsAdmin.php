<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = auth('web_admin')->user() ?? auth()->user();

        if ($user) {
            $roleName = $user->role ? strtolower(trim($user->role->name)) : null;
            if (in_array($roleName, ['admin', 'leader'], true) || (int) $user->role_id === 3) {
                return $next($request);
            }
        }

        // Nếu không phải admin → chuyển hướng
        return redirect('/')->with('error', 'Bạn không có quyền truy cập vào trang này!');
    }
}
