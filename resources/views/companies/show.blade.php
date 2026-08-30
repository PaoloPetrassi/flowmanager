@extends('layouts.app')

@section('title', $company->name)
@section('page-title', $company->name)
@section('page-subtitle', __('Company workspace and related operational records'))

@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <a href="{{ route('companies.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            {{ __('Companies') }}
        </a>

        <div class="d-flex flex-wrap gap-2">
            @can('create', App\Models\Contact::class)
                <a href="{{ route('contacts.create', ['company' => $company->id]) }}" class="btn btn-outline-primary">
                    <i class="bi bi-person-plus me-1"></i>
                    {{ __('Contact') }}
                </a>
            @endcan

            @can('create', App\Models\Project::class)
                <a href="{{ route('projects.create', ['company' => $company->id]) }}" class="btn btn-outline-primary">
                    <i class="bi bi-kanban me-1"></i>
                    {{ __('Project') }}
                </a>
            @endcan

            @can('create', App\Models\Ticket::class)
                <a href="{{ route('tickets.create', ['company' => $company->id]) }}" class="btn btn-outline-primary">
                    <i class="bi bi-ticket-perforated me-1"></i>
                    {{ __('Ticket') }}
                </a>
            @endcan

            @can('update', $company)
                <a href="{{ route('companies.edit', $company) }}" class="btn btn-primary">
                    <i class="bi bi-pencil me-1"></i>
                    {{ __('Edit') }}
                </a>
            @endcan

            @can('delete', $company)
                <form method="POST" action="{{ route('companies.destroy', $company) }}" onsubmit="return confirm(@js(__('Delete this company?')));">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="bi bi-trash me-1"></i>
                        {{ __('Delete') }}
                    </button>
                </form>
            @endcan
        </div>
    </div>

    <div class="row g-3 mb-4">
        @can('viewAny', App\Models\Contact::class)
            <div class="col-6 col-lg-3">
                <a href="{{ route('contacts.index', ['company_id' => $company->id]) }}" class="card fm-card fm-related-stat text-reset h-100">
                    <div class="card-body">
                        <div class="small text-secondary">{{ __('Contacts') }}</div>
                        <div class="h4 mb-0 mt-1">{{ $relatedCounts['contacts'] }}</div>
                    </div>
                </a>
            </div>
        @endcan

        @can('viewAny', App\Models\Project::class)
            <div class="col-6 col-lg-3">
                <a href="{{ route('projects.index', ['company_id' => $company->id]) }}" class="card fm-card fm-related-stat text-reset h-100">
                    <div class="card-body">
                        <div class="small text-secondary">{{ __('Projects') }}</div>
                        <div class="h4 mb-0 mt-1">{{ $relatedCounts['projects'] }}</div>
                    </div>
                </a>
            </div>
        @endcan

        @can('viewAny', App\Models\Asset::class)
            <div class="col-6 col-lg-3">
                <a href="{{ route('assets.index', ['company_id' => $company->id]) }}" class="card fm-card fm-related-stat text-reset h-100">
                    <div class="card-body">
                        <div class="small text-secondary">{{ __('Assets') }}</div>
                        <div class="h4 mb-0 mt-1">{{ $relatedCounts['assets'] }}</div>
                    </div>
                </a>
            </div>
        @endcan

        @can('viewAny', App\Models\Ticket::class)
            <div class="col-6 col-lg-3">
                <a href="{{ route('tickets.index', ['company_id' => $company->id]) }}" class="card fm-card fm-related-stat text-reset h-100">
                    <div class="card-body">
                        <div class="small text-secondary">{{ __('Tickets') }}</div>
                        <div class="h4 mb-0 mt-1">{{ $relatedCounts['tickets'] }}</div>
                    </div>
                </a>
            </div>
        @endcan
    </div>

    <div class="row g-4">
        <div class="col-12 col-xl-8">
            <div class="card fm-card mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start gap-3 mb-4">
                        <div class="fm-company-avatar">{{ strtoupper(substr($company->name, 0, 1)) }}</div>
                        <div class="min-w-0">
                            <h2 class="h4 mb-1">{{ $company->name }}</h2>
                            @if ($company->legal_name)
                                <div class="text-secondary">{{ $company->legal_name }}</div>
                            @endif
                            <div class="mt-2 d-flex flex-wrap gap-1">
                                <span class="badge text-bg-light border">{{ $company->type->label() }}</span>
                                @switch($company->status->value)
                                    @case('active')
                                        <span class="badge text-bg-success">{{ $company->status->label() }}</span>
                                        @break
                                    @case('prospect')
                                        <span class="badge text-bg-primary">{{ $company->status->label() }}</span>
                                        @break
                                    @case('suspended')
                                        <span class="badge text-bg-warning">{{ $company->status->label() }}</span>
                                        @break
                                    @default
                                        <span class="badge text-bg-secondary">{{ $company->status->label() }}</span>
                                @endswitch
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row g-4">
                        <div class="col-12 col-md-6"><div class="small text-secondary">{{ __('Industry') }}</div><div class="fw-semibold">{{ $company->industry ?: '—' }}</div></div>
                        <div class="col-12 col-md-6"><div class="small text-secondary">{{ __('Employees') }}</div><div class="fw-semibold">{{ $company->employees !== null ? number_format($company->employees) : '—' }}</div></div>
                        <div class="col-12 col-md-6"><div class="small text-secondary">{{ __('VAT number') }}</div><div class="fw-semibold">{{ $company->vat_number ?: '—' }}</div></div>
                        <div class="col-12 col-md-6"><div class="small text-secondary">{{ __('Tax code') }}</div><div class="fw-semibold">{{ $company->tax_code ?: '—' }}</div></div>
                        <div class="col-12 col-md-6"><div class="small text-secondary">{{ __('Email') }}</div><div class="fw-semibold">@if ($company->email)<a href="mailto:{{ $company->email }}">{{ $company->email }}</a>@else—@endif</div></div>
                        <div class="col-12 col-md-6"><div class="small text-secondary">{{ __('Phone') }}</div><div class="fw-semibold">{{ $company->phone ?: '—' }}</div></div>
                        <div class="col-12"><div class="small text-secondary">{{ __('Address') }}</div><div class="fw-semibold">{{ collect([$company->address, $company->postal_code, $company->city, $company->province, $company->country_code])->filter()->implode(', ') ?: '—' }}</div></div>
                        @if ($company->notes)
                            <div class="col-12"><div class="small text-secondary mb-1">{{ __('Notes') }}</div><div class="border rounded p-3 bg-light text-break">{!! nl2br(e($company->notes)) !!}</div></div>
                        @endif
                    </div>
                </div>
            </div>

            @can('viewAny', App\Models\Project::class)
                <div class="card fm-card mb-4">
                    <div class="card-header fm-card-header">
                        <div><h2 class="fm-card-title">{{ __('Recent projects') }}</h2><p class="fm-card-subtitle">{{ __('Delivery linked to this company') }}</p></div>
                        <a href="{{ route('projects.index', ['company_id' => $company->id]) }}" class="btn btn-sm btn-outline-secondary">{{ __('View all') }}</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0 fm-table">
                            <thead><tr><th>{{ __('Project') }}</th><th>{{ __('Status') }}</th><th>{{ __('Manager') }}</th><th>{{ __('Tasks') }}</th><th>{{ __('Due') }}</th></tr></thead>
                            <tbody>
                                @forelse ($projects as $project)
                                    <tr>
                                        <td><a href="{{ route('projects.show', $project) }}" class="fw-semibold text-dark">{{ $project->name }}</a><div class="small text-secondary">{{ $project->code }}</div></td>
                                        <td>{{ $project->status->label() }}</td>
                                        <td>{{ $project->manager?->name ?: '—' }}</td>
                                        <td>{{ $project->tasks_count }}</td>
                                        <td>{{ $project->due_date?->format('d/m/Y') ?: '—' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center py-4 text-secondary">{{ __('No projects linked to this company.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endcan

            @can('viewAny', App\Models\Ticket::class)
                <div class="card fm-card">
                    <div class="card-header fm-card-header">
                        <div><h2 class="fm-card-title">{{ __('Recent tickets') }}</h2><p class="fm-card-subtitle">{{ __('Support activity for this company') }}</p></div>
                        <a href="{{ route('tickets.index', ['company_id' => $company->id]) }}" class="btn btn-sm btn-outline-secondary">{{ __('View all') }}</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0 fm-table">
                            <thead><tr><th>{{ __('Ticket') }}</th><th>{{ __('Status') }}</th><th>{{ __('Priority') }}</th><th>{{ __('Operator') }}</th></tr></thead>
                            <tbody>
                                @forelse ($tickets as $ticket)
                                    <tr>
                                        <td><a href="{{ route('tickets.show', $ticket) }}" class="fw-semibold text-dark">{{ $ticket->subject }}</a><div class="small text-secondary">{{ $ticket->reference }}</div></td>
                                        <td>{{ $ticket->status->label() }}</td>
                                        <td>{{ $ticket->priority->label() }}</td>
                                        <td>{{ $ticket->assignee?->name ?: '—' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center py-4 text-secondary">{{ __('No tickets linked to this company.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endcan
        </div>

        <div class="col-12 col-xl-4">
            @can('viewAny', App\Models\Contact::class)
                <div class="card fm-card mb-4">
                    <div class="card-header fm-card-header">
                        <div><h2 class="fm-card-title">{{ __('Contacts') }}</h2><p class="fm-card-subtitle">{{ __('People associated with this company') }}</p></div>
                        @can('create', App\Models\Contact::class)
                            <a href="{{ route('contacts.create', ['company' => $company->id]) }}" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg"></i></a>
                        @endcan
                    </div>
                    <div class="list-group list-group-flush">
                        @forelse ($contacts as $contact)
                            <a href="{{ route('contacts.show', $contact) }}" class="list-group-item list-group-item-action fm-related-list-item">
                                <div class="min-w-0">
                                    <div class="fw-semibold">{{ $contact->full_name }} @if ($contact->is_primary)<span class="badge text-bg-primary ms-1">{{ __('Primary') }}</span>@endif</div>
                                    <div class="small text-secondary">{{ $contact->job_title ?: ($contact->email ?: __('No role specified')) }}</div>
                                </div>
                                <i class="bi bi-chevron-right text-secondary"></i>
                            </a>
                        @empty
                            <div class="p-4 text-center text-secondary">{{ __('No contacts linked to this company.') }}</div>
                        @endforelse
                    </div>
                </div>
            @endcan

            @can('viewAny', App\Models\Asset::class)
                <div class="card fm-card mb-4">
                    <div class="card-header fm-card-header">
                        <div><h2 class="fm-card-title">{{ __('Assets') }}</h2><p class="fm-card-subtitle">{{ __('Equipment associated with this company') }}</p></div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('assets.index', ['company_id' => $company->id]) }}" class="btn btn-sm btn-outline-secondary">{{ __('All') }}</a>
                            @can('create', App\Models\Asset::class)
                                <a href="{{ route('assets.create', ['company' => $company->id]) }}" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg"></i></a>
                            @endcan
                        </div>
                    </div>
                    <div class="list-group list-group-flush">
                        @forelse ($assets as $asset)
                            <a href="{{ route('assets.show', $asset) }}" class="list-group-item list-group-item-action fm-related-list-item">
                                <div class="min-w-0"><div class="fw-semibold">{{ $asset->name }}</div><div class="small text-secondary">{{ $asset->asset_tag }} · {{ $asset->status->label() }}</div></div>
                                <i class="bi bi-chevron-right text-secondary"></i>
                            </a>
                        @empty
                            <div class="p-4 text-center text-secondary">{{ __('No assets linked to this company.') }}</div>
                        @endforelse
                    </div>
                </div>
            @endcan

            <div class="card fm-card">
                <div class="card-body">
                    <h2 class="fm-card-title mb-3">{{ __('Record information') }}</h2>
                    <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-secondary">{{ __('Created by') }}</span><strong>{{ $company->creator?->name ?: __('System') }}</strong></div>
                    <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-secondary">{{ __('Created') }}</span><strong>{{ $company->created_at->format('d/m/Y H:i') }}</strong></div>
                    <div class="d-flex justify-content-between pt-2"><span class="text-secondary">{{ __('Updated') }}</span><strong>{{ $company->updated_at->format('d/m/Y H:i') }}</strong></div>
                </div>
            </div>
        </div>
    </div>

    @include('partials.collaboration-panel', ['collaborationTarget' => $company])
@endsection
