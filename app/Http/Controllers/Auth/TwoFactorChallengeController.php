<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LoginActivity;
use App\Models\User;
use App\Services\TotpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TwoFactorChallengeController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('login.2fa.user_id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    public function store(Request $request, TotpService $totp): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $userId = $request->session()->get('login.2fa.user_id');
        $user = User::query()->find($userId);

        if (! $user || ! $user->two_factor_secret || ! $totp->verify($user->two_factor_secret, (string) $request->input('code'))) {
            LoginActivity::create([
                'user_id' => $user?->id,
                'email' => $user?->email ?? 'unknown',
                'event' => 'two_factor_failed',
                'ip_address' => $request->ip(),
                'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
            ]);

            return back()->withErrors(['code' => __('The authentication code is invalid.')]);
        }

        $remember = (bool) $request->session()->pull('login.2fa.remember', false);
        $request->session()->forget('login.2fa.user_id');

        Auth::login($user, $remember);
        $request->session()->regenerate();

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

        return redirect()->intended(route('dashboard'))->with('status', __('Welcome back.'));
    }
}
