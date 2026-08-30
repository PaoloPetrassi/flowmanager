@extends('layouts.app')
@section('title', __('Edit task'))
@section('page-title', __('Edit task'))
@section('page-subtitle'){{ $task->title }}@endsection
@section('content')
    <div class="card fm-card"><form method="POST" action="{{ route('tasks.update', $task) }}">@csrf @method('PUT')<div class="card-body p-4">@include('tasks._form')</div><div class="card-footer bg-white d-flex justify-content-end gap-2 p-3"><a href="{{ route('tasks.show', $task) }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a><button class="btn btn-primary" type="submit">{{ __('Save changes') }}</button></div></form></div>
@endsection
