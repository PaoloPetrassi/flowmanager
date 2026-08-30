@extends('layouts.app')

@section('title', __('Notifications'))
@section('page-title', __('Notifications'))
@section('page-subtitle', __('Assignments, comments and updates that require your attention'))

@section('content')
    <div class="d-flex justify-content-end mb-3">
        @if (auth()->user()->unreadNotifications()->exists())
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-outline-primary">
                    <i class="bi bi-check2-all me-1"></i>
                    {{ __('Mark all as read') }}
                </button>
            </form>
        @endif
    </div>

    <div class="card fm-card">
        <div class="list-group list-group-flush">
            @forelse ($notifications as $notification)
                @php($data = $notification->data)
                <div class="list-group-item fm-notification-row {{ $notification->read_at ? '' : 'is-unread' }}">
                    <div class="fm-notification-icon">
                        <i class="bi {{ $data['icon'] ?? 'bi-bell' }}"></i>
                    </div>

                    <div class="flex-grow-1 min-w-0">
                        <a href="{{ route('notifications.open', $notification->id) }}" class="fw-semibold text-dark d-block">
                            {{ __($data['title_key'] ?? 'Notification') }}
                        </a>
                        <div class="text-secondary small mt-1">
                            {{ __($data['message_key'] ?? '', $data['parameters'] ?? []) }}
                        </div>
                        <div class="small text-secondary mt-1">{{ $notification->created_at->diffForHumans() }}</div>
                    </div>

                    @if (! $notification->read_at)
                        <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-sm btn-outline-secondary" title="{{ __('Mark as read') }}">
                                <i class="bi bi-check2"></i>
                            </button>
                        </form>
                    @endif
                </div>
            @empty
                <div class="p-5 text-center text-secondary">
                    <i class="bi bi-bell d-block fs-2 mb-2"></i>
                    {{ __('You have no notifications yet.') }}
                </div>
            @endforelse
        </div>

        @if ($notifications->hasPages())
            <div class="card-footer bg-white">{{ $notifications->links() }}</div>
        @endif
    </div>
@endsection
