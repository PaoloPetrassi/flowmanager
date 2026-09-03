@extends('layouts.app')

@section('title', __('Search'))
@section('page-title', __('Global search'))
@section('page-subtitle', __('Find records across every module you can access'))

@section('content')
    <div class="card fm-card mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('search.index') }}" class="row g-3 align-items-end">
                <div class="col-12 col-lg-8">
                    <label for="global-search-query" class="form-label fw-semibold">{{ __('Search FlowManager') }}</label>
                    <div class="fm-search-page-form">
                        <i class="bi bi-search"></i>
                        <input
                            id="global-search-query"
                            type="search"
                            name="q"
                            value="{{ $query }}"
                            class="form-control form-control-lg"
                            placeholder="{{ __('Search companies, contacts, projects, tasks, assets, tickets and users...') }}"
                            autofocus
                        >
                    </div>
                </div>

                <div class="col-12 col-md-8 col-lg-3">
                    <label for="global-search-scope" class="form-label fw-semibold">{{ __('Search in') }}</label>
                    <select id="global-search-scope" name="scope" class="form-select form-select-lg">
                        <option value="">{{ __('All accessible modules') }}</option>
                        @foreach ($scopes as $key => $label)
                            <option value="{{ $key }}" @selected($scope === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-4 col-lg-1 d-grid">
                    <button type="submit" class="btn btn-primary btn-lg" title="{{ __('Search') }}"><i class="bi bi-search"></i></button>
                </div>
            </form>

            <div class="form-text mt-2">{{ __('Enter at least 2 characters. Results respect your current permissions and prioritize prefix matches.') }}</div>
        </div>
    </div>

    @if ($query !== '' && mb_strlen($query) < 2)
        <div class="alert alert-info">{{ __('Enter at least 2 characters to start searching.') }}</div>
    @elseif ($query !== '')
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
            <h2 class="h6 mb-0">{{ __('Results for ":query"', ['query' => $query]) }}</h2>
            <span class="text-secondary small">{{ trans_choice('ui.counts.search_results', $resultCount, ['count' => $resultCount]) }}</span>
        </div>

        @forelse ($groups as $group)
            <div class="card fm-card mb-4">
                <div class="card-header fm-card-header">
                    <div class="d-flex align-items-center gap-2">
                        <span class="fm-search-group-icon"><i class="bi {{ $group['icon'] }}"></i></span>
                        <h2 class="fm-card-title">{{ __($group['label']) }}</h2>
                    </div>
                    <span class="badge text-bg-light border">{{ count($group['items']) }}</span>
                </div>
                <div class="list-group list-group-flush">
                    @foreach ($group['items'] as $item)
                        <a href="{{ $item['url'] }}" class="list-group-item list-group-item-action fm-search-result">
                            <div class="fm-search-result-icon"><i class="bi {{ $item['icon'] }}"></i></div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="fw-semibold text-dark">{{ $item['title'] }}</div>
                                @if ($item['subtitle'])<div class="small text-secondary text-truncate">{{ $item['subtitle'] }}</div>@endif
                            </div>
                            @if ($item['meta'])<span class="small text-secondary d-none d-md-inline">{{ $item['meta'] }}</span>@endif
                            <i class="bi bi-chevron-right text-secondary"></i>
                        </a>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="card fm-card">
                <div class="card-body p-5 text-center text-secondary">
                    <i class="bi bi-search d-block fs-2 mb-2"></i>
                    {{ __('No results found for this search.') }}
                </div>
            </div>
        @endforelse
    @endif
@endsection
