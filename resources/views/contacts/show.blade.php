@extends('layouts.app')

@section('title', $contact->full_name)

@section('page-title', $contact->full_name)

@section('page-subtitle')
    Contact details
@endsection

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">

        <a
            href="{{ route('contacts.index') }}"
            class="btn btn-outline-secondary"
        >
            <i class="bi bi-arrow-left me-1"></i>
            Contacts
        </a>

        <div class="d-flex gap-2">

            @can('update', $contact)

                <a
                    href="{{ route('contacts.edit', $contact) }}"
                    class="btn btn-primary"
                >
                    <i class="bi bi-pencil me-1"></i>
                    Edit
                </a>

            @endcan

            @can('delete', $contact)

                <form
                    method="POST"
                    action="{{ route('contacts.destroy', $contact) }}"
                    onsubmit="return confirm('Delete this contact?');"
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

                        <div class="fm-contact-avatar">
                            {{ strtoupper(substr($contact->first_name, 0, 1)) }}{{ strtoupper(substr($contact->last_name, 0, 1)) }}
                        </div>

                        <div>

                            <h2 class="h4 mb-1">
                                {{ $contact->full_name }}
                            </h2>

                            @if ($contact->job_title)

                                <div class="text-secondary">
                                    {{ $contact->job_title }}
                                </div>

                            @endif

                            <div class="mt-2">

                                @if ($contact->is_primary)

                                    <span class="badge text-bg-primary">
                                        Primary contact
                                    </span>

                                @endif

                                @if ($contact->department)

                                    <span class="badge text-bg-light border">
                                        {{ $contact->department }}
                                    </span>

                                @endif

                            </div>

                        </div>

                    </div>

                    <hr>

                    <div class="row g-4">

                        <div class="col-12 col-md-6">

                            <div class="text-secondary small">
                                Email
                            </div>

                            <div class="fw-semibold">

                                @if ($contact->email)

                                    <a href="mailto:{{ $contact->email }}">
                                        {{ $contact->email }}
                                    </a>

                                @else
                                    —
                                @endif

                            </div>

                        </div>

                        <div class="col-12 col-md-6">

                            <div class="text-secondary small">
                                Department
                            </div>

                            <div class="fw-semibold">
                                {{ $contact->department ?: '—' }}
                            </div>

                        </div>

                        <div class="col-12 col-md-6">

                            <div class="text-secondary small">
                                Phone
                            </div>

                            <div class="fw-semibold">
                                {{ $contact->phone ?: '—' }}
                            </div>

                        </div>

                        <div class="col-12 col-md-6">

                            <div class="text-secondary small">
                                Mobile
                            </div>

                            <div class="fw-semibold">
                                {{ $contact->mobile ?: '—' }}
                            </div>

                        </div>

                    </div>

                </div>

            </div>

            @if ($contact->notes)

                <div class="card fm-card mt-4">

                    <div class="card-header fm-card-header">
                        <h2 class="fm-card-title">
                            Notes
                        </h2>
                    </div>

                    <div class="card-body p-4">

                        <div class="text-break">
                            {!! nl2br(e($contact->notes)) !!}
                        </div>

                    </div>

                </div>

            @endif

        </div>

        <div class="col-12 col-xl-4">

            <div class="card fm-card mb-4">

                <div class="card-header fm-card-header">
                    <h2 class="fm-card-title">
                        Company
                    </h2>
                </div>

                <div class="card-body p-4">

                    @if ($contact->company)

                        <div class="d-flex align-items-center gap-3">

                            <div class="fm-small-avatar">
                                {{ strtoupper(substr($contact->company->name, 0, 1)) }}
                            </div>

                            <div>

                                <a
                                    href="{{ route('companies.show', $contact->company) }}"
                                    class="fw-semibold text-dark"
                                >
                                    {{ $contact->company->name }}
                                </a>

                                @if ($contact->company->industry)

                                    <div class="small text-secondary">
                                        {{ $contact->company->industry }}
                                    </div>

                                @endif

                            </div>

                        </div>

                    @else

                        <div class="text-secondary">
                            This contact is not associated with a company.
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
                            {{ $contact->creator?->name ?: 'System' }}
                        </div>

                    </div>

                    <div class="mb-3">

                        <div class="text-secondary small">
                            Created
                        </div>

                        <div>
                            {{ $contact->created_at->format('d M Y, H:i') }}
                        </div>

                    </div>

                    <div>

                        <div class="text-secondary small">
                            Last updated
                        </div>

                        <div>
                            {{ $contact->updated_at->format('d M Y, H:i') }}
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

@endsection
