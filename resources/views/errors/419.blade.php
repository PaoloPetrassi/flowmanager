@extends('layouts.guest')
@section('title', __('Session expired'))
@section('content')<div class="card fm-login-card"><div class="card-body p-5 text-center"><div class="display-4 fw-bold text-primary mb-2">419</div><h1 class="h4">{{ __('Session expired') }}</h1><p class="text-secondary">{{ __('Your session expired. Refresh the page or sign in again.') }}</p><a href="{{ route('login') }}" class="btn btn-primary">{{ __('Sign in') }}</a></div></div>@endsection
