@extends('layouts.app')
@section('title', __('New asset'))
@section('page-title', __('New asset'))
@section('page-subtitle', __('Register a new managed asset'))
@section('content')<div class="card fm-card"><form method="POST" action="{{ route('assets.store') }}">@csrf<div class="card-body p-4">@include('assets._form')</div><div class="card-footer bg-white d-flex justify-content-end gap-2 p-3"><a href="{{ route('assets.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a><button class="btn btn-primary" type="submit">{{ __('Create asset') }}</button></div></form></div>@endsection
