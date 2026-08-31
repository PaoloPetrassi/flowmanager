<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailVerifiedConfigured
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('flowmanager.security.require_email_verification', false)) {
            return $next($request);
        }

        $user = $request->user();

        if (! $user instanceof MustVerifyEmail || $user->hasVerifiedEmail()) {
            return $next($request);
        }

        if ($request->routeIs('verification.*', 'security.*', 'logout')) {
            return $next($request);
        }

        return redirect()->route('verification.notice');
    }
}
