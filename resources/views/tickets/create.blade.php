@extends('layouts.app')
@section('title', 'New ticket')
@section('page-title', 'New ticket')
@section('page-subtitle', 'Open a new support request')
@section('content')<div class="card fm-card"><form method="POST" action="{{ route('tickets.store') }}">@csrf<div class="card-body p-4">@include('tickets._form')</div><div class="card-footer bg-white d-flex justify-content-end gap-2 p-3"><a href="{{ route('tickets.index') }}" class="btn btn-outline-secondary">Cancel</a><button class="btn btn-primary" type="submit">Create ticket</button></div></form></div>@endsection
