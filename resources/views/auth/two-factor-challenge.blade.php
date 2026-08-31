@extends('layouts.guest')

@section('title', __('Two-factor authentication'))

@section('content')
    <div class="card fm-login-card">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <div class="fm-brand-mark mx-auto mb-3"><i class="bi bi-shield-check"></i></div>
                <h1 class="fm-brand h3 mb-2">{{ __('Two-factor authentication') }}</h1>
                <p class="text-secondary mb-0">{{ __('Enter the 6-digit code from your authenticator app.') }}</p>
            </div>

            <form method="POST" action="{{ route('two-factor.verify') }}">
                @csrf
                <div class="mb-4">
                    <label for="code" class="form-label fm-form-label">{{ __('Authentication code') }}</label>
                    <input id="code" name="code" type="text" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" class="form-control fm-form-control fm-otp-input @error('code') is-invalid @enderror" autofocus required>
                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="btn btn-primary fm-btn-primary w-100">{{ __('Verify and sign in') }}</button>
            </form>

            <div class="text-center mt-4">
                <a href="{{ route('login') }}" class="text-decoration-none">{{ __('Cancel and return to sign in') }}</a>
            </div>
        </div>
    </div>
@endsection
