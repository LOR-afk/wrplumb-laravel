<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminOtpVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->role === 'admin' && $request->session()->get('admin_otp_verified') !== true) {
            return redirect()
                ->route('admin.otp.form')
                ->withErrors([
                    'otp' => 'Please verify the admin OTP before continuing.',
                ]);
        }

        return $next($request);
    }
}
