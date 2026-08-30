<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') | {{ config('app.name') }}</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])
</head>
<body>
    <div class="fm-app">
        <div class="fm-sidebar-backdrop" data-fm-sidebar-backdrop></div>

        <aside class="fm-sidebar" data-fm-sidebar>
            <div class="fm-sidebar-header">
                <a href="{{ route('dashboard') }}" class="fm-sidebar-brand">
                    <span class="fm-sidebar-brand-mark">FM</span>
                    <span>FlowManager</span>
                </a>

                <button type="button" class="fm-sidebar-close" data-fm-sidebar-toggle aria-label="Close navigation">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <nav class="fm-sidebar-nav">
                <div class="fm-nav-section">Overview</div>

                <a href="{{ route('dashboard') }}" class="fm-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid"></i>
                    <span>Dashboard</span>
                </a>

                <div class="fm-nav-section">CRM</div>

                @can('viewAny', App\Models\Company::class)
                    <a href="{{ route('companies.index') }}" class="fm-nav-link {{ request()->routeIs('companies.*') ? 'active' : '' }}">
                        <i class="bi bi-buildings"></i>
                        <span>Companies</span>
                    </a>
                @endcan

                @can('viewAny', App\Models\Contact::class)
                    <a href="{{ route('contacts.index') }}" class="fm-nav-link {{ request()->routeIs('contacts.*') ? 'active' : '' }}">
                        <i class="bi bi-person-vcard"></i>
                        <span>Contacts</span>
                    </a>
                @endcan

                <div class="fm-nav-section">Operations</div>

                @can('viewAny', App\Models\Project::class)
                    <a href="{{ route('projects.index') }}" class="fm-nav-link {{ request()->routeIs('projects.*') ? 'active' : '' }}">
                        <i class="bi bi-kanban"></i>
                        <span>Projects</span>
                    </a>
                @endcan

                @can('viewAny', App\Models\Task::class)
                    <a href="{{ route('tasks.index') }}" class="fm-nav-link {{ request()->routeIs('tasks.*') ? 'active' : '' }}">
                        <i class="bi bi-check2-square"></i>
                        <span>Tasks</span>
                    </a>
                @endcan

                @can('viewAny', App\Models\Asset::class)
                    <a href="{{ route('assets.index') }}" class="fm-nav-link {{ request()->routeIs('assets.*') ? 'active' : '' }}">
                        <i class="bi bi-laptop"></i>
                        <span>Assets</span>
                    </a>
                @endcan

                <div class="fm-nav-section">Support</div>

                @can('viewAny', App\Models\Ticket::class)
                    <a href="{{ route('tickets.index') }}" class="fm-nav-link {{ request()->routeIs('tickets.*') ? 'active' : '' }}">
                        <i class="bi bi-ticket-perforated"></i>
                        <span>Tickets</span>
                    </a>
                @endcan

                @if (auth()->user()->can('viewAny', App\Models\User::class) || auth()->user()->can('viewAny', App\Models\Role::class))
                    <div class="fm-nav-section">Administration</div>
                @endif

                @can('viewAny', App\Models\User::class)
                    <a href="{{ route('users.index') }}" class="fm-nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <i class="bi bi-people"></i>
                        <span>Users</span>
                    </a>
                @endcan

                @can('viewAny', App\Models\Role::class)
                    <a href="{{ route('roles.index') }}" class="fm-nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                        <i class="bi bi-shield-lock"></i>
                        <span>Roles</span>
                    </a>
                @endcan
            </nav>

            <div class="fm-sidebar-footer">
                <div>FlowManager</div>
                <small>Portfolio build v0.2</small>
            </div>
        </aside>

        <main class="fm-main">
            <header class="fm-header">
                <div class="fm-header-left">
                    <button type="button" class="fm-sidebar-toggle" data-fm-sidebar-toggle aria-label="Open navigation">
                        <i class="bi bi-list"></i>
                    </button>

                    <div>
                        <h1 class="fm-header-title">@yield('page-title', 'Dashboard')</h1>

                        @hasSection('page-subtitle')
                            <div class="fm-header-subtitle">@yield('page-subtitle')</div>
                        @endif
                    </div>
                </div>

                <div class="dropdown">
                    <button class="fm-user-menu" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="fm-user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>

                        <span class="fm-user-info">
                            <strong>{{ auth()->user()->name }}</strong>
                            <small>{{ auth()->user()->roles->pluck('name')->implode(', ') }}</small>
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
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="bi bi-box-arrow-right me-2"></i>
                                    Sign out
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </header>

            <section class="fm-content">
                @if (session('status'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('status') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @yield('content')
            </section>
        </main>
    </div>
</body>
</html>
