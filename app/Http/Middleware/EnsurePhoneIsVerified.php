<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsurePhoneIsVerified
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->user()->phone_verified) {
            return redirect()->route('phone.verify');
        }

        return $next($request);
    }
} 