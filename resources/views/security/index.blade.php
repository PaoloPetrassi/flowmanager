@extends('layouts.app')

@section('title', __('Security'))
@section('page-title', __('Security'))
@section('page-subtitle', __('Password, two-factor authentication and active sessions'))

@section('content')
    <div class="row g-4">
        <div class="col-12 col-xl-7">
            <div class="card fm-card mb-4">
                <div class="card-header fm-card-header">
                    <div><h2 class="fm-card-title">{{ __('Account security') }}</h2><p class="fm-card-subtitle">{{ __('Protect your FlowManager account') }}</p></div>
                    <span class="badge {{ $user->hasTwoFactorEnabled() ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $user->hasTwoFactorEnabled() ? __('2FA enabled') : __('2FA disabled') }}</span>
                </div>
                <div class="card-body">
                    <div class="row g-4 align-items-start">
                        <div class="col-12 col-md-6">
                            <h3 class="h6 mb-3">{{ __('Change password') }}</h3>
                            <form method="POST" action="{{ route('security.password') }}">
                                @csrf
                                @method('PUT')
                                <div class="mb-3">
                                    <label class="form-label" for="current_password">{{ __('Current password') }}</label>
                                    <input class="form-control @error('current_password') is-invalid @enderror" type="password" id="current_password" name="current_password" autocomplete="current-password" required>
                                    @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="password">{{ __('New password') }}</label>
                                    <input class="form-control @error('password') is-invalid @enderror" type="password" id="password" name="password" autocomplete="new-password" required>
                                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="password_confirmation">{{ __('Confirm password') }}</label>
                                    <input class="form-control" type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" required>
                                </div>
                                <button class="btn btn-primary" type="submit"><i class="bi bi-key me-1"></i>{{ __('Update password') }}</button>
                            </form>
                        </div>

                        <div class="col-12 col-md-6">
                            <h3 class="h6 mb-3">{{ __('Two-factor authentication') }}</h3>
                            @if ($user->hasTwoFactorEnabled())
                                <div class="fm-security-status is-success mb-3"><i class="bi bi-shield-check"></i><div><strong>{{ __('Your account is protected by 2FA.') }}</strong><small>{{ __('A 6-digit code will be required after your password.') }}</small></div></div>
                                <form method="POST" action="{{ route('security.two-factor.disable') }}">
                                    @csrf
                                    @method('DELETE')
                                    <div class="mb-3">
                                        <label class="form-label" for="disable_current_password">{{ __('Current password') }}</label>
                                        <input class="form-control" type="password" id="disable_current_password" name="current_password" required>
                                    </div>
                                    <button class="btn btn-outline-danger" type="submit" data-confirm="{{ __('Disable two-factor authentication?') }}"><i class="bi bi-shield-x me-1"></i>{{ __('Disable 2FA') }}</button>
                                </form>
                            @elseif ($pendingSecret)
                                <div class="alert alert-info small">{{ __('Add this secret to your authenticator app, then enter a generated code to confirm setup.') }}</div>
                                <div class="fm-secret-box mb-3"><code>{{ $pendingSecret }}</code></div>
                                <div class="small text-secondary text-break mb-3">{{ $provisioningUri }}</div>
                                <form method="POST" action="{{ route('security.two-factor.confirm') }}">
                                    @csrf
                                    <div class="input-group">
                                        <input class="form-control @error('code') is-invalid @enderror" name="code" inputmode="numeric" maxlength="6" placeholder="123456" required>
                                        <button class="btn btn-primary" type="submit">{{ __('Confirm 2FA') }}</button>
                                    </div>
                                    @error('code')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </form>
                            @else
                                <p class="text-secondary small">{{ __('Use any TOTP-compatible authenticator app. No external service is required.') }}</p>
                                <form method="POST" action="{{ route('security.two-factor.begin') }}">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label" for="two_factor_current_password">{{ __('Current password') }}</label>
                                        <input class="form-control" type="password" id="two_factor_current_password" name="current_password" required>
                                    </div>
                                    <button class="btn btn-outline-primary" type="submit"><i class="bi bi-shield-plus me-1"></i>{{ __('Set up 2FA') }}</button>
                                </form>
                            @endif
                        </div>
                    </div>

                    <hr class="my-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <div class="fw-semibold">{{ __('Email verification') }}</div>
                            <div class="small text-secondary">{{ $user->hasVerifiedEmail() ? __('Verified on :date', ['date' => $user->email_verified_at?->format('d/m/Y H:i')]) : __('Your email address is not verified.') }}</div>
                        </div>
                        @unless ($user->hasVerifiedEmail())
                            <a class="btn btn-outline-primary btn-sm" href="{{ route('verification.notice') }}">{{ __('Verify email') }}</a>
                        @endunless
                    </div>
                </div>
            </div>

            <div class="card fm-card">
                <div class="card-header fm-card-header"><div><h2 class="fm-card-title">{{ __('Active sessions') }}</h2><p class="fm-card-subtitle">{{ __('Devices currently signed in with your account') }}</p></div></div>
                <div class="card-body p-0">
                    @if ($sessions->isEmpty())
                        <div class="p-4 text-secondary">{{ __('Session details are available when SESSION_DRIVER=database.') }}</div>
                    @else
                        <div class="table-responsive"><table class="table align-middle mb-0">
                            <thead><tr><th>{{ __('Device') }}</th><th>{{ __('IP address') }}</th><th>{{ __('Last activity') }}</th><th class="text-end">{{ __('Actions') }}</th></tr></thead>
                            <tbody>
                                @foreach ($sessions as $session)
                                    <tr>
                                        <td><div class="fw-semibold">{{ Str::limit($session->user_agent ?: __('Unknown device'), 70) }}</div>@if ($session->is_current)<span class="badge text-bg-success">{{ __('Current session') }}</span>@endif</td>
                                        <td>{{ $session->ip_address ?: '—' }}</td>
                                        <td>{{ $session->last_active_at->diffForHumans() }}</td>
                                        <td class="text-end">
                                            @unless ($session->is_current)
                                                <form method="POST" action="{{ route('security.sessions.destroy', $session->id) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" type="submit">{{ __('Terminate') }}</button></form>
                                            @endunless
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table></div>
                    @endif
                </div>
                @if ($sessions->count() > 1)
                    <div class="card-footer bg-white">
                        <form class="row g-2 align-items-end" method="POST" action="{{ route('security.sessions.destroy-others') }}">
                            @csrf @method('DELETE')
                            <div class="col"><label class="form-label small" for="sessions_password">{{ __('Current password') }}</label><input class="form-control" type="password" id="sessions_password" name="current_password" required></div>
                            <div class="col-auto"><button class="btn btn-outline-danger" type="submit">{{ __('Sign out other sessions') }}</button></div>
                        </form>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-12 col-xl-5">
            <div class="card fm-card">
                <div class="card-header fm-card-header"><div><h2 class="fm-card-title">{{ __('Recent sign-in activity') }}</h2><p class="fm-card-subtitle">{{ __('Successful, failed and blocked authentication attempts') }}</p></div></div>
                <div class="list-group list-group-flush">
                    @forelse ($loginActivities as $activity)
                        <div class="list-group-item py-3">
                            <div class="d-flex justify-content-between gap-3"><strong>{{ __($activity->event) }}</strong><span class="small text-secondary">{{ $activity->created_at->diffForHumans() }}</span></div>
                            <div class="small text-secondary">{{ $activity->ip_address ?: '—' }} · {{ Str::limit($activity->user_agent ?: __('Unknown device'), 64) }}</div>
                        </div>
                    @empty
                        <div class="p-4 text-secondary">{{ __('No sign-in activity recorded yet.') }}</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
