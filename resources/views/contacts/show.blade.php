@extends('layouts.app')

@section('title', $contact->full_name)
@section('page-title', $contact->full_name)
@section('page-subtitle', 'Contact workspace and related activity')

@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <a href="{{ route('contacts.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Contacts
        </a>

        <div class="d-flex flex-wrap gap-2">
            @can('create', App\Models\Project::class)
                @if ($contact->company)
                    <a href="{{ route('projects.create', ['company' => $contact->company_id, 'contact' => $contact->id]) }}" class="btn btn-outline-primary">
                        <i class="bi bi-kanban me-1"></i>
                        Project
                    </a>
                @endif
            @endcan

            @can('create', App\Models\Ticket::class)
                <a href="{{ route('tickets.create', ['company' => $contact->company_id, 'contact' => $contact->id]) }}" class="btn btn-outline-primary">
                    <i class="bi bi-ticket-perforated me-1"></i>
                    Ticket
                </a>
            @endcan

            @can('update', $contact)
                <a href="{{ route('contacts.edit', $contact) }}" class="btn btn-primary">
                    <i class="bi bi-pencil me-1"></i>
                    Edit
                </a>
            @endcan

            @can('delete', $contact)
                <form method="POST" action="{{ route('contacts.destroy', $contact) }}" onsubmit="return confirm('Delete this contact?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="bi bi-trash me-1"></i>
                        Delete
                    </button>
                </form>
            @endcan
        </div>
    </div>

    <div class="row g-3 mb-4">
        @can('viewAny', App\Models\Project::class)
            <div class="col-6 col-md-3">
                <div class="card fm-card h-100">
                    <div class="card-body">
                        <div class="small text-secondary">Projects</div>
                        <div class="h4 mb-0 mt-1">{{ $relatedCounts['projects'] }}</div>
                    </div>
                </div>
            </div>
        @endcan

        @can('viewAny', App\Models\Ticket::class)
            <div class="col-6 col-md-3">
                <div class="card fm-card h-100">
                    <div class="card-body">
                        <div class="small text-secondary">Tickets</div>
                        <div class="h4 mb-0 mt-1">{{ $relatedCounts['tickets'] }}</div>
                    </div>
                </div>
            </div>
        @endcan
    </div>

    <div class="row g-4">
        <div class="col-12 col-xl-8">
            <div class="card fm-card mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start gap-3 mb-4">
                        <div class="fm-contact-avatar">
                            {{ strtoupper(substr($contact->first_name, 0, 1)) }}{{ strtoupper(substr($contact->last_name, 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <h2 class="h4 mb-1">{{ $contact->full_name }}</h2>
                            @if ($contact->job_title)
                                <div class="text-secondary">{{ $contact->job_title }}</div>
                            @endif
                            <div class="mt-2 d-flex flex-wrap gap-1">
                                @if ($contact->is_primary)
                                    <span class="badge text-bg-primary">Primary contact</span>
                                @endif
                                @if ($contact->department)
                                    <span class="badge text-bg-light border">{{ $contact->department }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row g-4">
                        <div class="col-12 col-md-6"><div class="small text-secondary">Email</div><div class="fw-semibold">@if ($contact->email)<a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a>@else—@endif</div></div>
                        <div class="col-12 col-md-6"><div class="small text-secondary">Department</div><div class="fw-semibold">{{ $contact->department ?: '—' }}</div></div>
                        <div class="col-12 col-md-6"><div class="small text-secondary">Phone</div><div class="fw-semibold">{{ $contact->phone ?: '—' }}</div></div>
                        <div class="col-12 col-md-6"><div class="small text-secondary">Mobile</div><div class="fw-semibold">{{ $contact->mobile ?: '—' }}</div></div>
                        @if ($contact->notes)
                            <div class="col-12"><div class="small text-secondary mb-1">Notes</div><div class="border rounded p-3 bg-light text-break">{!! nl2br(e($contact->notes)) !!}</div></div>
                        @endif
                    </div>
                </div>
            </div>

            @can('viewAny', App\Models\Project::class)
                <div class="card fm-card mb-4">
                    <div class="card-header fm-card-header">
                        <div><h2 class="fm-card-title">Projects</h2><p class="fm-card-subtitle">Projects where this person is the reference contact</p></div>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0 fm-table">
                            <thead><tr><th>Project</th><th>Company</th><th>Status</th><th>Manager</th><th>Tasks</th></tr></thead>
                            <tbody>
                                @forelse ($projects as $project)
                                    <tr>
                                        <td><a href="{{ route('projects.show', $project) }}" class="fw-semibold text-dark">{{ $project->name }}</a><div class="small text-secondary">{{ $project->code }}</div></td>
                                        <td>{{ $project->company->name }}</td>
                                        <td>{{ $project->status->label() }}</td>
                                        <td>{{ $project->manager?->name ?: '—' }}</td>
                                        <td>{{ $project->tasks_count }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center py-4 text-secondary">No projects use this contact as a reference.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endcan

            @can('viewAny', App\Models\Ticket::class)
                <div class="card fm-card">
                    <div class="card-header fm-card-header">
                        <div><h2 class="fm-card-title">Tickets</h2><p class="fm-card-subtitle">Support history associated with this contact</p></div>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0 fm-table">
                            <thead><tr><th>Ticket</th><th>Company</th><th>Status</th><th>Priority</th><th>Operator</th></tr></thead>
                            <tbody>
                                @forelse ($tickets as $ticket)
                                    <tr>
                                        <td><a href="{{ route('tickets.show', $ticket) }}" class="fw-semibold text-dark">{{ $ticket->subject }}</a><div class="small text-secondary">{{ $ticket->reference }}</div></td>
                                        <td>{{ $ticket->company?->name ?: '—' }}</td>
                                        <td>{{ $ticket->status->label() }}</td>
                                        <td>{{ $ticket->priority->label() }}</td>
                                        <td>{{ $ticket->assignee?->name ?: '—' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center py-4 text-secondary">No tickets associated with this contact.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endcan
        </div>

        <div class="col-12 col-xl-4">
            <div class="card fm-card mb-4">
                <div class="card-header fm-card-header"><h2 class="fm-card-title">Company</h2></div>
                <div class="card-body p-4">
                    @if ($contact->company)
                        <div class="d-flex align-items-center gap-3">
                            <div class="fm-small-avatar">{{ strtoupper(substr($contact->company->name, 0, 1)) }}</div>
                            <div class="min-w-0">
                                <a href="{{ route('companies.show', $contact->company) }}" class="fw-semibold text-dark">{{ $contact->company->name }}</a>
                                @if ($contact->company->industry)
                                    <div class="small text-secondary">{{ $contact->company->industry }}</div>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="text-secondary">This contact is not associated with a company.</div>
                    @endif
                </div>
            </div>

            <div class="card fm-card">
                <div class="card-body">
                    <h2 class="fm-card-title mb-3">Record information</h2>
                    <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-secondary">Created by</span><strong>{{ $contact->creator?->name ?: 'System' }}</strong></div>
                    <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-secondary">Created</span><strong>{{ $contact->created_at->format('d/m/Y H:i') }}</strong></div>
                    <div class="d-flex justify-content-between pt-2"><span class="text-secondary">Updated</span><strong>{{ $contact->updated_at->format('d/m/Y H:i') }}</strong></div>
                </div>
            </div>
        </div>
    </div>
@endsection
