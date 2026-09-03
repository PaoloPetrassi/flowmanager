@php
    $bulkPriorities = $priorities ?? [];
    $bulkUsers = $users ?? collect();
@endphp

<div class="fm-bulk-toolbar" data-bulk-toolbar>
    <strong><span data-bulk-count>0</span> {{ __('selected') }}</strong>

    <form
        method="POST"
        action="{{ route('bulk.update', $bulkResource) }}"
        class="d-flex align-items-center gap-2 flex-wrap"
        data-bulk-form
        data-bulk-action-form
        data-delete-confirm="{{ __('Delete the selected records?') }}"
    >
        @csrf

        <select name="action" class="form-select form-select-sm" style="width:auto" required data-bulk-action>
            <option value="status">{{ __('Change status') }}</option>
            @if (count($bulkPriorities) > 0)
                <option value="priority">{{ __('Change priority') }}</option>
            @endif
            @if ($bulkUsers->count() > 0)
                <option value="assign">{{ __('Change assignment') }}</option>
            @endif
            <option value="delete">{{ __('Delete') }}</option>
        </select>

        <select name="value" class="form-select form-select-sm" style="width:auto;max-width:260px" data-bulk-value>
            <option value="">{{ __('Select value') }}</option>

            <optgroup label="{{ __('Status') }}" data-bulk-value-group="status">
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}">{{ $status->label() }}</option>
                @endforeach
            </optgroup>

            @if (count($bulkPriorities) > 0)
                <optgroup label="{{ __('Priority') }}" data-bulk-value-group="priority">
                    @foreach ($bulkPriorities as $priority)
                        <option value="{{ $priority->value }}">{{ $priority->label() }}</option>
                    @endforeach
                </optgroup>
            @endif

            @if ($bulkUsers->count() > 0)
                <optgroup label="{{ __('Assignment') }}" data-bulk-value-group="assign">
                    <option value="">{{ __('Unassigned') }}</option>
                    @foreach ($bulkUsers as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </optgroup>
            @endif
        </select>

        <button class="btn btn-sm btn-primary" type="submit">{{ __('Apply') }}</button>
    </form>
</div>
