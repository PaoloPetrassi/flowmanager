@php
    $savedFilters = auth()->user()->savedFilters()->where('resource_type', $filterResource)->orderBy('name')->get();
    $activeFilterPayload = collect(request()->query())->except('page')->filter(fn ($value) => $value !== null && $value !== '')->all();
@endphp
<div class="d-flex align-items-center gap-2 flex-wrap mb-4">
    @foreach ($savedFilters as $savedFilter)
        <div class="btn-group btn-group-sm">
            <a href="{{ route($filterRoute, $savedFilter->filters) }}" class="btn btn-outline-secondary"><i class="bi bi-bookmark me-1"></i>{{ $savedFilter->name }}</a>
            <form method="POST" action="{{ route('saved-filters.destroy', $savedFilter) }}">@csrf @method('DELETE')<button class="btn btn-outline-secondary rounded-start-0" title="{{ __('Delete saved filter') }}"><i class="bi bi-x"></i></button></form>
        </div>
    @endforeach
    @if ($activeFilterPayload !== [])
        <form method="POST" action="{{ route('saved-filters.store') }}" class="d-flex gap-2 ms-auto">
            @csrf
            <input type="hidden" name="resource_type" value="{{ $filterResource }}">
            @foreach ($activeFilterPayload as $key => $value)
                @if (is_array($value))
                    @foreach ($value as $item)<input type="hidden" name="filters[{{ $key }}][]" value="{{ $item }}">@endforeach
                @else
                    <input type="hidden" name="filters[{{ $key }}]" value="{{ $value }}">
                @endif
            @endforeach
            <input name="name" class="form-control form-control-sm" required maxlength="100" placeholder="{{ __('Filter name') }}">
            <button class="btn btn-sm btn-outline-primary text-nowrap"><i class="bi bi-bookmark-plus me-1"></i>{{ __('Save filter') }}</button>
        </form>
    @endif
</div>
