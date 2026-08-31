<div class="fm-bulk-toolbar" data-bulk-toolbar>
    <strong><span data-bulk-count>0</span> {{ __('selected') }}</strong>
    <form method="POST" action="{{ route('bulk.update', $bulkResource) }}" class="d-flex align-items-center gap-2 flex-wrap" data-bulk-form>
        @csrf
        <select name="action" class="form-select form-select-sm" style="width:auto" required>
            <option value="status">{{ __('Change status') }}</option>
            <option value="priority">{{ __('Change priority') }}</option>
            <option value="delete">{{ __('Delete') }}</option>
        </select>
        <select name="value" class="form-select form-select-sm" style="width:auto">
            <option value="">{{ __('Select value') }}</option>
            @foreach ($statuses as $status)<option value="{{ $status->value }}">{{ __('Status') }} · {{ $status->label() }}</option>@endforeach
            @foreach ($priorities as $priority)<option value="{{ $priority->value }}">{{ __('Priority') }} · {{ $priority->label() }}</option>@endforeach
        </select>
        <button class="btn btn-sm btn-primary" type="submit">{{ __('Apply') }}</button>
    </form>
</div>
