@extends('layouts.app')
@section('title', 'Edit ticket')
@section('page-title', 'Edit ticket')
@section('page-subtitle'){{ $ticket->reference }} — {{ $ticket->subject }}@endsection
@section('content')<div class="card fm-card"><form method="POST" action="{{ route('tickets.update', $ticket) }}">@csrf @method('PUT')<div class="card-body p-4">@include('tickets._form')</div><div class="card-footer bg-white d-flex justify-content-end gap-2 p-3"><a href="{{ route('tickets.show', $ticket) }}" class="btn btn-outline-secondary">Cancel</a><button class="btn btn-primary" type="submit">Save changes</button></div></form></div>@endsection
