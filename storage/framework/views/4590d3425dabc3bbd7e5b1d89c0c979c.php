<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo $__env->yieldContent('title', __('Dashboard')); ?> | <?php echo e(config('app.name')); ?></title>

    <?php echo app('Illuminate\Foundation\Vite')([
        'resources/css/app.css',
        'resources/js/app.js',
    ]); ?>
</head>
<body>
    <div class="fm-app">
        <div class="fm-sidebar-backdrop" data-fm-sidebar-backdrop></div>

        <aside id="fm-sidebar" class="fm-sidebar" data-fm-sidebar>
            <div class="fm-sidebar-header">
                <a href="<?php echo e(route('dashboard')); ?>" class="fm-sidebar-brand">
                    <span class="fm-sidebar-brand-mark">FM</span>
                    <span>FlowManager</span>
                </a>

                <button type="button" class="fm-sidebar-close" data-fm-sidebar-toggle aria-controls="fm-sidebar" aria-expanded="false" aria-label="<?php echo e(__('Close navigation')); ?>">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <nav class="fm-sidebar-nav">
                <div class="fm-nav-section"><?php echo e(__('Overview')); ?></div>

                <a href="<?php echo e(route('dashboard')); ?>" class="fm-nav-link <?php echo e(request()->routeIs('dashboard') ? 'active' : ''); ?>">
                    <i class="bi bi-grid"></i>
                    <span><?php echo e(__('Dashboard')); ?></span>
                </a>

                <a href="<?php echo e(route('search.index')); ?>" class="fm-nav-link <?php echo e(request()->routeIs('search.*') ? 'active' : ''); ?>">
                    <i class="bi bi-search"></i>
                    <span><?php echo e(__('Global search')); ?></span>
                </a>

                <div class="fm-nav-section"><?php echo e(__('CRM')); ?></div>

                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('viewAny', App\Models\Company::class)): ?>
                    <a href="<?php echo e(route('companies.index')); ?>" class="fm-nav-link <?php echo e(request()->routeIs('companies.*') ? 'active' : ''); ?>">
                        <i class="bi bi-buildings"></i>
                        <span><?php echo e(__('Companies')); ?></span>
                    </a>
                <?php endif; ?>

                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('viewAny', App\Models\Contact::class)): ?>
                    <a href="<?php echo e(route('contacts.index')); ?>" class="fm-nav-link <?php echo e(request()->routeIs('contacts.*') ? 'active' : ''); ?>">
                        <i class="bi bi-person-vcard"></i>
                        <span><?php echo e(__('Contacts')); ?></span>
                    </a>
                <?php endif; ?>

                <div class="fm-nav-section"><?php echo e(__('Operations')); ?></div>

                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('viewAny', App\Models\Project::class)): ?>
                    <a href="<?php echo e(route('projects.index')); ?>" class="fm-nav-link <?php echo e(request()->routeIs('projects.*') ? 'active' : ''); ?>">
                        <i class="bi bi-kanban"></i>
                        <span><?php echo e(__('Projects')); ?></span>
                    </a>
                <?php endif; ?>

                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('viewAny', App\Models\Task::class)): ?>
                    <a href="<?php echo e(route('tasks.index')); ?>" class="fm-nav-link <?php echo e(request()->routeIs('tasks.*') ? 'active' : ''); ?>">
                        <i class="bi bi-check2-square"></i>
                        <span><?php echo e(__('Tasks')); ?></span>
                        <?php if(($sidebarWorkCounts['tasks'] ?? 0) > 0): ?>
                            <span class="fm-nav-counter"><?php echo e($sidebarWorkCounts['tasks']); ?></span>
                        <?php endif; ?>
                    </a>
                <?php endif; ?>

                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('viewAny', App\Models\Asset::class)): ?>
                    <a href="<?php echo e(route('assets.index')); ?>" class="fm-nav-link <?php echo e(request()->routeIs('assets.*') ? 'active' : ''); ?>">
                        <i class="bi bi-laptop"></i>
                        <span><?php echo e(__('Assets')); ?></span>
                    </a>
                <?php endif; ?>

                <div class="fm-nav-section"><?php echo e(__('Support')); ?></div>

                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('viewAny', App\Models\Ticket::class)): ?>
                    <a href="<?php echo e(route('tickets.index')); ?>" class="fm-nav-link <?php echo e(request()->routeIs('tickets.*') ? 'active' : ''); ?>">
                        <i class="bi bi-ticket-perforated"></i>
                        <span><?php echo e(__('Tickets')); ?></span>
                        <?php if(($sidebarWorkCounts['tickets'] ?? 0) > 0): ?>
                            <span class="fm-nav-counter"><?php echo e($sidebarWorkCounts['tickets']); ?></span>
                        <?php endif; ?>
                    </a>
                <?php endif; ?>

                <div class="fm-nav-section"><?php echo e(__('Planning')); ?></div>

                <?php if(
                    auth()->user()->can('viewAny', App\Models\Project::class)
                    || auth()->user()->can('viewAny', App\Models\Task::class)
                    || auth()->user()->can('viewAny', App\Models\Asset::class)
                ): ?>
                    <a href="<?php echo e(route('calendar.index')); ?>" class="fm-nav-link <?php echo e(request()->routeIs('calendar.*') ? 'active' : ''); ?>">
                        <i class="bi bi-calendar3"></i>
                        <span><?php echo e(__('Calendar')); ?></span>
                    </a>
                <?php endif; ?>

                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('viewAny', App\Models\Project::class)): ?>
                    <a href="<?php echo e(route('planning.gantt')); ?>" class="fm-nav-link <?php echo e(request()->routeIs('planning.gantt') ? 'active' : ''); ?>">
                        <i class="bi bi-bar-chart-steps"></i>
                        <span><?php echo e(__('Gantt')); ?></span>
                    </a>
                <?php endif; ?>

                <?php if(auth()->user()->hasPermission('workload.view')): ?>
                    <a href="<?php echo e(route('planning.workload')); ?>" class="fm-nav-link <?php echo e(request()->routeIs('planning.workload') ? 'active' : ''); ?>">
                        <i class="bi bi-people-fill"></i>
                        <span><?php echo e(__('Workload')); ?></span>
                    </a>
                <?php endif; ?>

                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\Project::class)): ?>
                    <a href="<?php echo e(route('project-templates.index')); ?>" class="fm-nav-link <?php echo e(request()->routeIs('project-templates.*') ? 'active' : ''); ?>">
                        <i class="bi bi-copy"></i>
                        <span><?php echo e(__('Project templates')); ?></span>
                    </a>
                <?php endif; ?>

                <?php if(
                    auth()->user()->can('viewAny', App\Models\Task::class)
                    || auth()->user()->can('viewAny', App\Models\Ticket::class)
                ): ?>
                    <a href="<?php echo e(route('boards.index')); ?>" class="fm-nav-link <?php echo e(request()->routeIs('boards.*') ? 'active' : ''); ?>">
                        <i class="bi bi-columns-gap"></i>
                        <span><?php echo e(__('Kanban')); ?></span>
                    </a>
                <?php endif; ?>

                <?php if(auth()->user()->hasPermission('reports.view')): ?>
                    <a href="<?php echo e(route('reports.index')); ?>" class="fm-nav-link <?php echo e(request()->routeIs('reports.*') ? 'active' : ''); ?>">
                        <i class="bi bi-bar-chart-line"></i>
                        <span><?php echo e(__('Reports')); ?></span>
                    </a>
                <?php endif; ?>

                <?php if(auth()->user()->hasPermission('automations.view')): ?>
                    <a href="<?php echo e(route('automations.index')); ?>" class="fm-nav-link <?php echo e(request()->routeIs('automations.*') ? 'active' : ''); ?>">
                        <i class="bi bi-lightning-charge"></i>
                        <span><?php echo e(__('Automations')); ?></span>
                    </a>
                <?php endif; ?>

                <?php if(
                    auth()->user()->can('viewAny', App\Models\User::class)
                    || auth()->user()->can('viewAny', App\Models\Role::class)
                    || auth()->user()->hasPermission('audit.view')
                    || auth()->user()->hasPermission('trash.view')
                    || auth()->user()->hasPermission('system.view')
                ): ?>
                    <div class="fm-nav-section"><?php echo e(__('Administration')); ?></div>
                <?php endif; ?>

                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('viewAny', App\Models\User::class)): ?>
                    <a href="<?php echo e(route('users.index')); ?>" class="fm-nav-link <?php echo e(request()->routeIs('users.*') ? 'active' : ''); ?>">
                        <i class="bi bi-people"></i>
                        <span><?php echo e(__('Users')); ?></span>
                    </a>
                <?php endif; ?>

                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('viewAny', App\Models\Role::class)): ?>
                    <a href="<?php echo e(route('roles.index')); ?>" class="fm-nav-link <?php echo e(request()->routeIs('roles.*') ? 'active' : ''); ?>">
                        <i class="bi bi-shield-lock"></i>
                        <span><?php echo e(__('Roles')); ?></span>
                    </a>
                <?php endif; ?>

                <?php if(auth()->user()->hasPermission('audit.view')): ?>
                    <a href="<?php echo e(route('activity.index')); ?>" class="fm-nav-link <?php echo e(request()->routeIs('activity.*') ? 'active' : ''); ?>">
                        <i class="bi bi-clock-history"></i>
                        <span><?php echo e(__('Activity log')); ?></span>
                    </a>
                <?php endif; ?>

                <?php if(auth()->user()->hasPermission('trash.view')): ?>
                    <a href="<?php echo e(route('trash.index')); ?>" class="fm-nav-link <?php echo e(request()->routeIs('trash.*') ? 'active' : ''); ?>">
                        <i class="bi bi-trash3"></i>
                        <span><?php echo e(__('Trash')); ?></span>
                    </a>
                <?php endif; ?>

                <?php if(auth()->user()->hasPermission('system.view')): ?>
                    <a href="<?php echo e(route('system.index')); ?>" class="fm-nav-link <?php echo e(request()->routeIs('system.*') ? 'active' : ''); ?>">
                        <i class="bi bi-activity"></i>
                        <span><?php echo e(__('System')); ?></span>
                    </a>
                <?php endif; ?>
            </nav>

            <div class="fm-sidebar-footer">
                <div>FlowManager</div>
                <small><?php echo e(__('Portfolio build v0.9')); ?></small>
            </div>
        </aside>

        <main class="fm-main">
            <header class="fm-header">
                <div class="fm-header-left">
                    <button type="button" class="fm-sidebar-toggle" data-fm-sidebar-toggle aria-controls="fm-sidebar" aria-expanded="false" aria-label="<?php echo e(__('Open navigation')); ?>">
                        <i class="bi bi-list"></i>
                    </button>

                    <div>
                        <h1 class="fm-header-title"><?php echo $__env->yieldContent('page-title', __('Dashboard')); ?></h1>

                        <?php if (! empty(trim($__env->yieldContent('page-subtitle')))): ?>
                            <div class="fm-header-subtitle"><?php echo $__env->yieldContent('page-subtitle'); ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <form method="GET" action="<?php echo e(route('search.index')); ?>" class="fm-header-search" role="search">
                    <i class="bi bi-search"></i>
                    <input
                        type="search"
                        name="q"
                        value="<?php echo e(request()->routeIs('search.*') ? request('q') : ''); ?>"
                        placeholder="<?php echo e(__('Search FlowManager...')); ?>"
                        aria-label="<?php echo e(__('Global search')); ?>"
                    >
                    <span class="fm-search-shortcut">/</span>
                </form>

                <div class="fm-header-actions">
                    <div class="dropdown">
                        <button class="fm-icon-button position-relative" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="<?php echo e(__('Notifications')); ?>">
                            <i class="bi bi-bell"></i>
                            <?php if(($unreadNotificationCount ?? 0) > 0): ?>
                                <span class="fm-notification-badge"><?php echo e(min($unreadNotificationCount, 99)); ?></span>
                            <?php endif; ?>
                        </button>

                        <div class="dropdown-menu dropdown-menu-end fm-notification-dropdown p-0">
                            <div class="fm-notification-dropdown-header">
                                <div>
                                    <div class="fw-semibold"><?php echo e(__('Notifications')); ?></div>
                                    <small class="text-secondary"><?php echo e(trans_choice('ui.counts.unread_notifications', $unreadNotificationCount ?? 0, ['count' => $unreadNotificationCount ?? 0])); ?></small>
                                </div>
                                <?php if(($unreadNotificationCount ?? 0) > 0): ?>
                                    <form method="POST" action="<?php echo e(route('notifications.read-all')); ?>">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('PATCH'); ?>
                                        <button type="submit" class="btn btn-sm btn-link text-decoration-none"><?php echo e(__('Read all')); ?></button>
                                    </form>
                                <?php endif; ?>
                            </div>

                            <div class="fm-notification-dropdown-list">
                                <?php $__empty_1 = true; $__currentLoopData = ($headerNotifications ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <?php ($notificationData = $notification->data); ?>
                                    <a href="<?php echo e(route('notifications.open', $notification->id)); ?>" class="fm-notification-dropdown-item <?php echo e($notification->read_at ? '' : 'is-unread'); ?>">
                                        <span class="fm-notification-small-icon"><i class="bi <?php echo e($notificationData['icon'] ?? 'bi-bell'); ?>"></i></span>
                                        <span class="min-w-0">
                                            <strong><?php echo e(__($notificationData['title_key'] ?? 'Notification')); ?></strong>
                                            <small><?php echo e(__($notificationData['message_key'] ?? '', $notificationData['parameters'] ?? [])); ?></small>
                                            <small class="text-secondary"><?php echo e($notification->created_at->diffForHumans()); ?></small>
                                        </span>
                                    </a>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <div class="p-4 text-center text-secondary small"><?php echo e(__('No notifications yet.')); ?></div>
                                <?php endif; ?>
                            </div>

                            <a href="<?php echo e(route('notifications.index')); ?>" class="fm-notification-dropdown-footer">
                                <?php echo e(__('View all notifications')); ?>

                                <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>

                    <?php echo $__env->make('partials.language-switcher', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                    <div class="dropdown">
                        <button class="fm-user-menu" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="fm-user-avatar"><?php echo e(strtoupper(substr(auth()->user()->name, 0, 1))); ?></span>

                            <span class="fm-user-info">
                                <strong><?php echo e(auth()->user()->name); ?></strong>
                                <small><?php echo e(auth()->user()->roles->pluck('name')->map(fn ($name) => __($name))->implode(', ')); ?></small>
                            </span>

                            <i class="bi bi-chevron-down"></i>
                        </button>

                        <ul class="dropdown-menu dropdown-menu-end fm-user-dropdown">
                            <li class="px-3 py-2">
                                <div class="fw-semibold"><?php echo e(auth()->user()->name); ?></div>
                                <small class="text-secondary"><?php echo e(auth()->user()->email); ?></small>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a href="<?php echo e(route('security.index')); ?>" class="dropdown-item">
                                    <i class="bi bi-shield-check me-2"></i>
                                    <?php echo e(__('Security')); ?>

                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(route('notifications.index')); ?>" class="dropdown-item">
                                    <i class="bi bi-bell me-2"></i>
                                    <?php echo e(__('Notifications')); ?>

                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="<?php echo e(route('logout')); ?>">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="dropdown-item">
                                        <i class="bi bi-box-arrow-right me-2"></i>
                                        <?php echo e(__('Sign out')); ?>

                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>

            <section class="fm-content">
                <?php if(session('status')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo e(session('status')); ?>

                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="<?php echo e(__('Close')); ?>"></button>
                    </div>
                <?php endif; ?>

                <?php if(session('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo e(session('error')); ?>

                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="<?php echo e(__('Close')); ?>"></button>
                    </div>
                <?php endif; ?>

                <?php echo $__env->yieldContent('content'); ?>
            </section>
        </main>
    </div>
</body>
</html>
<?php /**PATH C:\Projects\flowmanager\resources\views/layouts/app.blade.php ENDPATH**/ ?>