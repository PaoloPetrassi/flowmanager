<?php $__env->startSection('title', __('System')); ?>
<?php $__env->startSection('page-title', __('System health')); ?>
<?php $__env->startSection('page-subtitle', __('Runtime status, queues, scheduler, storage and database backups')); ?>

<?php $__env->startSection('content'); ?>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div class="text-secondary small">
            <?php echo e(__('FlowManager :version', ['version' => $metrics['version']])); ?>

        </div>

        <a href="<?php echo e(route('system.jobs.index')); ?>" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-stack me-1"></i><?php echo e(__('Background jobs')); ?>

            <?php if(($metrics['queue_pending'] ?? 0) > 0): ?>
                <span class="badge text-bg-primary ms-1"><?php echo e($metrics['queue_pending']); ?></span>
            <?php endif; ?>
        </a>
    </div>

    <div class="row g-4 mb-4">
        <?php $__currentLoopData = $checks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $name => $check): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="col-12 col-sm-6 col-xl">
                <div class="card fm-card h-100">
                    <div class="card-body d-flex align-items-start gap-3">
                        <span class="fm-health-icon <?php echo e($check['ok'] ? 'is-ok' : 'is-error'); ?>">
                            <i class="bi <?php echo e($check['ok'] ? 'bi-check-lg' : 'bi-exclamation-triangle'); ?>"></i>
                        </span>
                        <div class="min-w-0">
                            <div class="text-secondary small text-uppercase"><?php echo e(__(ucfirst($name))); ?></div>
                            <div class="fw-bold"><?php echo e($check['ok'] ? __('OK') : __('Attention')); ?></div>
                            <div class="small text-secondary text-break"><?php echo e($check['message']); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <div class="row g-4">
        <div class="col-12 col-xl-5">
            <div class="card fm-card mb-4">
                <div class="card-header fm-card-header">
                    <div>
                        <h2 class="fm-card-title"><?php echo e(__('Runtime')); ?></h2>
                        <p class="fm-card-subtitle"><?php echo e(__('Current application environment')); ?></p>
                    </div>
                </div>
                <div class="card-body">
                    <?php $__currentLoopData = [
                        __('Version') => $metrics['version'],
                        __('PHP') => $metrics['php'],
                        __('Laravel') => $metrics['laravel'],
                        __('Environment') => $metrics['environment'],
                        __('Debug mode') => $metrics['debug'] ? __('ON') : __('OFF'),
                        __('Database latency') => $metrics['database_latency_ms'] !== null ? $metrics['database_latency_ms'].' ms' : '—',
                        __('Queue connection') => $metrics['queue_connection'],
                        __('Queued jobs') => $metrics['queue_pending'] ?? '—',
                        __('Tracked active jobs') => $metrics['tracked_jobs'] ?? '—',
                        __('Failed jobs') => $metrics['failed_jobs'] ?? '—',
                        __('Active sessions') => $metrics['sessions'] ?? '—',
                        __('XLSX support') => $metrics['zip_available'] ? __('Available') : __('ZIP extension missing'),
                    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="d-flex justify-content-between gap-3 py-2 border-bottom">
                            <span class="text-secondary"><?php echo e($label); ?></span>
                            <strong class="text-end"><?php echo e($value); ?></strong>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <div class="card fm-card mb-4">
                <div class="card-header fm-card-header">
                    <div>
                        <h2 class="fm-card-title"><?php echo e(__('Storage')); ?></h2>
                        <p class="fm-card-subtitle"><?php echo e(__('Local disk capacity used by the application')); ?></p>
                    </div>
                </div>
                <div class="card-body">
                    <?php if($metrics['disk_total'] && $metrics['disk_free'] !== null): ?>
                        <?php
                            $diskUsed = max(0, $metrics['disk_total'] - $metrics['disk_free']);
                            $diskPercent = $metrics['disk_total'] > 0
                                ? min(100, round(($diskUsed / $metrics['disk_total']) * 100, 1))
                                : 0;
                        ?>

                        <div class="d-flex justify-content-between mb-2">
                            <span><?php echo e(__('Used')); ?></span>
                            <strong><?php echo e($diskPercent); ?>%</strong>
                        </div>
                        <div class="progress mb-3" role="progressbar" aria-valuenow="<?php echo e($diskPercent); ?>" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar" style="width: <?php echo e($diskPercent); ?>%"></div>
                        </div>
                        <div class="small text-secondary">
                            <?php echo e(\App\Support\FileSize::format($diskUsed)); ?> /
                            <?php echo e(\App\Support\FileSize::format($metrics['disk_total'])); ?>

                        </div>
                    <?php else: ?>
                        <div class="text-secondary"><?php echo e(__('Disk capacity is not available on this filesystem.')); ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card fm-card">
                <div class="card-header fm-card-header">
                    <div>
                        <h2 class="fm-card-title"><?php echo e(__('Recent authentication activity')); ?></h2>
                        <p class="fm-card-subtitle"><?php echo e(__('Latest account access events')); ?></p>
                    </div>
                </div>
                <div class="list-group list-group-flush">
                    <?php $__empty_1 = true; $__currentLoopData = $loginActivities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="list-group-item py-3">
                            <div class="d-flex justify-content-between gap-2">
                                <strong><?php echo e($activity->user?->name ?: $activity->email); ?></strong>
                                <span class="badge text-bg-light"><?php echo e(__($activity->event)); ?></span>
                            </div>
                            <div class="small text-secondary">
                                <?php echo e($activity->created_at->format('d/m/Y H:i')); ?> · <?php echo e($activity->ip_address ?: '—'); ?>

                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="p-4 text-secondary"><?php echo e(__('No activity recorded.')); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-7">
            <div class="card fm-card">
                <div class="card-header fm-card-header">
                    <div>
                        <h2 class="fm-card-title"><?php echo e(__('Database backups')); ?></h2>
                        <p class="fm-card-subtitle"><?php echo e(__('Portable snapshots of FlowManager data and private attachments')); ?></p>
                    </div>

                    <?php if(auth()->user()->hasPermission('system.manage')): ?>
                        <form method="POST" action="<?php echo e(route('system.backups.store')); ?>">
                            <?php echo csrf_field(); ?>
                            <button class="btn btn-primary btn-sm" type="submit">
                                <i class="bi bi-database-add me-1"></i><?php echo e(__('Queue backup')); ?>

                            </button>
                        </form>
                    <?php endif; ?>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th><?php echo e(__('Backup')); ?></th>
                                    <th><?php echo e(__('Created')); ?></th>
                                    <th><?php echo e(__('Size')); ?></th>
                                    <th><?php echo e(__('Integrity')); ?></th>
                                    <th class="text-end"><?php echo e(__('Actions')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $backups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $backup): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td class="fw-semibold"><?php echo e($backup['name']); ?></td>
                                        <td><?php echo e(\Illuminate\Support\Carbon::createFromTimestamp($backup['modified_at'])->format('d/m/Y H:i')); ?></td>
                                        <td><?php echo e(\App\Support\FileSize::format($backup['size'])); ?></td>
                                        <td>
                                            <span class="badge <?php echo e($backup['valid'] ? 'text-bg-success' : 'text-bg-danger'); ?>">
                                                <?php echo e($backup['valid'] ? __('Valid') : __('Invalid')); ?>

                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-inline-flex gap-1">
                                                <a class="btn btn-sm btn-outline-primary" href="<?php echo e(route('system.backups.download', $backup['name'])); ?>">
                                                    <i class="bi bi-download"></i>
                                                </a>

                                                <?php if(auth()->user()->hasPermission('system.manage')): ?>
                                                    <form method="POST" action="<?php echo e(route('system.backups.destroy', $backup['name'])); ?>">
                                                        <?php echo csrf_field(); ?>
                                                        <?php echo method_field('DELETE'); ?>
                                                        <button
                                                            class="btn btn-sm btn-outline-danger"
                                                            type="submit"
                                                            data-confirm="<?php echo e(__('Delete this backup?')); ?>"
                                                        >
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-secondary py-5">
                                            <?php echo e(__('No backups have been created yet.')); ?>

                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer bg-transparent small text-secondary">
                    <?php echo e(__('Web backups are queued. Restore remains CLI-only for safety: php artisan flowmanager:restore backups/file.json')); ?>

                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Projects\flowmanager\resources\views/system/index.blade.php ENDPATH**/ ?>