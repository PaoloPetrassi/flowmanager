@extends('layouts.app')

@section('title', 'Companies')

@section('page-title', 'Companies')

@section('page-subtitle')
    Manage customers, suppliers, partners and prospects
@endsection

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <span class="text-secondary">
                {{ $companies->total() }}
                {{ Str::plural('company', $companies->total()) }}
            </span>
        </div>

        @can('create', App\Models\Company::class)

            <a
                href="{{ route('companies.create') }}"
                class="btn btn-primary"
            >
                <i class="bi bi-plus-lg me-1"></i>
                New company
            </a>

        @endcan

    </div>

    <div class="card fm-card mb-4">

        <div class="card-body p-4">

            <form
                method="GET"
                action="{{ route('companies.index') }}"
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
                                placeholder="Name, VAT, email, city..."
                            >

                        </div>

                    </div>

                    <div class="col-12 col-md-6 col-xl-2">

                        <label
                            for="type"
                            class="form-label fw-semibold"
                        >
                            Type
                        </label>

                        <select
                            id="type"
                            name="type"
                            class="form-select"
                        >

                            <option value="">
                                All types
                            </option>

                            @foreach ($types as $type)

                                <option
                                    value="{{ $type->value }}"
                                    @selected(
                                        $filters['type'] === $type->value
                                    )
                                >
                                    {{ $type->label() }}
                                </option>

                            @endforeach

                        </select>

                    </div>

                    <div class="col-12 col-md-6 col-xl-2">

                        <label
                            for="status"
                            class="form-label fw-semibold"
                        >
                            Status
                        </label>

                        <select
                            id="status"
                            name="status"
                            class="form-select"
                        >

                            <option value="">
                                All statuses
                            </option>

                            @foreach ($statuses as $status)

                                <option
                                    value="{{ $status->value }}"
                                    @selected(
                                        $filters['status'] === $status->value
                                    )
                                >
                                    {{ $status->label() }}
                                </option>

                            @endforeach

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
                                value="name"
                                @selected($filters['sort'] === 'name')
                            >
                                Name
                            </option>

                            <option
                                value="type"
                                @selected($filters['sort'] === 'type')
                            >
                                Type
                            </option>

                            <option
                                value="status"
                                @selected($filters['sort'] === 'status')
                            >
                                Status
                            </option>

                            <option
                                value="city"
                                @selected($filters['sort'] === 'city')
                            >
                                City
                            </option>

                            <option
                                value="industry"
                                @selected($filters['sort'] === 'industry')
                            >
                                Industry
                            </option>

                            <option
                                value="created_at"
                                @selected($filters['sort'] === 'created_at')
                            >
                                Created
                            </option>

                        </select>

                    </div>

                    <div class="col-12 col-md-6 col-xl-2">

                        <label
                            for="direction"
                            class="form-label fw-semibold"
                        >
                            Direction
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
                                Ascending
                            </option>

                            <option
                                value="desc"
                                @selected($filters['direction'] === 'desc')
                            >
                                Descending
                            </option>

                        </select>

                    </div>

                </div>

                <div class="d-flex justify-content-end gap-2 mt-3">

                    <a
                        href="{{ route('companies.index') }}"
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
                        <th>Company</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Industry</th>
                        <th>Location</th>
                        <th>Contact</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse ($companies as $company)

                        <tr>

                            <td>

                                <a
                                    href="{{ route('companies.show', $company) }}"
                                    class="fw-semibold text-dark"
                                >
                                    {{ $company->name }}
                                </a>

                                @if ($company->legal_name)

                                    <div class="small text-secondary">
                                        {{ $company->legal_name }}
                                    </div>

                                @endif

                                @if ($company->vat_number)

                                    <div class="small text-secondary">
                                        VAT {{ $company->vat_number }}
                                    </div>

                                @endif

                            </td>

                            <td>
                                {{ $company->type->label() }}
                            </td>

                            <td>

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

                            </td>

                            <td>
                                {{ $company->industry ?: '—' }}
                            </td>

                            <td>

                                {{ $company->city ?: '—' }}

                                @if ($company->country_code)

                                    <div class="small text-secondary">
                                        {{ $company->country_code }}
                                    </div>

                                @endif

                            </td>

                            <td>

                                @if ($company->email)

                                    <div>
                                        {{ $company->email }}
                                    </div>

                                @endif

                                @if ($company->phone)

                                    <div class="small text-secondary">
                                        {{ $company->phone }}
                                    </div>

                                @endif

                                @if (! $company->email && ! $company->phone)
                                    —
                                @endif

                            </td>

                            <td class="text-end">

                                <div class="btn-group">

                                    <a
                                        href="{{ route('companies.show', $company) }}"
                                        class="btn btn-sm btn-outline-secondary"
                                        title="View"
                                    >
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    @can('update', $company)

                                        <a
                                            href="{{ route('companies.edit', $company) }}"
                                            class="btn btn-sm btn-outline-secondary"
                                            title="Edit"
                                        >
                                            <i class="bi bi-pencil"></i>
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

                                <i class="bi bi-buildings fs-1 text-secondary"></i>

                                <div class="fw-semibold mt-3">
                                    No companies found
                                </div>

                                <div class="text-secondary">
                                    Try changing the search filters or create a new company.
                                </div>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        @if ($companies->hasPages())

            <div class="card-footer bg-white p-3">

                {{ $companies->links() }}

            </div>

        @endif

    </div>

@endsection