@extends('layouts.app')

@section('title', __('API tokens'))
@section('page-title', __('API tokens'))
@section('page-subtitle', __('Create revocable credentials for external integrations'))

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div class="text-secondary">
            {{ __('Use scoped bearer tokens to access the FlowManager REST API.') }}
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('integrations.api.docs') }}" class="btn btn-outline-primary">
                <i class="bi bi-braces me-2"></i>{{ __('API documentation') }}
            </a>
            <a href="{{ route('api.openapi') }}" class="btn btn-outline-secondary" target="_blank" rel="noopener">
                <i class="bi bi-filetype-json me-2"></i>OpenAPI JSON
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-4">
            <div class="card fm-card">
                <div class="card-body">
                    <form method="POST" action="{{ route('integrations.api.store') }}">
                        @csrf

                        <h5>{{ __('Create token') }}</h5>

                        <div class="mb-3">
                            <label class="form-label" for="api-token-name">{{ __('Name') }}</label>
                            <input
                                id="api-token-name"
                                class="form-control @error('name') is-invalid @enderror"
                                name="name"
                                value="{{ old('name') }}"
                                required
                            >
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="api-token-expires">{{ __('Expires at') }}</label>
                            <input
                                id="api-token-expires"
                                class="form-control @error('expires_at') is-invalid @enderror"
                                type="date"
                                name="expires_at"
                                value="{{ old('expires_at') }}"
                            >
                            @error('expires_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label d-block">{{ __('Abilities') }}</label>

                            <div class="form-check form-check-inline">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="abilities[]"
                                    value="read"
                                    id="ability-read"
                                    {{ in_array('read', old('abilities', ['read']), true) ? 'checked' : '' }}
                                >
                                <label class="form-check-label" for="ability-read">Read</label>
                            </div>

                            <div class="form-check form-check-inline">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="abilities[]"
                                    value="write"
                                    id="ability-write"
                                    {{ in_array('write', old('abilities', []), true) ? 'checked' : '' }}
                                >
                                <label class="form-check-label" for="ability-write">Write</label>
                            </div>

                            @error('abilities')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                            @error('abilities.*')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-warning small">
                            {{ __('The complete token is shown only once after creation.') }}
                        </div>

                        <button
                            class="btn btn-primary w-100"
                            {{ auth()->user()->isDemoAccount() && config('flowmanager.demo.read_only') ? 'disabled' : '' }}
                        >
                            {{ __('Generate token') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="card fm-card">
                <div class="table-responsive">
                    <table class="table fm-table mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Prefix') }}</th>
                                <th>{{ __('Abilities') }}</th>
                                <th>{{ __('Last used') }}</th>
                                <th>{{ __('Expires') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($tokens as $token)
                                <tr>
                                    <td>{{ $token->name }}</td>
                                    <td><code>{{ $token->token_prefix }}…</code></td>
                                    <td>{{ implode(', ', $token->abilities ?? []) }}</td>
                                    <td>{{ $token->last_used_at?->diffForHumans() ?: '—' }}</td>
                                    <td>{{ $token->expires_at?->format('d/m/Y') ?: '—' }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('integrations.api.destroy', $token) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                class="btn btn-sm btn-outline-danger"
                                                {{ auth()->user()->isDemoAccount() && config('flowmanager.demo.read_only') ? 'disabled' : '' }}
                                            >
                                                {{ __('Revoke') }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">{{ __('No API tokens.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
