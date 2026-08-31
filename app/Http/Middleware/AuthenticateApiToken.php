<?php

namespace App\Http\Middleware;

use App\Models\ApiToken;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $plain = $request->bearerToken();
        if (! $plain) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }
        $token = ApiToken::with('user')->where('token_hash', hash('sha256', $plain))->first();
        if (! $token || ($token->expires_at && $token->expires_at->isPast())) {
            return response()->json(['message' => 'Invalid or expired token.'], 401);
        }
        $token->forceFill(['last_used_at' => now()])->saveQuietly();
        Auth::setUser($token->user);
        $request->attributes->set('apiToken', $token);

        return $next($request);
    }
}
