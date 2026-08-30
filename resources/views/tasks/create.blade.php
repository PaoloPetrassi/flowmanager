@extends('layouts.app')
@section('title', __('New task'))
@section('page-title', __('New task'))
@section('page-subtitle', __('Create and assign a project task'))
@section('content')
    <div class="card fm-card"><form method="POST" action="{{ route('tasks.store') }}">@csrf<div class="card-body p-4">@include('tasks._form')</div><div class="card-footer bg-white d-flex justify-content-end gap-2 p-3"><a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a><button class="btn btn-primary" type="submit">{{ __('Create task') }}</button></div></form></div>
@endsection
