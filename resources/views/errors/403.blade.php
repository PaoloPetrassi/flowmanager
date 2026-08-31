@extends('layouts.guest')
@section('title', __('Access denied'))
@section('content')<div class="card fm-login-card"><div class="card-body p-5 text-center"><div class="display-4 fw-bold text-primary mb-2">403</div><h1 class="h4">{{ __('Access denied') }}</h1><p class="text-secondary">{{ __('You do not have permission to access this resource.') }}</p><a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="btn btn-primary">{{ __('Return to FlowManager') }}</a></div></div>@endsection
