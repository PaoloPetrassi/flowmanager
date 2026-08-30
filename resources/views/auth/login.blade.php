@extends('layouts.guest')

@section('title', __('Sign in'))

@section('content')

    <div class="card fm-login-card">
        <div class="card-body p-4 p-md-5">

            <div class="text-center mb-4">

                <div class="fm-brand-mark mx-auto mb-3">
                    FM
                </div>

                <h1 class="fm-brand h3 mb-2">
                    FlowManager
                </h1>

                <p class="text-secondary mb-0">
                    {{ __('Business Management Platform') }}
                </p>

            </div>

            @if (session('status'))
                <div
                    class="alert alert-success"
                    role="alert"
                >
                    {{ session('status') }}
                </div>
            @endif

            <form
                method="POST"
                action="{{ route('login.attempt') }}"
                novalidate
            >
                @csrf

                <div class="mb-3">

                    <label
                        for="email"
                        class="form-label fm-form-label"
                    >
                        {{ __('Email address') }}
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        class="form-control fm-form-control @error('email') is-invalid @enderror"
                        autocomplete="email"
                        autofocus
                        required
                    >

                    @error('email')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                </div>

                <div class="mb-3">

                    <label
                        for="password"
                        class="form-label fm-form-label"
                    >
                        {{ __('Password') }}
                    </label>

                    <div class="input-group">

                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control fm-form-control @error('password') is-invalid @enderror"
                            autocomplete="current-password"
                            required
                        >

                        <button
                            type="button"
                            class="btn btn-outline-secondary fm-password-toggle"
                            data-password-toggle
                            data-target="password"
                            data-label-show="{{ __('Show password') }}"
                            data-label-hide="{{ __('Hide password') }}"
                            aria-label="{{ __('Show password') }}"
                        >
                            <i class="bi bi-eye"></i>
                        </button>

                        @error('password')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                </div>

                <div class="form-check mb-4">

                    <input
                        type="checkbox"
                        id="remember"
                        name="remember"
                        value="1"
                        class="form-check-input"
                        {{ old('remember') ? 'checked' : '' }}
                    >

                    <label
                        for="remember"
                        class="form-check-label"
                    >
                        {{ __('Remember me') }}
                    </label>

                </div>

                <button
                    type="submit"
                    class="btn btn-primary fm-btn-primary w-100"
                >
                    {{ __('Sign in') }}
                </button>

            </form>

        </div>
    </div>

    <div class="text-center mt-4 fm-login-footer">
        {{ __('FlowManager Portfolio Project') }}
    </div>

@endsection