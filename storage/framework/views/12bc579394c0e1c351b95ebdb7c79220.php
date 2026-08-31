<?php $__env->startSection('title', __('Workload')); ?>
<?php $__env->startSection('page-title', __('Team workload')); ?>
<?php $__env->startSection('page-subtitle', __('Open work, estimated effort and tracked time by user')); ?>

<?php $__env->startSection('content'); ?>
    <div class="row g-4 mb-4">
        <div class="col-12 col-md-4"><div class="card fm-card h-100"><div class="card-body"><div class="text-secondary small"><?php echo e(__('Users with assigned work')); ?></div><div class="display-6 fw-bold"><?php echo e($users->where('open_tasks_count', '>', 0)->count()); ?></div></div></div></div>
        <div class="col-12 col-md-4"><div class="card fm-card h-100"><div class="card-body"><div class="text-secondary small"><?php echo e(__('Open tasks')); ?></div><div class="display-6 fw-bold"><?php echo e($users->sum('open_tasks_count')); ?></div></div></div></div>
        <div class="col-12 col-md-4"><div class="card fm-card h-100"><div class="card-body"><div class="text-secondary small"><?php echo e(__('Tracked hours')); ?></div><div class="display-6 fw-bold"><?php echo e(number_format($users->sum('tracked_minutes_sum') / 60, 1)); ?></div></div></div></div>
    </div>

    <div class="card fm-card"><div class="card-header fm-card-header"><div><h2 class="fm-card-title"><?php echo e(__('Workload by user')); ?></h2><p class="fm-card-subtitle"><?php echo e(__('A quick capacity view for managers')); ?></p></div></div><div class="card-body p-0"><div class="table-responsive"><table class="table align-middle mb-0">
        <thead><tr><th><?php echo e(__('User')); ?></th><th><?php echo e(__('Open tasks')); ?></th><th><?php echo e(__('Active projects')); ?></th><th><?php echo e(__('Estimated hours')); ?></th><th><?php echo e(__('Tracked hours')); ?></th><th style="min-width:220px"><?php echo e(__('Load indicator')); ?></th></tr></thead>
        <tbody>
            <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php ($load = min(100, ($user->open_tasks_count * 10) + (($user->estimated_minutes_sum ?? 0) / 60))); ?>
                <tr><td><div class="fw-semibold"><?php echo e($user->name); ?></div><div class="small text-secondary"><?php echo e($user->email); ?></div></td><td><?php echo e($user->open_tasks_count); ?></td><td><?php echo e($user->active_projects_count); ?></td><td><?php echo e(number_format(($user->estimated_minutes_sum ?? 0) / 60, 1)); ?></td><td><?php echo e(number_format(($user->tracked_minutes_sum ?? 0) / 60, 1)); ?></td><td><div class="progress" style="height:8px"><div class="progress-bar" style="width: <?php echo e($load); ?>%"></div></div><div class="small text-secondary mt-1"><?php echo e(number_format($load, 0)); ?>%</div></td></tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table></div></div></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Projects\flowmanager\resources\views/planning/workload.blade.php ENDPATH**/ ?>