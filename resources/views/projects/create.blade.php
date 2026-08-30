@extends('layouts.app')
@section('title', __('New project'))
@section('page-title', __('New project'))
@section('page-subtitle', __('Create a new operational project'))
@section('content')
    <div class="card fm-card">
        <form method="POST" action="{{ route('projects.store') }}">
            @csrf
            <div class="card-body p-4">@include('projects._form')</div>
            <div class="card-footer bg-white d-flex justify-content-end gap-2 p-3">
                <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
                <button type="submit" class="btn btn-primary">{{ __('Create project') }}</button>
            </div>
        </form>
    </div>
@endsection
