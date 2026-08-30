@extends('layouts.app')
@section('title', __('New ticket'))
@section('page-title', __('New ticket'))
@section('page-subtitle', __('Open a new support request'))
@section('content')<div class="card fm-card"><form method="POST" action="{{ route('tickets.store') }}">@csrf<div class="card-body p-4">@include('tickets._form')</div><div class="card-footer bg-white d-flex justify-content-end gap-2 p-3"><a href="{{ route('tickets.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a><button class="btn btn-primary" type="submit">{{ __('Create ticket') }}</button></div></form></div>@endsection
