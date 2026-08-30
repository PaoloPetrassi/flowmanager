@extends('layouts.app')
@section('title', __('Edit project'))
@section('page-title', __('Edit project'))
@section('page-subtitle'){{ $project->code }} — {{ $project->name }}@endsection
@section('content')
    <div class="card fm-card">
        <form method="POST" action="{{ route('projects.update', $project) }}">
            @csrf
            @method('PUT')
            <div class="card-body p-4">@include('projects._form')</div>
            <div class="card-footer bg-white d-flex justify-content-end gap-2 p-3">
                <a href="{{ route('projects.show', $project) }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
                <button type="submit" class="btn btn-primary">{{ __('Save changes') }}</button>
            </div>
        </form>
    </div>
@endsection
