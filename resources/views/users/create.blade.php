@extends('layouts.app')
@section('title', 'New user')
@section('page-title', 'New user')
@section('page-subtitle', 'Create an account and assign application roles')
@section('content')<div class="card fm-card"><form method="POST" action="{{ route('users.store') }}">@csrf<div class="card-body p-4">@include('users._form')</div><div class="card-footer bg-white d-flex justify-content-end gap-2 p-3"><a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Cancel</a><button class="btn btn-primary" type="submit">Create user</button></div></form></div>@endsection
