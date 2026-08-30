@extends('layouts.app')
@section('title', 'New asset')
@section('page-title', 'New asset')
@section('page-subtitle', 'Register a new managed asset')
@section('content')<div class="card fm-card"><form method="POST" action="{{ route('assets.store') }}">@csrf<div class="card-body p-4">@include('assets._form')</div><div class="card-footer bg-white d-flex justify-content-end gap-2 p-3"><a href="{{ route('assets.index') }}" class="btn btn-outline-secondary">Cancel</a><button class="btn btn-primary" type="submit">Create asset</button></div></form></div>@endsection
