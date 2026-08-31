<?php $__env->startSection('title', __('Dashboard')); ?>
<?php $__env->startSection('page-title', __('Dashboard')); ?>
<?php $__env->startSection('page-subtitle', __('Operational overview and work that needs your attention')); ?>

<?php $__env->startSection('content'); ?>
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <div class="fw-semibold"><?php echo e(__('Welcome back, :name.', ['name' => $currentUser->name])); ?></div>
            <div class="text-secondary small">
                <?php if($overdueTaskCount > 0): ?>
                    <?php echo e(trans_choice('ui.overdue_tasks', $overdueTaskCount, ['count' => $overdueTaskCount])); ?>

                <?php else: ?>
                    <?php echo e(__('No overdue tasks are currently assigned to you.')); ?>

                <?php endif; ?>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\Task::class)): ?>
                <a href="<?php echo e(route('tasks.create', ['assign_to_me' => 1])); ?>" class="btn btn-outline-primary">
                    <i class="bi bi-check2-square me-1"></i>
                    <?php echo e(__('New task')); ?>

                </a>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\Ticket::class)): ?>
                <a href="<?php echo e(route('tickets.create', ['assign_to_me' => 1])); ?>" class="btn btn-outline-primary">
                    <i class="bi bi-ticket-perforated me-1"></i>
                    <?php echo e(__('New ticket')); ?>

                </a>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\Project::class)): ?>
                <a href="<?php echo e(route('projects.create', ['manage_by_me' => 1])); ?>" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i>
                    <?php echo e(__('New project')); ?>

                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if(in_array('stats', $dashboardWidgets, true)): ?>
    <div class="row g-4 mb-4">
        <?php $__currentLoopData = $stats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="col-12 col-sm-6 col-xl-4 col-xxl-2">
                <?php if($stat['url']): ?>
                    <a href="<?php echo e($stat['url']); ?>" class="card fm-card fm-stat-card fm-stat-link h-100 text-reset">
                <?php else: ?>
                    <div class="card fm-card fm-stat-card h-100">
                <?php endif; ?>

                    <div class="card-body">
                        <div class="fm-stat-header">
                            <div class="fm-stat-icon">
                                <i class="bi <?php echo e($stat['icon']); ?>"></i>
                            </div>
                            <span class="fm-stat-label"><?php echo e($stat['label']); ?></span>
                        </div>
                        <div class="fm-stat-value"><?php echo e($stat['value']); ?></div>
                    </div>

                <?php if($stat['url']): ?>
                    </a>
                <?php else: ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <?php endif; ?>

    <?php if(in_array('my_work', $dashboardWidgets, true)): ?>
    <div class="row g-4 mb-4">
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('viewAny', App\Models\Task::class)): ?>
            <div class="col-12 col-xl-6">
                <div class="card fm-card h-100">
                    <div class="card-header fm-card-header">
                        <div>
                            <h2 class="fm-card-title"><?php echo e(__('My open tasks')); ?></h2>
                            <p class="fm-card-subtitle"><?php echo e(__('Nearest deadlines assigned to you')); ?></p>
                        </div>
                        <a href="<?php echo e(route('tasks.index', ['assigned_to' => $currentUser->id])); ?>" class="btn btn-sm btn-outline-secondary">
                            <?php echo e(__('View all')); ?>

                        </a>
                    </div>

                    <div class="list-group list-group-flush">
                        <?php $__empty_1 = true; $__currentLoopData = $myTasks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $isOverdue = $task->due_date && $task->due_date->isBefore(today());
                            ?>

                            <div class="list-group-item fm-work-item">
                                <div class="flex-grow-1 min-w-0">
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <a href="<?php echo e(route('tasks.show', $task)); ?>" class="fw-semibold text-dark">
                                            <?php echo e($task->title); ?>

                                        </a>
                                        <?php if($isOverdue): ?>
                                            <span class="badge text-bg-danger"><?php echo e(__('Overdue')); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="small text-secondary mt-1">
                                        <?php echo e($task->project->code); ?> · <?php echo e($task->project->company->name); ?>

                                        <?php if($task->due_date): ?>
                                            · <?php echo e(__('Due :date', ['date' => $task->due_date->format('d/m/Y')])); ?>

                                        <?php endif; ?>
                                    </div>
                                </div>

                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $task)): ?>
                                    <form method="POST" action="<?php echo e(route('tasks.complete', $task)); ?>">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('PATCH'); ?>
                                        <button type="submit" class="btn btn-sm btn-outline-success" title="<?php echo e(__('Mark completed')); ?>">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="p-4 text-center text-secondary">
                                <?php echo e(__('No open tasks are assigned to you.')); ?>

                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('viewAny', App\Models\Ticket::class)): ?>
            <div class="col-12 col-xl-6">
                <div class="card fm-card h-100">
                    <div class="card-header fm-card-header">
                        <div>
                            <h2 class="fm-card-title"><?php echo e(__('My open tickets')); ?></h2>
                            <p class="fm-card-subtitle"><?php echo e(__('Support requests currently assigned to you')); ?></p>
                        </div>
                        <a href="<?php echo e(route('tickets.index', ['assigned_to' => $currentUser->id])); ?>" class="btn btn-sm btn-outline-secondary">
                            <?php echo e(__('View all')); ?>

                        </a>
                    </div>

                    <div class="list-group list-group-flush">
                        <?php $__empty_1 = true; $__currentLoopData = $myTickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="list-group-item fm-work-item">
                                <div class="flex-grow-1 min-w-0">
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <a href="<?php echo e(route('tickets.show', $ticket)); ?>" class="fw-semibold text-dark">
                                            <?php echo e($ticket->subject); ?>

                                        </a>
                                        <?php if($ticket->priority->value === 'urgent'): ?>
                                            <span class="badge text-bg-danger"><?php echo e(__('Urgent')); ?></span>
                                        <?php elseif($ticket->priority->value === 'high'): ?>
                                            <span class="badge text-bg-warning"><?php echo e(__('High')); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="small text-secondary mt-1">
                                        <?php echo e($ticket->reference); ?>

                                        <?php if($ticket->company): ?>
                                            · <?php echo e($ticket->company->name); ?>

                                        <?php endif; ?>
                                        · <?php echo e($ticket->status->label()); ?>

                                    </div>
                                </div>

                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $ticket)): ?>
                                    <form method="POST" action="<?php echo e(route('tickets.resolve', $ticket)); ?>">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('PATCH'); ?>
                                        <button type="submit" class="btn btn-sm btn-outline-success" title="<?php echo e(__('Mark resolved')); ?>">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="p-4 text-center text-secondary">
                                <?php echo e(__('No open tickets are assigned to you.')); ?>

                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="row g-4">
        <?php if(in_array('projects', $dashboardWidgets, true)): ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('viewAny', App\Models\Project::class)): ?>
            <div class="col-12 col-xl-7">
                <div class="card fm-card h-100">
                    <div class="card-header fm-card-header">
                        <div>
                            <h2 class="fm-card-title"><?php echo e(__('Projects I manage')); ?></h2>
                            <p class="fm-card-subtitle"><?php echo e(__('Active delivery under your responsibility')); ?></p>
                        </div>
                        <a href="<?php echo e(route('projects.index', ['manager_id' => $currentUser->id])); ?>" class="btn btn-sm btn-outline-secondary">
                            <?php echo e(__('View all')); ?>

                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle mb-0 fm-table">
                            <thead>
                                <tr>
                                    <th><?php echo e(__('Project')); ?></th>
                                    <th><?php echo e(__('Company')); ?></th>
                                    <th><?php echo e(__('Status')); ?></th>
                                    <th><?php echo e(__('Open tasks')); ?></th>
                                    <th><?php echo e(__('Due')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $managedProjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td>
                                            <a href="<?php echo e(route('projects.show', $project)); ?>" class="fw-semibold text-dark">
                                                <?php echo e($project->name); ?>

                                            </a>
                                            <div class="small text-secondary"><?php echo e($project->code); ?></div>
                                        </td>
                                        <td><?php echo e($project->company->name); ?></td>
                                        <td><?php echo e($project->status->label()); ?></td>
                                        <td><?php echo e($project->open_tasks_count); ?> / <?php echo e($project->tasks_count); ?></td>
                                        <td><?php echo e($project->due_date?->format('d/m/Y') ?: '—'); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-secondary">
                                            <?php echo e(__('No active projects are currently managed by you.')); ?>

                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <?php endif; ?>

        <?php if(in_array('access', $dashboardWidgets, true)): ?>
        <div class="col-12 col-xl-5">
            <div class="card fm-card mb-4">
                <div class="card-body">
                    <h2 class="fm-card-title mb-3"><?php echo e(__('Your access')); ?></h2>

                    <div class="fm-access-user">
                        <div class="fm-access-avatar">
                            <?php echo e(strtoupper(substr($currentUser->name, 0, 1))); ?>

                        </div>
                        <div>
                            <div class="fw-semibold"><?php echo e($currentUser->name); ?></div>
                            <small class="text-secondary"><?php echo e($currentUser->email); ?></small>
                        </div>
                    </div>

                    <hr>

                    <div class="fm-access-row">
                        <span><?php echo e(__('Roles')); ?></span>
                        <strong><?php echo e($currentUser->roles->count()); ?></strong>
                    </div>
                    <div class="fm-access-row">
                        <span><?php echo e(__('Permissions')); ?></span>
                        <strong><?php echo e($permissionCount); ?></strong>
                    </div>

                    <div class="mt-3">
                        <?php $__currentLoopData = $currentUser->roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <span class="badge text-bg-primary me-1"><?php echo e(__($role->name)); ?></span>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>

            <?php if($latestUsers->isNotEmpty()): ?>
                <div class="card fm-card">
                    <div class="card-header fm-card-header">
                        <div>
                            <h2 class="fm-card-title"><?php echo e(__('Recent users')); ?></h2>
                            <p class="fm-card-subtitle"><?php echo e(__('Latest application accounts')); ?></p>
                        </div>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('viewAny', App\Models\User::class)): ?>
                            <a href="<?php echo e(route('users.index')); ?>" class="btn btn-sm btn-outline-secondary"><?php echo e(__('View all')); ?></a>
                        <?php endif; ?>
                    </div>

                    <div class="list-group list-group-flush">
                        <?php $__currentLoopData = $latestUsers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <a href="<?php echo e(route('users.show', $user)); ?>" class="list-group-item list-group-item-action fm-user-list-item">
                                <div class="fm-small-avatar"><?php echo e(strtoupper(substr($user->name, 0, 1))); ?></div>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold"><?php echo e($user->name); ?></div>
                                    <small class="text-secondary"><?php echo e($user->roles->pluck('name')->map(fn ($name) => __($name))->implode(', ') ?: __('No role')); ?></small>
                                </div>
                                <i class="bi bi-chevron-right text-secondary"></i>
                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>


    <?php if(in_array('charts', $dashboardWidgets, true)): ?>
    <div class="row g-4 mt-1">
        <?php if(!empty($trendSeries['items'])): ?>
            <div class="col-12 col-xxl-6">
                <div class="card fm-card h-100">
                    <div class="card-header fm-card-header">
                        <div>
                            <h2 class="fm-card-title"><?php echo e(__('Six-month throughput')); ?></h2>
                            <p class="fm-card-subtitle"><?php echo e(__('Completed tasks and resolved tickets by month')); ?></p>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="fm-chart-legend">
                            <span><i class="fm-legend-dot fm-legend-primary"></i><?php echo e(__('Tasks completed')); ?></span>
                            <span><i class="fm-legend-dot fm-legend-success"></i><?php echo e(__('Tickets resolved')); ?></span>
                        </div>
                        <div class="fm-column-chart" role="img" aria-label="<?php echo e(__('Six-month throughput')); ?>">
                            <?php $__currentLoopData = $trendSeries['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $point): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="fm-column-group">
                                    <div class="fm-column-pair">
                                        <div class="fm-column fm-column-primary" style="height: <?php echo e(max(4, round(($point['tasks'] / $trendSeries['max']) * 150))); ?>px" title="<?php echo e(__('Tasks completed')); ?>: <?php echo e($point['tasks']); ?>">
                                            <span><?php echo e($point['tasks']); ?></span>
                                        </div>
                                        <div class="fm-column fm-column-success" style="height: <?php echo e(max(4, round(($point['tickets'] / $trendSeries['max']) * 150))); ?>px" title="<?php echo e(__('Tickets resolved')); ?>: <?php echo e($point['tickets']); ?>">
                                            <span><?php echo e($point['tickets']); ?></span>
                                        </div>
                                    </div>
                                    <small><?php echo e($point['label']); ?></small>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if(!empty($taskStatusChart)): ?>
            <div class="col-12 col-lg-6 col-xxl-3">
                <div class="card fm-card h-100">
                    <div class="card-header fm-card-header">
                        <div>
                            <h2 class="fm-card-title"><?php echo e(__('Tasks by status')); ?></h2>
                            <p class="fm-card-subtitle"><?php echo e(__('Current task distribution')); ?></p>
                        </div>
                    </div>
                    <div class="card-body fm-bar-chart">
                        <?php ($taskMax = max(1, collect($taskStatusChart)->max('value'))); ?>
                        <?php $__currentLoopData = $taskStatusChart; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="fm-bar-row">
                                <div class="fm-bar-label"><span><?php echo e($item['label']); ?></span><strong><?php echo e($item['value']); ?></strong></div>
                                <div class="fm-bar-track"><span style="width: <?php echo e(round(($item['value'] / $taskMax) * 100)); ?>%"></span></div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if(!empty($ticketPriorityChart)): ?>
            <div class="col-12 col-lg-6 col-xxl-3">
                <div class="card fm-card h-100">
                    <div class="card-header fm-card-header">
                        <div>
                            <h2 class="fm-card-title"><?php echo e(__('Open tickets by priority')); ?></h2>
                            <p class="fm-card-subtitle"><?php echo e(__('Current support pressure')); ?></p>
                        </div>
                    </div>
                    <div class="card-body fm-bar-chart">
                        <?php ($ticketMax = max(1, collect($ticketPriorityChart)->max('value'))); ?>
                        <?php $__currentLoopData = $ticketPriorityChart; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="fm-bar-row">
                                <div class="fm-bar-label"><span><?php echo e($item['label']); ?></span><strong><?php echo e($item['value']); ?></strong></div>
                                <div class="fm-bar-track"><span style="width: <?php echo e(round(($item['value'] / $ticketMax) * 100)); ?>%"></span></div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php if($teamWorkload->isNotEmpty()): ?>
        <div class="card fm-card mt-4">
            <div class="card-header fm-card-header">
                <div>
                    <h2 class="fm-card-title"><?php echo e(__('Team workload')); ?></h2>
                    <p class="fm-card-subtitle"><?php echo e(__('Open tasks and tickets currently assigned')); ?></p>
                </div>
                <?php if(auth()->user()->hasPermission('reports.view')): ?>
                    <a href="<?php echo e(route('reports.index')); ?>" class="btn btn-sm btn-outline-secondary"><?php echo e(__('Open reports')); ?></a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php ($workloadMax = max(1, $teamWorkload->max('workload_total'))); ?>
                <div class="fm-workload-grid">
                    <?php $__currentLoopData = $teamWorkload; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="fm-workload-item">
                            <div class="d-flex justify-content-between gap-3 mb-2">
                                <strong><?php echo e($member->name); ?></strong>
                                <span class="text-secondary small"><?php echo e($member->workload_total); ?></span>
                            </div>
                            <div class="fm-bar-track"><span style="width: <?php echo e(round(($member->workload_total / $workloadMax) * 100)); ?>%"></span></div>
                            <div class="small text-secondary mt-2">
                                <?php echo e(trans_choice('ui.counts.tasks', $member->open_tasks_count, ['count' => $member->open_tasks_count])); ?> ·
                                <?php echo e(trans_choice('ui.counts.tickets', $member->open_tickets_count, ['count' => $member->open_tickets_count])); ?>

                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Projects\flowmanager\resources\views/dashboard/index.blade.php ENDPATH**/ ?>