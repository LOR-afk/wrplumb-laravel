<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureInspector
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $role = strtolower(trim((string) Auth::user()->role));

        if (!in_array($role, ['inspector', 'worker'], true)) {
            abort(403);
        }

        return $next($request);
    }
}