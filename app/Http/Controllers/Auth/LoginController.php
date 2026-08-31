<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\LoginActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = Auth::user();

        if ($user?->hasTwoFactorEnabled()) {
            $request->session()->put([
                'login.2fa.user_id' => $user->id,
                'login.2fa.remember' => $request->boolean('remember'),
            ]);

            Auth::guard('web')->logout();

            return redirect()->route('two-factor.challenge');
        }

        $request->session()->regenerate();

        if ($user) {
            $user->forceFill([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ])->saveQuietly();

            LoginActivity::create([
                'user_id' => $user->id,
                'email' => $user->email,
                'event' => 'login',
                'ip_address' => $request->ip(),
                'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
            ]);
        }

        return redirect()
            ->intended(route('dashboard'))
            ->with('status', __('Welcome back.'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user) {
            LoginActivity::create([
                'user_id' => $user->id,
                'email' => $user->email,
                'event' => 'logout',
                'ip_address' => $request->ip(),
                'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
            ]);
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('status', __('You have been signed out.'));
    }
}
