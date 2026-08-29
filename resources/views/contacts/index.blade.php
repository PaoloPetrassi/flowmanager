@extends('layouts.app')

@section('title', 'Contacts')

@section('page-title', 'Contacts')

@section('page-subtitle')
    Manage people and company relationships
@endsection

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <span class="text-secondary">
                {{ $contacts->total() }}
                {{ Str::plural('contact', $contacts->total()) }}
            </span>
        </div>

        @can('create', App\Models\Contact::class)

            <a
                href="{{ route('contacts.create') }}"
                class="btn btn-primary"
            >
                <i class="bi bi-plus-lg me-1"></i>
                New contact
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
                            Search
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
                                placeholder="Name, company, email, role..."
                            >

                        </div>

                    </div>

                    <div class="col-12 col-md-6 col-xl-3">

                        <label
                            for="company_id"
                            class="form-label fw-semibold"
                        >
                            Company
                        </label>

                        <select
                            id="company_id"
                            name="company_id"
                            class="form-select"
                        >

                            <option value="">
                                All companies
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
                            Contact type
                        </label>

                        <select
                            id="primary"
                            name="primary"
                            class="form-select"
                        >

                            <option value="">
                                All contacts
                            </option>

                            <option
                                value="1"
                                @selected($filters['primary'] === '1')
                            >
                                Primary only
                            </option>

                            <option
                                value="0"
                                @selected($filters['primary'] === '0')
                            >
                                Non-primary
                            </option>

                        </select>

                    </div>

                    <div class="col-12 col-md-6 col-xl-2">

                        <label
                            for="sort"
                            class="form-label fw-semibold"
                        >
                            Sort by
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
                                Last name
                            </option>

                            <option
                                value="first_name"
                                @selected($filters['sort'] === 'first_name')
                            >
                                First name
                            </option>

                            <option
                                value="job_title"
                                @selected($filters['sort'] === 'job_title')
                            >
                                Job title
                            </option>

                            <option
                                value="created_at"
                                @selected($filters['sort'] === 'created_at')
                            >
                                Created
                            </option>

                        </select>

                    </div>

                    <div class="col-12 col-md-6 col-xl-1">

                        <label
                            for="direction"
                            class="form-label fw-semibold"
                        >
                            Order
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
                                A-Z
                            </option>

                            <option
                                value="desc"
                                @selected($filters['direction'] === 'desc')
                            >
                                Z-A
                            </option>

                        </select>

                    </div>

                </div>

                <div class="d-flex justify-content-end gap-2 mt-3">

                    <a
                        href="{{ route('contacts.index') }}"
                        class="btn btn-outline-secondary"
                    >
                        Reset
                    </a>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Apply filters
                    </button>

                </div>

            </form>

        </div>

    </div>

    <div class="card fm-card">

        <div class="table-responsive">

            <table class="table align-middle mb-0 fm-table">

                <thead>
                    <tr>
                        <th>Contact</th>
                        <th>Company</th>
                        <th>Role</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Type</th>
                        <th class="text-end">Actions</th>
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
                                        Primary
                                    </span>

                                @else

                                    <span class="badge text-bg-light border text-secondary">
                                        Standard
                                    </span>

                                @endif

                            </td>

                            <td class="text-end">

                                <div class="btn-group">

                                    <a
                                        href="{{ route('contacts.show', $contact) }}"
                                        class="btn btn-sm btn-outline-secondary"
                                        title="View"
                                    >
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    @can('update', $contact)

                                        <a
                                            href="{{ route('contacts.edit', $contact) }}"
                                            class="btn btn-sm btn-outline-secondary"
                                            title="Edit"
                                        >
                                            <i class="bi bi-pencil"></i>
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
                                                class="btn btn-sm btn-outline-danger rounded-start-0"
                                                title="Delete"
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
                                    No contacts found
                                </div>

                                <div class="text-secondary">
                                    Try changing the search filters or create a new contact.
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
