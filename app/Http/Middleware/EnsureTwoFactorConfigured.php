<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorConfigured
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (
            ! $user?->isDemoAccount()
            && config('flowmanager.security.two_factor_required_for_administrators')
            && $user?->hasRole('administrator')
            && ! $user->hasTwoFactorEnabled()
            && ! $request->routeIs('security.*', 'logout')
        ) {
            return redirect()->route('security.index')
                ->with('error', __('Two-factor authentication is required for administrators.'));
        }

        return $next($request);
    }
}
