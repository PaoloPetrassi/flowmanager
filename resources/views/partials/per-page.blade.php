@php
    $currentPerPage = (int) request('per_page', 15);
    $preservedQuery = collect(request()->query())->except(['page', 'per_page'])->all();
@endphp

<form method="GET" action="{{ url()->current() }}" class="d-flex align-items-center gap-2 fm-per-page-form">
    @foreach ($preservedQuery as $key => $value)
        @if (is_array($value))
            @foreach ($value as $item)
                <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
            @endforeach
        @else
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endif
    @endforeach

    <label for="per-page-{{ $resourceName }}" class="small text-secondary text-nowrap">{{ __('Rows per page') }}</label>
    <select
        id="per-page-{{ $resourceName }}"
        name="per_page"
        class="form-select form-select-sm"
        style="width:auto"
        onchange="this.form.submit()"
    >
        @foreach ([15, 25, 50, 100] as $option)
            <option value="{{ $option }}" @selected($currentPerPage === $option)>{{ $option }}</option>
        @endforeach
    </select>
</form>
