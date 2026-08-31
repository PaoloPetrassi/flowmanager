<?php $__env->startSection('title', __('Trash')); ?>
<?php $__env->startSection('page-title', __('Trash')); ?>
<?php $__env->startSection('page-subtitle', __('Restore soft-deleted records or permanently remove them')); ?>

<?php $__env->startSection('content'); ?>
    <div class="alert alert-warning d-flex gap-2 align-items-start">
        <i class="bi bi-exclamation-triangle mt-1"></i>
        <div><?php echo e(__('Permanent deletion cannot be undone. FlowManager blocks it when required dependent records still exist.')); ?></div>
    </div>

    <div class="card fm-card mb-4">
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('trash.index')); ?>" class="row g-3 align-items-end">
                <div class="col-12 col-md-6 col-lg-5">
                    <label class="form-label"><?php echo e(__('Search')); ?></label>
                    <input type="search" name="search" value="<?php echo e($filters['search']); ?>" class="form-control" placeholder="<?php echo e(__('Search deleted records')); ?>">
                </div>
                <div class="col-12 col-md-4 col-lg-3">
                    <label class="form-label"><?php echo e(__('Resource')); ?></label>
                    <select name="type" class="form-select">
                        <option value=""><?php echo e(__('All resources')); ?></option>
                        <?php $__currentLoopData = $resources; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $resource): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($resource); ?>" <?php if($filters['type'] === $resource): echo 'selected'; endif; ?>><?php echo e(__(ucfirst($resource))); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-12 col-md-2 d-grid">
                    <button type="submit" class="btn btn-primary"><?php echo e(__('Apply')); ?></button>
                </div>
                <?php if($filters['type'] || $filters['search']): ?>
                    <div class="col-12 col-md-auto d-grid">
                        <a href="<?php echo e(route('trash.index')); ?>" class="btn btn-outline-secondary"><?php echo e(__('Reset')); ?></a>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="card fm-card">
        <div class="table-responsive">
            <table class="table align-middle mb-0 fm-table">
                <thead>
                    <tr>
                        <th><?php echo e(__('Resource')); ?></th>
                        <th><?php echo e(__('Record')); ?></th>
                        <th><?php echo e(__('Deleted at')); ?></th>
                        <th class="text-end"><?php echo e(__('Actions')); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><span class="badge text-bg-light border"><?php echo e($item['resource']); ?></span></td>
                            <td class="fw-semibold"><?php echo e($item['label']); ?></td>
                            <td>
                                <div><?php echo e($item['deleted_at']?->format('d/m/Y H:i') ?: '—'); ?></div>
                                <?php if($item['deleted_at']): ?><div class="small text-secondary"><?php echo e($item['deleted_at']->diffForHumans()); ?></div><?php endif; ?>
                            </td>
                            <td>
                                <div class="d-flex justify-content-end gap-2">
                                    <?php if(auth()->user()->hasPermission('trash.restore')): ?>
                                        <form method="POST" action="<?php echo e(route('trash.restore', [$item['type'], $item['model']->id])); ?>">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('PATCH'); ?>
                                            <button type="submit" class="btn btn-sm btn-outline-success">
                                                <i class="bi bi-arrow-counterclockwise me-1"></i><?php echo e(__('Restore')); ?>

                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if(auth()->user()->hasPermission('trash.delete')): ?>
                                        <form method="POST" action="<?php echo e(route('trash.destroy', [$item['type'], $item['model']->id])); ?>" onsubmit="return confirm(<?php echo \Illuminate\Support\Js::from(__('Permanently delete this item? This action cannot be undone.'))->toHtml() ?>);">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash3 me-1"></i><?php echo e(__('Delete permanently')); ?>

                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="4" class="text-center py-5 text-secondary"><?php echo e(__('Trash is empty for the selected filters.')); ?></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if($items->hasPages()): ?>
            <div class="card-footer bg-white"><?php echo e($items->links()); ?></div>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Projects\flowmanager\resources\views/trash/index.blade.php ENDPATH**/ ?>