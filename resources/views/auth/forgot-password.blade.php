@extends('layouts.guest')

@section('title', __('Reset password'))

@section('content')
    <div class="card fm-login-card">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <div class="fm-brand-mark mx-auto mb-3">FM</div>
                <h1 class="fm-brand h3 mb-2">{{ __('Reset password') }}</h1>
                <p class="text-secondary mb-0">{{ __('Enter your email address and we will send you a reset link.') }}</p>
            </div>

            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <div class="mb-4">
                    <label for="email" class="form-label fm-form-label">{{ __('Email address') }}</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" class="form-control fm-form-control @error('email') is-invalid @enderror" autocomplete="email" autofocus required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="btn btn-primary fm-btn-primary w-100">{{ __('Send reset link') }}</button>
            </form>

            <div class="text-center mt-4">
                <a href="{{ route('login') }}" class="text-decoration-none">{{ __('Back to sign in') }}</a>
            </div>
        </div>
    </div>
@endsection
