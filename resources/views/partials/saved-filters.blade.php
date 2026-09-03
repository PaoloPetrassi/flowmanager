@php
    $savedFilters = auth()->user()
        ->savedFilters()
        ->where('resource_type', $filterResource)
        ->orderByDesc('is_default')
        ->orderBy('name')
        ->get();

    $activeFilterPayload = collect(request()->query())
        ->except('page')
        ->filter(fn ($value) => $value !== null && $value !== '')
        ->all();
@endphp

<div class="fm-saved-filters d-flex align-items-center gap-2 flex-wrap mb-4">
    @if ($savedFilters->isNotEmpty())
        <span class="small fw-semibold text-secondary me-1">{{ __('Saved views') }}</span>
    @endif

    @foreach ($savedFilters as $savedFilter)
        <div class="btn-group btn-group-sm">
            <a
                href="{{ route($filterRoute, $savedFilter->filters) }}"
                class="btn {{ $savedFilter->is_default ? 'btn-primary' : 'btn-outline-secondary' }}"
                title="{{ $savedFilter->is_default ? __('Default saved view') : __('Open saved view') }}"
            >
                <i class="bi {{ $savedFilter->is_default ? 'bi-star-fill' : 'bi-bookmark' }} me-1"></i>
                {{ $savedFilter->name }}
            </a>

            <form method="POST" action="{{ route('saved-filters.default', $savedFilter) }}">
                @csrf
                @method('PATCH')
                <button
                    type="submit"
                    class="btn btn-outline-secondary rounded-0"
                    title="{{ $savedFilter->is_default ? __('Clear default view') : __('Make default view') }}"
                    aria-label="{{ $savedFilter->is_default ? __('Clear default view') : __('Make default view') }}"
                >
                    <i class="bi {{ $savedFilter->is_default ? 'bi-star-slash' : 'bi-star' }}"></i>
                </button>
            </form>

            <form method="POST" action="{{ route('saved-filters.destroy', $savedFilter) }}">
                @csrf
                @method('DELETE')
                <button
                    type="submit"
                    class="btn btn-outline-secondary rounded-start-0"
                    title="{{ __('Delete saved filter') }}"
                    aria-label="{{ __('Delete saved filter') }}"
                    data-confirm="{{ __('Delete this saved filter?') }}"
                >
                    <i class="bi bi-x"></i>
                </button>
            </form>
        </div>
    @endforeach

    @if ($activeFilterPayload !== [])
        <form method="POST" action="{{ route('saved-filters.store') }}" class="d-flex align-items-center gap-2 flex-wrap ms-auto">
            @csrf
            <input type="hidden" name="resource_type" value="{{ $filterResource }}">

            @foreach ($activeFilterPayload as $key => $value)
                @if (is_array($value))
                    @foreach ($value as $item)
                        <input type="hidden" name="filters[{{ $key }}][]" value="{{ $item }}">
                    @endforeach
                @else
                    <input type="hidden" name="filters[{{ $key }}]" value="{{ $value }}">
                @endif
            @endforeach

            <input
                name="name"
                class="form-control form-control-sm"
                required
                maxlength="100"
                placeholder="{{ __('View name') }}"
                aria-label="{{ __('View name') }}"
            >

            <div class="form-check form-check-inline mb-0">
                <input class="form-check-input" type="checkbox" name="is_default" value="1" id="default-filter-{{ $filterResource }}">
                <label class="form-check-label small" for="default-filter-{{ $filterResource }}">{{ __('Default') }}</label>
            </div>

            <button class="btn btn-sm btn-outline-primary text-nowrap">
                <i class="bi bi-bookmark-plus me-1"></i>{{ __('Save view') }}
            </button>
        </form>
    @endif
</div>
