<?php

namespace App\Http\Responses;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class RegisterResponse implements RegisterResponseContract
{
    public function toResponse(Request $request)
    {
        $user = $request->user();
        Log::info('Register response for user:', [
            'user_id' => $user ? $user->id : null,
            'role' => ($user && $user->role) ? $user->role->name : null,
        ]);

        if (! $user || ! $user->role) {
            Log::warning('User or role not found after registration');

            return redirect()->intended('/');
        }

        switch ($user->role->name) {
            case 'admin':
                return redirect()->intended('/admin/dashboard');
            case 'doctor':
                return redirect()->intended('/doctor/dashboard');
            case 'leader':
                return redirect()->intended('/leader/dashboard');
            case 'patient':
                return redirect()->intended('/patient/dashboard');
            default:
                return redirect()->intended('/');
        }
    }
}
