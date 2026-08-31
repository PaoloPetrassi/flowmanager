@extends('layouts.app')

@section('title', __('Verify email'))
@section('page-title', __('Verify email'))
@section('page-subtitle', __('Confirm ownership of your email address'))

@section('content')
    <div class="card fm-card mx-auto" style="max-width: 720px;">
        <div class="card-body p-4 p-md-5 text-center">
            <div class="fm-security-hero-icon mx-auto mb-3"><i class="bi bi-envelope-check"></i></div>
            <h2 class="h4 mb-2">{{ __('Verify your email address') }}</h2>
            <p class="text-secondary mb-4">{{ __('We will send a signed verification link to :email.', ['email' => auth()->user()->email]) }}</p>
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="btn btn-primary"><i class="bi bi-send me-1"></i>{{ __('Send verification link') }}</button>
            </form>
        </div>
    </div>
@endsection
