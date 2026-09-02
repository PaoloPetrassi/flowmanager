<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LoginActivity;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DemoLoginController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        abort_unless(config('flowmanager.demo.enabled'), 404);

        $user = User::query()
            ->where('email', config('flowmanager.demo.email'))
            ->where('is_demo', true)
            ->first();

        if (! $user) {
            return redirect()
                ->route('login')
                ->withErrors([
                    'demo' => __('The demo account is not available. Run the demo seeder first.'),
                ]);
        }

        Auth::guard('web')->login($user);
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

        $status = config('flowmanager.demo.read_only')
            ? __('Demo mode started. The demo account is read-only.')
            : __('Demo mode started.');

        return redirect()
            ->route('dashboard')
            ->with('status', $status);
    }
}
