@extends('layouts.app')

@section('title', __('Notifications'))
@section('page-title', __('Notifications'))
@section('page-subtitle', __('Assignments, comments, reminders and automation alerts that require your attention'))

@section('content')
    <div class="card fm-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('notifications.index') }}" class="row g-3 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label" for="notification-status">{{ __('Status') }}</label>
                    <select id="notification-status" class="form-select" name="status">
                        <option value="all" @selected($filters['status'] === 'all')>{{ __('All') }}</option>
                        <option value="unread" @selected($filters['status'] === 'unread')>{{ __('Unread') }} ({{ $unreadCount }})</option>
                        <option value="read" @selected($filters['status'] === 'read')>{{ __('Read') }} ({{ $readCount }})</option>
                    </select>
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label" for="notification-category">{{ __('Category') }}</label>
                    <select id="notification-category" class="form-select" name="category">
                        <option value="">{{ __('All categories') }}</option>
                        @foreach ($categories as $key => $label)
                            <option value="{{ $key }}" @selected($filters['category'] === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-4 d-flex gap-2">
                    <button class="btn btn-primary" type="submit">
                        <i class="bi bi-funnel me-1"></i>{{ __('Apply filters') }}
                    </button>
                    @if ($filters['status'] !== 'all' || $filters['category'] !== '')
                        <a class="btn btn-outline-secondary" href="{{ route('notifications.index') }}">{{ __('Reset') }}</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap mb-3">
        <div class="d-flex gap-2 flex-wrap">
            <span class="badge rounded-pill text-bg-primary">{{ __('Unread') }}: {{ $unreadCount }}</span>
            <span class="badge rounded-pill text-bg-light">{{ __('Read') }}: {{ $readCount }}</span>
        </div>

        <div class="d-flex gap-2 flex-wrap">
            @if ($unreadCount > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-check2-all me-1"></i>{{ __('Mark all as read') }}
                    </button>
                </form>
            @endif

            @if ($readCount > 0)
                <form method="POST" action="{{ route('notifications.clear-read') }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger" data-confirm="{{ __('Delete all read notifications?') }}">
                        <i class="bi bi-trash3 me-1"></i>{{ __('Clear read') }}
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="card fm-card">
        <div class="list-group list-group-flush">
            @forelse ($notifications as $notification)
                @php
                    $data = $notification->data;
                    $category = $data['category'] ?? 'system';
                    $categoryLabel = $categories[$category] ?? __('System');
                @endphp

                <div class="list-group-item fm-notification-row {{ $notification->read_at ? '' : 'is-unread' }}">
                    <div class="fm-notification-icon">
                        <i class="bi {{ $data['icon'] ?? 'bi-bell' }}"></i>
                    </div>

                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <a href="{{ route('notifications.open', $notification->id) }}" class="fw-semibold text-dark d-block">
                                {{ __($data['title_key'] ?? 'Notification') }}
                            </a>
                            <span class="badge fm-notification-category">{{ $categoryLabel }}</span>
                            @if (! $notification->read_at)
                                <span class="badge text-bg-primary">{{ __('Unread') }}</span>
                            @endif
                        </div>

                        <div class="text-secondary small mt-1">
                            {{ __($data['message_key'] ?? '', $data['parameters'] ?? []) }}
                        </div>
                        <div class="small text-secondary mt-1">{{ $notification->created_at->diffForHumans() }}</div>
                    </div>

                    <div class="d-flex gap-1 align-items-center flex-shrink-0">
                        @if (! $notification->read_at)
                            <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-outline-secondary" title="{{ __('Mark as read') }}" aria-label="{{ __('Mark as read') }}">
                                    <i class="bi bi-check2"></i>
                                </button>
                            </form>
                        @endif

                        <form method="POST" action="{{ route('notifications.destroy', $notification->id) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('Delete notification') }}" aria-label="{{ __('Delete notification') }}" data-confirm="{{ __('Delete this notification?') }}">
                                <i class="bi bi-trash3"></i>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="p-5 text-center text-secondary">
                    <i class="bi bi-bell d-block fs-2 mb-2"></i>
                    {{ __('No notifications match the selected filters.') }}
                </div>
            @endforelse
        </div>

        @if ($notifications->hasPages())
            <div class="card-footer bg-white">{{ $notifications->links() }}</div>
        @endif
    </div>
@endsection
