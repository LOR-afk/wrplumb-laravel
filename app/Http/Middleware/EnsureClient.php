<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureClient
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $role = strtolower(trim((string) Auth::user()->role));

        if ($role !== 'client') {
            abort(403);
        }

        return $next($request);
    }
}