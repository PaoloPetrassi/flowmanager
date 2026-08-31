<?php $__env->startSection('title', __('Activity log')); ?>
<?php $__env->startSection('page-title', __('Activity log')); ?>
<?php $__env->startSection('page-subtitle', __('Track who changed what across FlowManager')); ?>

<?php $__env->startSection('content'); ?>
    <div class="card fm-card mb-4">
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('activity.index')); ?>" class="row g-3 align-items-end">
                <div class="col-12 col-xl-3">
                    <label class="form-label"><?php echo e(__('Search')); ?></label>
                    <input type="search" name="search" value="<?php echo e($filters['search']); ?>" class="form-control" placeholder="<?php echo e(__('Record, user or IP address')); ?>">
                </div>
                <div class="col-6 col-md-3 col-xl-2">
                    <label class="form-label"><?php echo e(__('Event')); ?></label>
                    <select name="event" class="form-select">
                        <option value=""><?php echo e(__('All events')); ?></option>
                        <?php $__currentLoopData = $events; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($event); ?>" <?php if($filters['event'] === $event): echo 'selected'; endif; ?>><?php echo e(__(str_replace('_', ' ', $event))); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-6 col-md-3 col-xl-2">
                    <label class="form-label"><?php echo e(__('Resource')); ?></label>
                    <select name="resource" class="form-select">
                        <option value=""><?php echo e(__('All resources')); ?></option>
                        <?php $__currentLoopData = $resources; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $resource): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($resource); ?>" <?php if($filters['resource'] === $resource): echo 'selected'; endif; ?>><?php echo e(__(ucfirst($resource))); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-12 col-md-6 col-xl-2">
                    <label class="form-label"><?php echo e(__('User')); ?></label>
                    <select name="user_id" class="form-select">
                        <option value=""><?php echo e(__('All users')); ?></option>
                        <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($user->id); ?>" <?php if((string) $filters['user_id'] === (string) $user->id): echo 'selected'; endif; ?>><?php echo e($user->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-6 col-md-3 col-xl-1">
                    <label class="form-label"><?php echo e(__('From')); ?></label>
                    <input type="date" name="date_from" value="<?php echo e($filters['date_from']); ?>" class="form-control">
                </div>
                <div class="col-6 col-md-3 col-xl-1">
                    <label class="form-label"><?php echo e(__('To')); ?></label>
                    <input type="date" name="date_to" value="<?php echo e($filters['date_to']); ?>" class="form-control">
                </div>
                <div class="col-12 col-xl-1 d-grid">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-funnel"></i></button>
                </div>
            </form>
        </div>
    </div>

    <div class="card fm-card">
        <div class="card-header fm-card-header">
            <div>
                <h2 class="fm-card-title"><?php echo e(__('Recorded activity')); ?></h2>
                <p class="fm-card-subtitle"><?php echo e(trans_choice('ui.counts.audit_events', $logs->total(), ['count' => $logs->total()])); ?></p>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0 fm-table">
                <thead>
                    <tr>
                        <th><?php echo e(__('When')); ?></th>
                        <th><?php echo e(__('User')); ?></th>
                        <th><?php echo e(__('Resource')); ?></th>
                        <th><?php echo e(__('Event')); ?></th>
                        <th><?php echo e(__('Changes')); ?></th>
                        <th><?php echo e(__('IP address')); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="text-nowrap">
                                <div class="fw-semibold"><?php echo e($log->created_at->format('d/m/Y H:i')); ?></div>
                                <div class="small text-secondary"><?php echo e($log->created_at->diffForHumans()); ?></div>
                            </td>
                            <td>
                                <div class="fw-semibold"><?php echo e($log->user?->name ?: __('System')); ?></div>
                                <div class="small text-secondary"><?php echo e($log->user?->email ?: '—'); ?></div>
                            </td>
                            <td>
                                <div class="small text-secondary"><?php echo e($log->resourceLabel()); ?></div>
                                <?php if($log->subjectUrl()): ?>
                                    <a href="<?php echo e($log->subjectUrl()); ?>" class="fw-semibold"><?php echo e($log->auditable_label); ?></a>
                                <?php else: ?>
                                    <span class="fw-semibold"><?php echo e($log->auditable_label); ?></span>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge text-bg-light border"><?php echo e(__(str_replace('_', ' ', $log->event))); ?></span></td>
                            <td>
                                <?php if($log->old_values || $log->new_values): ?>
                                    <details class="fm-audit-details">
                                        <summary><?php echo e(__('View changes')); ?></summary>
                                        <div class="mt-2">
                                            <?php $__currentLoopData = collect(array_keys($log->old_values ?? []))->merge(array_keys($log->new_values ?? []))->unique(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <div class="fm-audit-change">
                                                    <span><?php echo e($log->fieldLabel($field)); ?></span>
                                                    <code><?php echo e(is_array(data_get($log->old_values, $field)) ? json_encode(data_get($log->old_values, $field)) : (data_get($log->old_values, $field) ?? '—')); ?></code>
                                                    <i class="bi bi-arrow-right"></i>
                                                    <code><?php echo e(is_array(data_get($log->new_values, $field)) ? json_encode(data_get($log->new_values, $field)) : (data_get($log->new_values, $field) ?? '—')); ?></code>
                                                </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </div>
                                    </details>
                                <?php else: ?>
                                    <span class="text-secondary">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-nowrap text-secondary"><?php echo e($log->ip_address ?: '—'); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="6" class="text-center py-5 text-secondary"><?php echo e(__('No activity matches the selected filters.')); ?></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if($logs->hasPages()): ?>
            <div class="card-footer bg-white"><?php echo e($logs->links()); ?></div>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Projects\flowmanager\resources\views/activity/index.blade.php ENDPATH**/ ?>