@extends('layouts.app')
@section('title', __('Edit ticket'))
@section('page-title', __('Edit ticket'))
@section('page-subtitle'){{ $ticket->reference }} — {{ $ticket->subject }}@endsection
@section('content')<div class="card fm-card"><form method="POST" action="{{ route('tickets.update', $ticket) }}">@csrf @method('PUT')<div class="card-body p-4">@include('tickets._form')</div><div class="card-footer bg-white d-flex justify-content-end gap-2 p-3"><a href="{{ route('tickets.show', $ticket) }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a><button class="btn btn-primary" type="submit">{{ __('Save changes') }}</button></div></form></div>@endsection
