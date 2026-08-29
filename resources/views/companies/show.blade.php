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