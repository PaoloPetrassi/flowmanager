<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', __('Dashboard')) | {{ config('app.name') }}</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])
</head>
<body>
    <div class="fm-app">
        <div class="fm-sidebar-backdrop" data-fm-sidebar-backdrop></div>

        <aside id="fm-sidebar" class="fm-sidebar" data-fm-sidebar>
            <div class="fm-sidebar-header">
                <a href="{{ route('dashboard') }}" class="fm-sidebar-brand">
                    <span class="fm-sidebar-brand-mark">FM</span>
                    <span>FlowManager</span>
                </a>

                <button type="button" class="fm-sidebar-close" data-fm-sidebar-toggle aria-controls="fm-sidebar" aria-expanded="false" aria-label="{{ __('Close navigation') }}">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <nav class="fm-sidebar-nav">
                <div class="fm-nav-section">{{ __('Overview') }}</div>

                <a href="{{ route('dashboard') }}" class="fm-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid"></i>
                    <span>{{ __('Dashboard') }}</span>
                </a>

                <a href="{{ route('search.index') }}" class="fm-nav-link {{ request()->routeIs('search.*') ? 'active' : '' }}">
                    <i class="bi bi-search"></i>
                    <span>{{ __('Global search') }}</span>
                </a>

                <div class="fm-nav-section">{{ __('CRM') }}</div>

                @can('viewAny', App\Models\Company::class)
                    <a href="{{ route('companies.index') }}" class="fm-nav-link {{ request()->routeIs('companies.*') ? 'active' : '' }}">
                        <i class="bi bi-buildings"></i>
                        <span>{{ __('Companies') }}</span>
                    </a>
                @endcan

                @can('viewAny', App\Models\Contact::class)
                    <a href="{{ route('contacts.index') }}" class="fm-nav-link {{ request()->routeIs('contacts.*') ? 'active' : '' }}">
                        <i class="bi bi-person-vcard"></i>
                        <span>{{ __('Contacts') }}</span>
                    </a>
                @endcan

                <div class="fm-nav-section">{{ __('Operations') }}</div>

                @can('viewAny', App\Models\Project::class)
                    <a href="{{ route('projects.index') }}" class="fm-nav-link {{ request()->routeIs('projects.*') ? 'active' : '' }}">
                        <i class="bi bi-kanban"></i>
                        <span>{{ __('Projects') }}</span>
                    </a>
                @endcan

                @can('viewAny', App\Models\Task::class)
                    <a href="{{ route('tasks.index') }}" class="fm-nav-link {{ request()->routeIs('tasks.*') ? 'active' : '' }}">
                        <i class="bi bi-check2-square"></i>
                        <span>{{ __('Tasks') }}</span>
                        @if (($sidebarWorkCounts['tasks'] ?? 0) > 0)
                            <span class="fm-nav-counter">{{ $sidebarWorkCounts['tasks'] }}</span>
                        @endif
                    </a>
                @endcan

                @can('viewAny', App\Models\Asset::class)
                    <a href="{{ route('assets.index') }}" class="fm-nav-link {{ request()->routeIs('assets.*') ? 'active' : '' }}">
                        <i class="bi bi-laptop"></i>
                        <span>{{ __('Assets') }}</span>
                    </a>
                @endcan

                <div class="fm-nav-section">{{ __('Support') }}</div>

                @can('viewAny', App\Models\Ticket::class)
                    <a href="{{ route('tickets.index') }}" class="fm-nav-link {{ request()->routeIs('tickets.*') ? 'active' : '' }}">
                        <i class="bi bi-ticket-perforated"></i>
                        <span>{{ __('Tickets') }}</span>
                        @if (($sidebarWorkCounts['tickets'] ?? 0) > 0)
                            <span class="fm-nav-counter">{{ $sidebarWorkCounts['tickets'] }}</span>
                        @endif
                    </a>
                @endcan

                <div class="fm-nav-section">{{ __('Planning') }}</div>

                @if (
                    auth()->user()->can('viewAny', App\Models\Project::class)
                    || auth()->user()->can('viewAny', App\Models\Task::class)
                    || auth()->user()->can('viewAny', App\Models\Asset::class)
                )
                    <a href="{{ route('calendar.index') }}" class="fm-nav-link {{ request()->routeIs('calendar.*') ? 'active' : '' }}">
                        <i class="bi bi-calendar3"></i>
                        <span>{{ __('Calendar') }}</span>
                    </a>
                @endif

                @can('viewAny', App\Models\Project::class)
                    <a href="{{ route('planning.gantt') }}" class="fm-nav-link {{ request()->routeIs('planning.gantt') ? 'active' : '' }}">
                        <i class="bi bi-bar-chart-steps"></i>
                        <span>{{ __('Gantt') }}</span>
                    </a>
                @endcan

                @if (auth()->user()->hasPermission('workload.view'))
                    <a href="{{ route('planning.workload') }}" class="fm-nav-link {{ request()->routeIs('planning.workload') ? 'active' : '' }}">
                        <i class="bi bi-people-fill"></i>
                        <span>{{ __('Workload') }}</span>
                    </a>
                @endif

                @can('create', App\Models\Project::class)
                    <a href="{{ route('project-templates.index') }}" class="fm-nav-link {{ request()->routeIs('project-templates.*') ? 'active' : '' }}">
                        <i class="bi bi-copy"></i>
                        <span>{{ __('Project templates') }}</span>
                    </a>
                @endcan

                @if (
                    auth()->user()->can('viewAny', App\Models\Task::class)
                    || auth()->user()->can('viewAny', App\Models\Ticket::class)
                )
                    <a href="{{ route('boards.index') }}" class="fm-nav-link {{ request()->routeIs('boards.*') ? 'active' : '' }}">
                        <i class="bi bi-columns-gap"></i>
                        <span>{{ __('Kanban') }}</span>
                    </a>
                @endif

                @if (auth()->user()->hasPermission('reports.view'))
                    <a href="{{ route('reports.index') }}" class="fm-nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                        <i class="bi bi-bar-chart-line"></i>
                        <span>{{ __('Reports') }}</span>
                    </a>
                @endif

                @if (auth()->user()->hasPermission('automations.view'))
                    <a href="{{ route('automations.index') }}" class="fm-nav-link {{ request()->routeIs('automations.*') ? 'active' : '' }}">
                        <i class="bi bi-lightning-charge"></i>
                        <span>{{ __('Automations') }}</span>
                    </a>
                @endif

                @if (
                    auth()->user()->can('viewAny', App\Models\User::class)
                    || auth()->user()->can('viewAny', App\Models\Role::class)
                    || auth()->user()->hasPermission('audit.view')
                    || auth()->user()->hasPermission('trash.view')
                    || auth()->user()->hasPermission('system.view')
                )
                    <div class="fm-nav-section">{{ __('Administration') }}</div>
                @endif

                @can('viewAny', App\Models\User::class)
                    <a href="{{ route('users.index') }}" class="fm-nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <i class="bi bi-people"></i>
                        <span>{{ __('Users') }}</span>
                    </a>
                @endcan

                @can('viewAny', App\Models\Role::class)
                    <a href="{{ route('roles.index') }}" class="fm-nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                        <i class="bi bi-shield-lock"></i>
                        <span>{{ __('Roles') }}</span>
                    </a>
                @endcan

                @if (auth()->user()->hasPermission('audit.view'))
                    <a href="{{ route('activity.index') }}" class="fm-nav-link {{ request()->routeIs('activity.*') ? 'active' : '' }}">
                        <i class="bi bi-clock-history"></i>
                        <span>{{ __('Activity log') }}</span>
                    </a>
                @endif

                @if (auth()->user()->hasPermission('trash.view'))
                    <a href="{{ route('trash.index') }}" class="fm-nav-link {{ request()->routeIs('trash.*') ? 'active' : '' }}">
                        <i class="bi bi-trash3"></i>
                        <span>{{ __('Trash') }}</span>
                    </a>
                @endif

                @if (auth()->user()->hasPermission('system.view'))
                    <a href="{{ route('system.index') }}" class="fm-nav-link {{ request()->routeIs('system.*') ? 'active' : '' }}">
                        <i class="bi bi-activity"></i>
                        <span>{{ __('System') }}</span>
                    </a>
                @endif
            </nav>

            <div class="fm-sidebar-footer">
                <div>FlowManager</div>
                <small>{{ __('Portfolio build v0.9') }}</small>
            </div>
        </aside>

        <main class="fm-main">
            <header class="fm-header">
                <div class="fm-header-left">
                    <button type="button" class="fm-sidebar-toggle" data-fm-sidebar-toggle aria-controls="fm-sidebar" aria-expanded="false" aria-label="{{ __('Open navigation') }}">
                        <i class="bi bi-list"></i>
                    </button>

                    <div>
                        <h1 class="fm-header-title">@yield('page-title', __('Dashboard'))</h1>

                        @hasSection('page-subtitle')
                            <div class="fm-header-subtitle">@yield('page-subtitle')</div>
                        @endif
                    </div>
                </div>

                <form method="GET" action="{{ route('search.index') }}" class="fm-header-search" role="search">
                    <i class="bi bi-search"></i>
                    <input
                        type="search"
                        name="q"
                        value="{{ request()->routeIs('search.*') ? request('q') : '' }}"
                        placeholder="{{ __('Search FlowManager...') }}"
                        aria-label="{{ __('Global search') }}"
                    >
                    <span class="fm-search-shortcut">/</span>
                </form>

                <div class="fm-header-actions">
                    <div class="dropdown">
                        <button class="fm-icon-button position-relative" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="{{ __('Notifications') }}">
                            <i class="bi bi-bell"></i>
                            @if (($unreadNotificationCount ?? 0) > 0)
                                <span class="fm-notification-badge">{{ min($unreadNotificationCount, 99) }}</span>
                            @endif
                        </button>

                        <div class="dropdown-menu dropdown-menu-end fm-notification-dropdown p-0">
                            <div class="fm-notification-dropdown-header">
                                <div>
                                    <div class="fw-semibold">{{ __('Notifications') }}</div>
                                    <small class="text-secondary">{{ trans_choice('ui.counts.unread_notifications', $unreadNotificationCount ?? 0, ['count' => $unreadNotificationCount ?? 0]) }}</small>
                                </div>
                                @if (($unreadNotificationCount ?? 0) > 0)
                                    <form method="POST" action="{{ route('notifications.read-all') }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-link text-decoration-none">{{ __('Read all') }}</button>
                                    </form>
                                @endif
                            </div>

                            <div class="fm-notification-dropdown-list">
                                @forelse (($headerNotifications ?? collect()) as $notification)
                                    @php($notificationData = $notification->data)
                                    <a href="{{ route('notifications.open', $notification->id) }}" class="fm-notification-dropdown-item {{ $notification->read_at ? '' : 'is-unread' }}">
                                        <span class="fm-notification-small-icon"><i class="bi {{ $notificationData['icon'] ?? 'bi-bell' }}"></i></span>
                                        <span class="min-w-0">
                                            <strong>{{ __($notificationData['title_key'] ?? 'Notification') }}</strong>
                                            <small>{{ __($notificationData['message_key'] ?? '', $notificationData['parameters'] ?? []) }}</small>
                                            <small class="text-secondary">{{ $notification->created_at->diffForHumans() }}</small>
                                        </span>
                                    </a>
                                @empty
                                    <div class="p-4 text-center text-secondary small">{{ __('No notifications yet.') }}</div>
                                @endforelse
                            </div>

                            <a href="{{ route('notifications.index') }}" class="fm-notification-dropdown-footer">
                                {{ __('View all notifications') }}
                                <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>

                    @include('partials.language-switcher')

                    <div class="dropdown">
                        <button class="fm-user-menu" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="fm-user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>

                            <span class="fm-user-info">
                                <strong>{{ auth()->user()->name }}</strong>
                                <small>{{ auth()->user()->roles->pluck('name')->map(fn ($name) => __($name))->implode(', ') }}</small>
                            </span>

                            <i class="bi bi-chevron-down"></i>
                        </button>

                        <ul class="dropdown-menu dropdown-menu-end fm-user-dropdown">
                            <li class="px-3 py-2">
                                <div class="fw-semibold">{{ auth()->user()->name }}</div>
                                <small class="text-secondary">{{ auth()->user()->email }}</small>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a href="{{ route('security.index') }}" class="dropdown-item">
                                    <i class="bi bi-shield-check me-2"></i>
                                    {{ __('Security') }}
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('notifications.index') }}" class="dropdown-item">
                                    <i class="bi bi-bell me-2"></i>
                                    {{ __('Notifications') }}
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="bi bi-box-arrow-right me-2"></i>
                                        {{ __('Sign out') }}
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>

            <section class="fm-content">
                @if (session('status'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('status') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('Close') }}"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('Close') }}"></button>
                    </div>
                @endif

                @yield('content')
            </section>
        </main>
    </div>
</body>
</html>
