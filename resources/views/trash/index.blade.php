@extends('layouts.app')

@section('title', __('Trash'))
@section('page-title', __('Trash'))
@section('page-subtitle', __('Restore soft-deleted records or permanently remove them'))

@section('content')
    <div class="alert alert-warning d-flex gap-2 align-items-start">
        <i class="bi bi-exclamation-triangle mt-1"></i>
        <div>{{ __('Permanent deletion cannot be undone. FlowManager blocks it when required dependent records still exist.') }}</div>
    </div>

    <div class="card fm-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('trash.index') }}" class="row g-3 align-items-end">
                <div class="col-12 col-md-6 col-lg-5">
                    <label class="form-label">{{ __('Search') }}</label>
                    <input type="search" name="search" value="{{ $filters['search'] }}" class="form-control" placeholder="{{ __('Search deleted records') }}">
                </div>
                <div class="col-12 col-md-4 col-lg-3">
                    <label class="form-label">{{ __('Resource') }}</label>
                    <select name="type" class="form-select">
                        <option value="">{{ __('All resources') }}</option>
                        @foreach ($resources as $resource)
                            <option value="{{ $resource }}" @selected($filters['type'] === $resource)>{{ __(ucfirst($resource)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-2 d-grid">
                    <button type="submit" class="btn btn-primary">{{ __('Apply') }}</button>
                </div>
                @if ($filters['type'] || $filters['search'])
                    <div class="col-12 col-md-auto d-grid">
                        <a href="{{ route('trash.index') }}" class="btn btn-outline-secondary">{{ __('Reset') }}</a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <div class="card fm-card">
        <div class="table-responsive">
            <table class="table align-middle mb-0 fm-table">
                <thead>
                    <tr>
                        <th>{{ __('Resource') }}</th>
                        <th>{{ __('Record') }}</th>
                        <th>{{ __('Deleted at') }}</th>
                        <th class="text-end">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        <tr>
                            <td><span class="badge text-bg-light border">{{ $item['resource'] }}</span></td>
                            <td class="fw-semibold">{{ $item['label'] }}</td>
                            <td>
                                <div>{{ $item['deleted_at']?->format('d/m/Y H:i') ?: '—' }}</div>
                                @if ($item['deleted_at'])<div class="small text-secondary">{{ $item['deleted_at']->diffForHumans() }}</div>@endif
                            </td>
                            <td>
                                <div class="d-flex justify-content-end gap-2">
                                    @if (auth()->user()->hasPermission('trash.restore'))
                                        <form method="POST" action="{{ route('trash.restore', [$item['type'], $item['model']->id]) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-success">
                                                <i class="bi bi-arrow-counterclockwise me-1"></i>{{ __('Restore') }}
                                            </button>
                                        </form>
                                    @endif

                                    @if (auth()->user()->hasPermission('trash.delete'))
                                        <form method="POST" action="{{ route('trash.destroy', [$item['type'], $item['model']->id]) }}" onsubmit="return confirm(@js(__('Permanently delete this item? This action cannot be undone.')));">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash3 me-1"></i>{{ __('Delete permanently') }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center py-5 text-secondary">{{ __('Trash is empty for the selected filters.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($items->hasPages())
            <div class="card-footer bg-white">{{ $items->links() }}</div>
        @endif
    </div>
@endsection
