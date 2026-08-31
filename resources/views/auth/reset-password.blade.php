@extends('layouts.guest')

@section('title', __('Choose a new password'))

@section('content')
    <div class="card fm-login-card">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <div class="fm-brand-mark mx-auto mb-3">FM</div>
                <h1 class="fm-brand h3 mb-2">{{ __('Choose a new password') }}</h1>
                <p class="text-secondary mb-0">{{ __('Use at least 8 characters and keep the password private.') }}</p>
            </div>

            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="mb-3">
                    <label for="email" class="form-label fm-form-label">{{ __('Email address') }}</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $email) }}" class="form-control fm-form-control @error('email') is-invalid @enderror" required autocomplete="email">
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label fm-form-label">{{ __('New password') }}</label>
                    <input id="password" name="password" type="password" class="form-control fm-form-control @error('password') is-invalid @enderror" required autocomplete="new-password">
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-4">
                    <label for="password_confirmation" class="form-label fm-form-label">{{ __('Confirm password') }}</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" class="form-control fm-form-control" required autocomplete="new-password">
                </div>

                <button type="submit" class="btn btn-primary fm-btn-primary w-100">{{ __('Reset password') }}</button>
            </form>
        </div>
    </div>
@endsection
