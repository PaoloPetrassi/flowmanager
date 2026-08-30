@extends('layouts.app')

@section('title', $ticket->reference)
@section('page-title', $ticket->subject)
@section('page-subtitle'){{ $ticket->reference }} · {{ $ticket->category->label() }}@endsection

@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <a href="{{ route('tickets.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Tickets
        </a>

        <div class="d-flex flex-wrap gap-2">
            @can('update', $ticket)
                @if (in_array($ticket->status->value, ['resolved', 'closed'], true))
                    <form method="POST" action="{{ route('tickets.reopen', $ticket) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>
                            Reopen
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('tickets.resolve', $ticket) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-outline-success">
                            <i class="bi bi-check-lg me-1"></i>
                            Mark resolved
                        </button>
                    </form>
                @endif

                <a href="{{ route('tickets.edit', $ticket) }}" class="btn btn-primary">
                    <i class="bi bi-pencil me-1"></i>
                    Edit ticket
                </a>
            @endcan
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-xl-8">
            <div class="card fm-card mb-4">
                <div class="card-header fm-card-header">
                    <div><h2 class="fm-card-title">Ticket details</h2><p class="fm-card-subtitle">Request context and current ownership</p></div>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-6 col-md-3"><div class="small text-secondary">Status</div><div class="fw-semibold">{{ $ticket->status->label() }}</div></div>
                        <div class="col-6 col-md-3"><div class="small text-secondary">Priority</div><div class="fw-semibold">{{ $ticket->priority->label() }}</div></div>
                        <div class="col-6 col-md-3"><div class="small text-secondary">Category</div><div class="fw-semibold">{{ $ticket->category->label() }}</div></div>
                        <div class="col-6 col-md-3"><div class="small text-secondary">Resolved</div><div class="fw-semibold">{{ $ticket->resolved_at?->format('d/m/Y H:i') ?: '—' }}</div></div>
                        <div class="col-12 col-md-6"><div class="small text-secondary">Company</div>@if ($ticket->company)<a class="fw-semibold" href="{{ route('companies.show', $ticket->company) }}">{{ $ticket->company->name }}</a>@else<div class="fw-semibold">—</div>@endif</div>
                        <div class="col-12 col-md-6"><div class="small text-secondary">Contact</div>@if ($ticket->contact)<a class="fw-semibold" href="{{ route('contacts.show', $ticket->contact) }}">{{ $ticket->contact->full_name }}</a>@else<div class="fw-semibold">—</div>@endif</div>
                        <div class="col-12 col-md-6"><div class="small text-secondary">Assigned operator</div><div class="fw-semibold">{{ $ticket->assignee?->name ?: 'Unassigned' }}</div></div>
                        <div class="col-12"><div class="small text-secondary mb-1">Description</div><div class="border rounded p-3">{{ $ticket->description }}</div></div>
                        @if ($ticket->resolution)
                            <div class="col-12"><div class="small text-secondary mb-1">Resolution</div><div class="border rounded p-3 bg-light">{{ $ticket->resolution }}</div></div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="card fm-card">
                <div class="card-body">
                    <h2 class="fm-card-title mb-3">Record information</h2>
                    <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-secondary">Created by</span><strong>{{ $ticket->creator?->name ?: 'System' }}</strong></div>
                    <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-secondary">Created</span><strong>{{ $ticket->created_at->format('d/m/Y H:i') }}</strong></div>
                    <div class="d-flex justify-content-between pt-2"><span class="text-secondary">Updated</span><strong>{{ $ticket->updated_at->format('d/m/Y H:i') }}</strong></div>
                </div>
            </div>
        </div>
    </div>
@endsection
