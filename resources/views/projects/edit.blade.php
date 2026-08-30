@extends('layouts.app')
@section('title', 'Edit project')
@section('page-title', 'Edit project')
@section('page-subtitle'){{ $project->code }} — {{ $project->name }}@endsection
@section('content')
    <div class="card fm-card">
        <form method="POST" action="{{ route('projects.update', $project) }}">
            @csrf
            @method('PUT')
            <div class="card-body p-4">@include('projects._form')</div>
            <div class="card-footer bg-white d-flex justify-content-end gap-2 p-3">
                <a href="{{ route('projects.show', $project) }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Save changes</button>
            </div>
        </form>
    </div>
@endsection
