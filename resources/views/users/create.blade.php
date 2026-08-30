@extends('layouts.app')
@section('title', __('New user'))
@section('page-title', __('New user'))
@section('page-subtitle', __('Create an account and assign application roles'))
@section('content')<div class="card fm-card"><form method="POST" action="{{ route('users.store') }}">@csrf<div class="card-body p-4">@include('users._form')</div><div class="card-footer bg-white d-flex justify-content-end gap-2 p-3"><a href="{{ route('users.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a><button class="btn btn-primary" type="submit">{{ __('Create user') }}</button></div></form></div>@endsection
