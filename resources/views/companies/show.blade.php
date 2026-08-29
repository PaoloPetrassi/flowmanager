@extends('layouts.app')

@section('title', $company->name)

@section('page-title', $company->name)

@section('page-subtitle')
    Company details
@endsection

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">

        <a
            href="{{ route('companies.index') }}"
            class="btn btn-outline-secondary"
        >
            <i class="bi bi-arrow-left me-1"></i>
            Companies
        </a>

        <div class="d-flex gap-2">

            @can('update', $company)

                <a
                    href="{{ route('companies.edit', $company) }}"
                    class="btn btn-primary"
                >
                    <i class="bi bi-pencil me-1"></i>
                    Edit
                </a>

            @endcan

            @can('delete', $company)

                <form
                    method="POST"
                    action="{{ route('companies.destroy', $company) }}"
                    onsubmit="return confirm('Delete this company?');"
                >
                    @csrf
                    @method('DELETE')

                    <button
                        type="submit"
                        class="btn btn-outline-danger"
                    >
                        <i class="bi bi-trash me-1"></i>
                        Delete
                    </button>

                </form>

            @endcan

        </div>

    </div>

    <div class="row g-4">

        <div class="col-12 col-xl-8">

            <div class="card fm-card">

                <div class="card-body p-4">

                    <div class="d-flex align-items-start gap-3 mb-4">

                        <div class="fm-company-avatar">
                            {{ strtoupper(substr($company->name, 0, 1)) }}
                        </div>

                        <div>

                            <h2 class="h4 mb-1">
                                {{ $company->name }}
                            </h2>

                            @if ($company->legal_name)

                                <div class="text-secondary">
                                    {{ $company->legal_name }}
                                </div>

                            @endif

                            <div class="mt-2">

                                <span class="badge text-bg-light border me-1">
                                    {{ $company->type->label() }}
                                </span>

                                @switch($company->status->value)

                                    @case('active')
                                        <span class="badge text-bg-success">
                                            {{ $company->status->label() }}
                                        </span>
                                        @break

                                    @case('prospect')
                                        <span class="badge text-bg-primary">
                                            {{ $company->status->label() }}
                                        </span>
                                        @break

                                    @case('suspended')
                                        <span class="badge text-bg-warning">
                                            {{ $company->status->label() }}
                                        </span>
                                        @break

                                    @default
                                        <span class="badge text-bg-secondary">
                                            {{ $company->status->label() }}
                                        </span>

                                @endswitch

                            </div>

                        </div>

                    </div>

                    <hr>

                    <div class="row g-4">

                        <div class="col-12 col-md-6">

                            <div class="text-secondary small">
                                Industry
                            </div>

                            <div class="fw-semibold">
                                {{ $company->industry ?: '—' }}
                            </div>

                        </div>

                        <div class="col-12 col-md-6">

                            <div class="text-secondary small">
                                Employees
                            </div>

                            <div class="fw-semibold">
                                {{ $company->employees !== null
                                    ? number_format($company->employees)
                                    : '—'
                                }}
                            </div>

                        </div>

                        <div class="col-12 col-md-6">

                            <div class="text-secondary small">
                                VAT number
                            </div>

                            <div class="fw-semibold">
                                {{ $company->vat_number ?: '—' }}
                            </div>

                        </div>

                        <div class="col-12 col-md-6">

                            <div class="text-secondary small">
                                Tax code
                            </div>

                            <div class="fw-semibold">
                                {{ $company->tax_code ?: '—' }}
                            </div>

                        </div>

                    </div>

                </div>

            </div>

            @can('viewAny', App\Models\Contact::class)

                <div class="card fm-card mt-4">

                    <div class="card-header fm-card-header">

                        <div>
                            <h2 class="fm-card-title">
                                Contacts
                            </h2>

                            <p class="fm-card-subtitle">
                                People associated with this company
                            </p>
                        </div>

                        @can('create', App\Models\Contact::class)

                            <a
                                href="{{ route('contacts.create', ['company' => $company->id]) }}"
                                class="btn btn-sm btn-primary"
                            >
                                <i class="bi bi-plus-lg me-1"></i>
                                New contact
                            </a>

                        @endcan

                    </div>

                    @if ($contacts->isNotEmpty())

                        <div class="table-responsive">

                            <table class="table align-middle mb-0 fm-table">

                                <thead>
                                    <tr>
                                        <th>Contact</th>
                                        <th>Role</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    @foreach ($contacts as $contact)

                                        <tr>

                                            <td>

                                                <a
                                                    href="{{ route('contacts.show', $contact) }}"
                                                    class="fw-semibold text-dark"
                                                >
                                                    {{ $contact->full_name }}
                                                </a>

                                                @if ($contact->is_primary)

                                                    <span class="badge text-bg-primary ms-1">
                                                        Primary
                                                    </span>

                                                @endif

                                            </td>

                                            <td>
                                                {{ $contact->job_title ?: '—' }}
                                            </td>

                                            <td>

                                                @if ($contact->email)

                                                    <a href="mailto:{{ $contact->email }}">
                                                        {{ $contact->email }}
                                                    </a>

                                                @else
                                                    —
                                                @endif

                                            </td>

                                            <td>
                                                {{ $contact->mobile ?: ($contact->phone ?: '—') }}
                                            </td>

                                            <td class="text-end">

                                                <a
                                                    href="{{ route('contacts.show', $contact) }}"
                                                    class="btn btn-sm btn-outline-secondary"
                                                    title="View"
                                                >
                                                    <i class="bi bi-eye"></i>
                                                </a>

                                            </td>

                                        </tr>

                                    @endforeach

                                </tbody>

                            </table>

                        </div>

                        <div class="card-footer bg-white p-3 text-end">

                            <a
                                href="{{ route('contacts.index', ['company_id' => $company->id]) }}"
                                class="btn btn-sm btn-outline-secondary"
                            >
                                View all company contacts ({{ $contactsCount }})
                            </a>

                        </div>

                    @else

                        <div class="card-body p-4 text-secondary">
                            No contacts are associated with this company yet.
                        </div>

                    @endif

                </div>

            @endcan

            @if ($company->notes)

                <div class="card fm-card mt-4">

                    <div class="card-header fm-card-header">

                        <h2 class="fm-card-title">
                            Notes
                        </h2>

                    </div>

                    <div class="card-body p-4">

                        <div class="text-break">
                            {!! nl2br(e($company->notes)) !!}
                        </div>

                    </div>

                </div>

            @endif

        </div>

        <div class="col-12 col-xl-4">

            <div class="card fm-card mb-4">

                <div class="card-header fm-card-header">
                    <h2 class="fm-card-title">
                        Contact
                    </h2>
                </div>

                <div class="card-body p-4">

                    <div class="mb-3">

                        <div class="text-secondary small">
                            Email
                        </div>

                        @if ($company->email)

                            <a href="mailto:{{ $company->email }}">
                                {{ $company->email }}
                            </a>

                        @else
                            —
                        @endif

                    </div>

                    <div class="mb-3">

                        <div class="text-secondary small">
                            Phone
                        </div>

                        <div>
                            {{ $company->phone ?: '—' }}
                        </div>

                    </div>

                    <div>

                        <div class="text-secondary small">
                            Website
                        </div>

                        @if ($company->website)

                            <a
                                href="{{ $company->website }}"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                {{ $company->website }}
                            </a>

                        @else
                            —
                        @endif

                    </div>

                </div>

            </div>

            <div class="card fm-card mb-4">

                <div class="card-header fm-card-header">
                    <h2 class="fm-card-title">
                        Address
                    </h2>
                </div>

                <div class="card-body p-4">

                    <div>
                        {{ $company->address ?: '—' }}
                    </div>

                    @if ($company->city || $company->postal_code)

                        <div>
                            {{ $company->postal_code }}
                            {{ $company->city }}
                        </div>

                    @endif

                    @if ($company->province)

                        <div>
                            {{ $company->province }}
                        </div>

                    @endif

                    @if ($company->country_code)

                        <div class="text-secondary">
                            {{ $company->country_code }}
                        </div>

                    @endif

                </div>

            </div>

            <div class="card fm-card">

                <div class="card-header fm-card-header">
                    <h2 class="fm-card-title">
                        Record information
                    </h2>
                </div>

                <div class="card-body p-4">

                    <div class="mb-3">

                        <div class="text-secondary small">
                            Created by
                        </div>

                        <div>
                            {{ $company->creator?->name ?: 'System' }}
                        </div>

                    </div>

                    <div class="mb-3">

                        <div class="text-secondary small">
                            Created
                        </div>

                        <div>
                            {{ $company->created_at->format('d M Y, H:i') }}
                        </div>

                    </div>

                    <div>

                        <div class="text-secondary small">
                            Last updated
                        </div>

                        <div>
                            {{ $company->updated_at->format('d M Y, H:i') }}
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

@endsection