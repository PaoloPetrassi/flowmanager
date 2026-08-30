@extends('layouts.app')
@section('title', 'New task')
@section('page-title', 'New task')
@section('page-subtitle', 'Create and assign a project task')
@section('content')
    <div class="card fm-card"><form method="POST" action="{{ route('tasks.store') }}">@csrf<div class="card-body p-4">@include('tasks._form')</div><div class="card-footer bg-white d-flex justify-content-end gap-2 p-3"><a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary">Cancel</a><button class="btn btn-primary" type="submit">Create task</button></div></form></div>
@endsection
