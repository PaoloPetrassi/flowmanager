<?php

namespace App\Http\Controllers;

use App\Models\LoginActivity;
use App\Services\AuditService;
use App\Services\TotpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class SecurityController extends Controller
{
    public function index(Request $request, TotpService $totp): View
    {
        $user = $request->user();
        $sessions = collect();

        if (config('session.driver') === 'database' && Schema::hasTable('sessions')) {
            $sessions = DB::table('sessions')
                ->where('user_id', $user->id)
                ->orderByDesc('last_activity')
                ->get()
                ->map(function ($session) use ($request) {
                    $session->is_current = $session->id === $request->session()->getId();
                    $session->last_active_at = \Illuminate\Support\Carbon::createFromTimestamp($session->last_activity);
                    return $session;
                });
        }

        $pendingSecret = $user->two_factor_secret && ! $user->two_factor_confirmed_at
            ? $user->two_factor_secret
            : null;

        return view('security.index', [
            'user' => $user,
            'sessions' => $sessions,
            'loginActivities' => $user->loginActivities()->latest('created_at')->limit(15)->get(),
            'pendingSecret' => $pendingSecret,
            'provisioningUri' => $pendingSecret ? $totp->provisioningUri($pendingSecret, $user->email) : null,
        ]);
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $request->user()->update([
            'password' => $validated['password'],
            'password_changed_at' => now(),
        ]);

        AuditService::record($request->user(), 'password_changed');

        return back()->with('status', __('Password updated successfully.'));
    }

    public function beginTwoFactor(Request $request, TotpService $totp): RedirectResponse
    {
        $request->validate(['current_password' => ['required', 'current_password']]);

        $request->user()->forceFill([
            'two_factor_secret' => $totp->generateSecret(),
            'two_factor_confirmed_at' => null,
        ])->save();

        return back()->with('status', __('Two-factor setup started. Enter a code to confirm it.'));
    }

    public function confirmTwoFactor(Request $request, TotpService $totp): RedirectResponse
    {
        $validated = $request->validate(['code' => ['required', 'digits:6']]);
        $user = $request->user();

        if (! $user->two_factor_secret || ! $totp->verify($user->two_factor_secret, $validated['code'])) {
            return back()->withErrors(['code' => __('The authentication code is invalid.')]);
        }

        $user->forceFill(['two_factor_confirmed_at' => now()])->save();
        AuditService::record($user, 'two_factor_enabled');

        return back()->with('status', __('Two-factor authentication enabled.'));
    }

    public function disableTwoFactor(Request $request): RedirectResponse
    {
        $request->validate(['current_password' => ['required', 'current_password']]);

        $request->user()->forceFill([
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        AuditService::record($request->user(), 'two_factor_disabled');

        return back()->with('status', __('Two-factor authentication disabled.'));
    }

    public function destroySession(Request $request, string $session): RedirectResponse
    {
        abort_unless(config('session.driver') === 'database' && Schema::hasTable('sessions'), 404);

        DB::table('sessions')
            ->where('id', $session)
            ->where('user_id', $request->user()->id)
            ->where('id', '!=', $request->session()->getId())
            ->delete();

        return back()->with('status', __('Session terminated.'));
    }

    public function destroyOtherSessions(Request $request): RedirectResponse
    {
        $request->validate(['current_password' => ['required', 'current_password']]);

        if (config('session.driver') === 'database' && Schema::hasTable('sessions')) {
            DB::table('sessions')
                ->where('user_id', $request->user()->id)
                ->where('id', '!=', $request->session()->getId())
                ->delete();
        }

        return back()->with('status', __('Other sessions terminated.'));
    }
}
