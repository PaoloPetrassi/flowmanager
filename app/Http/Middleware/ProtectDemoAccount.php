<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProtectDemoAccount
{
    private const ALLOWED_WRITE_ROUTES = [
        'locale.update',
        'logout',
        'preferences.update',
        'saved-filters.store',
        'saved-filters.default',
        'saved-filters.destroy',
        'notifications.open',
        'notifications.read',
        'notifications.read-all',
        'notifications.destroy',
        'notifications.clear-read',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (
            ! $user?->isDemoAccount()
            || ! config('flowmanager.demo.read_only')
            || in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true)
            || in_array($request->route()?->getName(), self::ALLOWED_WRITE_ROUTES, true)
        ) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => __('Demo mode is read-only. This action is disabled.'),
            ], 403);
        }

        return back()->with(
            'error',
            __('Demo mode is read-only. This action is disabled.')
        );
    }
}
