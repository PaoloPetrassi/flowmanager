@extends('layouts.app')

@section('title', __('Contacts'))

@section('page-title', __('Contacts'))

@section('page-subtitle', __('Manage people and company relationships'))

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <span class="text-secondary">
                {{ trans_choice('ui.counts.contacts', $contacts->total(), ['count' => $contacts->total()]) }}
            </span>
        </div>

        @can('create', App\Models\Contact::class)

            <a
                href="{{ route('contacts.create') }}"
                class="btn btn-primary"
            >
                <i class="bi bi-plus-lg me-1"></i>
                {{ __('New contact') }}
            </a>

        @endcan

    </div>

    <div class="card fm-card mb-4">

        <div class="card-body p-4">

            <form
                method="GET"
                action="{{ route('contacts.index') }}"
            >

                <div class="row g-3">

                    <div class="col-12 col-xl-4">

                        <label
                            for="search"
                            class="form-label fw-semibold"
                        >
                            {{ __('Search') }}
                        </label>

                        <div class="input-group">

                            <span class="input-group-text">
                                <i class="bi bi-search"></i>
                            </span>

                            <input
                                type="search"
                                id="search"
                                name="search"
                                value="{{ $filters['search'] }}"
                                class="form-control"
                                placeholder="{{ __('Name, company, email, role...') }}"
                            >

                        </div>

                    </div>

                    <div class="col-12 col-md-6 col-xl-3">

                        <label
                            for="company_id"
                            class="form-label fw-semibold"
                        >
                            {{ __('Company') }}
                        </label>

                        <select
                            id="company_id"
                            name="company_id"
                            class="form-select"
                        >

                            <option value="">
                                {{ __('All companies') }}
                            </option>

                            @foreach ($companies as $company)

                                <option
                                    value="{{ $company->id }}"
                                    @selected(
                                        $filters['company_id'] === (string) $company->id
                                    )
                                >
                                    {{ $company->name }}
                                </option>

                            @endforeach

                        </select>

                    </div>

                    <div class="col-12 col-md-6 col-xl-2">

                        <label
                            for="primary"
                            class="form-label fw-semibold"
                        >
                            {{ __('Contact type') }}
                        </label>

                        <select
                            id="primary"
                            name="primary"
                            class="form-select"
                        >

                            <option value="">
                                {{ __('All contacts') }}
                            </option>

                            <option
                                value="1"
                                @selected($filters['primary'] === '1')
                            >
                                {{ __('Primary only') }}
                            </option>

                            <option
                                value="0"
                                @selected($filters['primary'] === '0')
                            >
                                {{ __('Non-primary') }}
                            </option>

                        </select>

                    </div>

                    <div class="col-12 col-md-6 col-xl-2">

                        <label
                            for="sort"
                            class="form-label fw-semibold"
                        >
                            {{ __('Sort by') }}
                        </label>

                        <select
                            id="sort"
                            name="sort"
                            class="form-select"
                        >

                            <option
                                value="last_name"
                                @selected($filters['sort'] === 'last_name')
                            >
                                {{ __('Last name') }}
                            </option>

                            <option
                                value="first_name"
                                @selected($filters['sort'] === 'first_name')
                            >
                                {{ __('First name') }}
                            </option>

                            <option
                                value="job_title"
                                @selected($filters['sort'] === 'job_title')
                            >
                                {{ __('Job title') }}
                            </option>

                            <option
                                value="created_at"
                                @selected($filters['sort'] === 'created_at')
                            >
                                {{ __('Created') }}
                            </option>

                        </select>

                    </div>

                    <div class="col-12 col-md-6 col-xl-1">

                        <label
                            for="direction"
                            class="form-label fw-semibold"
                        >
                            {{ __('Order') }}
                        </label>

                        <select
                            id="direction"
                            name="direction"
                            class="form-select"
                        >

                            <option
                                value="asc"
                                @selected($filters['direction'] === 'asc')
                            >
                                {{ __('A-Z') }}
                            </option>

                            <option
                                value="desc"
                                @selected($filters['direction'] === 'desc')
                            >
                                {{ __('Z-A') }}
                            </option>

                        </select>

                    </div>

                </div>

                <div class="d-flex justify-content-end gap-2 mt-3">

                    <a
                        href="{{ route('contacts.index') }}"
                        class="btn btn-outline-secondary"
                    >
                        {{ __('Reset') }}
                    </a>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        {{ __('Apply filters') }}
                    </button>

                </div>

            </form>

        </div>

    </div>

    @include('partials.saved-filters', ['filterResource' => 'contacts', 'filterRoute' => 'contacts.index'])
    <div class="d-flex justify-content-end mb-3">
        @include('partials.per-page', ['resourceName' => 'contacts'])
    </div>

    <div class="card fm-card">

        <div class="table-responsive">

            <table class="table align-middle mb-0 fm-table">

                <thead>
                    <tr>
                        <th>{{ __('Contact') }}</th>
                        <th>{{ __('Company') }}</th>
                        <th>{{ __('Role') }}</th>
                        <th>{{ __('Email') }}</th>
                        <th>{{ __('Phone') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th class="text-end">{{ __('Actions') }}</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse ($contacts as $contact)

                        <tr>

                            <td>

                                <a
                                    href="{{ route('contacts.show', $contact) }}"
                                    class="fw-semibold text-dark"
                                >
                                    {{ $contact->full_name }}
                                </a>

                                @if ($contact->department)

                                    <div class="small text-secondary">
                                        {{ $contact->department }}
                                    </div>

                                @endif

                            </td>

                            <td>

                                @if ($contact->company)

                                    <a
                                        href="{{ route('companies.show', $contact->company) }}"
                                        class="text-decoration-none"
                                    >
                                        {{ $contact->company->name }}
                                    </a>

                                @else
                                    —
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

                                @if ($contact->mobile)

                                    <div>
                                        {{ $contact->mobile }}
                                    </div>

                                    @if ($contact->phone)
                                        <div class="small text-secondary">
                                            {{ $contact->phone }}
                                        </div>
                                    @endif

                                @elseif ($contact->phone)
                                    {{ $contact->phone }}
                                @else
                                    —
                                @endif

                            </td>

                            <td>

                                @if ($contact->is_primary)

                                    <span class="badge text-bg-primary">
                                        {{ __('Primary') }}
                                    </span>

                                @else

                                    <span class="badge text-bg-light border text-secondary">
                                        {{ __('Standard') }}
                                    </span>

                                @endif

                            </td>

                            <td class="text-end">

                                <div class="btn-group">

                                    <a
                                        href="{{ route('contacts.show', $contact) }}"
                                        class="btn btn-sm btn-outline-secondary"
                                        title="{{ __('View') }}"
                                    >
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    @can('update', $contact)

                                        <a
                                            href="{{ route('contacts.edit', $contact) }}"
                                            class="btn btn-sm btn-outline-secondary"
                                            title="{{ __('Edit') }}"
                                        >
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                    @endcan

                                    @can('delete', $contact)

                                        <form
                                            method="POST"
                                            action="{{ route('contacts.destroy', $contact) }}"
                                            onsubmit="return confirm(@js(__('Delete this contact?')));"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-outline-danger rounded-start-0"
                                                title="{{ __('Delete') }}"
                                            >
                                                <i class="bi bi-trash"></i>
                                            </button>

                                        </form>

                                    @endcan

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="7"
                                class="text-center py-5"
                            >

                                <i class="bi bi-person-vcard fs-1 text-secondary"></i>

                                <div class="fw-semibold mt-3">
                                    {{ __('No contacts found') }}
                                </div>

                                <div class="text-secondary">
                                    {{ __('Try changing the search filters or create a new contact.') }}
                                </div>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        @if ($contacts->hasPages())

            <div class="card-footer bg-white p-3">

                {{ $contacts->links() }}

            </div>

        @endif

    </div>

@endsection
