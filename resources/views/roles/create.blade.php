@extends('layouts.app')
@section('title', 'New role')
@section('page-title', 'New role')
@section('page-subtitle', 'Create a custom role and select its permissions')
@section('content')<div class="card fm-card"><form method="POST" action="{{ route('roles.store') }}">@csrf<div class="card-body p-4">@include('roles._form')</div><div class="card-footer bg-white d-flex justify-content-end gap-2 p-3"><a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">Cancel</a><button class="btn btn-primary" type="submit">Create role</button></div></form></div>@endsection
