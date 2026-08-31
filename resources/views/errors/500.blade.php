@extends('layouts.guest')
@section('title', __('Server error'))
@section('content')<div class="card fm-login-card"><div class="card-body p-5 text-center"><div class="display-4 fw-bold text-primary mb-2">500</div><h1 class="h4">{{ __('Something went wrong') }}</h1><p class="text-secondary">{{ __('The error has been logged. Please try again or contact an administrator.') }}</p><a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="btn btn-primary">{{ __('Return to FlowManager') }}</a></div></div>@endsection
