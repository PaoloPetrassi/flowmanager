@extends('layouts.guest')
@section('title', __('Page not found'))
@section('content')<div class="card fm-login-card"><div class="card-body p-5 text-center"><div class="display-4 fw-bold text-primary mb-2">404</div><h1 class="h4">{{ __('Page not found') }}</h1><p class="text-secondary">{{ __('The requested page does not exist or is no longer available.') }}</p><a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="btn btn-primary">{{ __('Return to FlowManager') }}</a></div></div>@endsection
