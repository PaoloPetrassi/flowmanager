<?php $__env->startSection('title', __('Search')); ?>
<?php $__env->startSection('page-title', __('Global search')); ?>
<?php $__env->startSection('page-subtitle', __('Find records across every module you can access')); ?>

<?php $__env->startSection('content'); ?>
    <div class="card fm-card mb-4">
        <div class="card-body p-4">
            <form method="GET" action="<?php echo e(route('search.index')); ?>" class="fm-search-page-form">
                <i class="bi bi-search"></i>
                <input type="search" name="q" value="<?php echo e($query); ?>" class="form-control form-control-lg" placeholder="<?php echo e(__('Search companies, contacts, projects, tasks, assets, tickets and users...')); ?>" autofocus>
                <button type="submit" class="btn btn-primary px-4"><?php echo e(__('Search')); ?></button>
            </form>
            <div class="form-text mt-2"><?php echo e(__('Enter at least 2 characters. Results respect your current permissions.')); ?></div>
        </div>
    </div>

    <?php if($query !== '' && mb_strlen($query) < 2): ?>
        <div class="alert alert-info"><?php echo e(__('Enter at least 2 characters to start searching.')); ?></div>
    <?php elseif($query !== ''): ?>
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h2 class="h6 mb-0"><?php echo e(__('Results for ":query"', ['query' => $query])); ?></h2>
            <span class="text-secondary small"><?php echo e(trans_choice('ui.counts.search_results', $resultCount, ['count' => $resultCount])); ?></span>
        </div>

        <?php $__empty_1 = true; $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="card fm-card mb-4">
                <div class="card-header fm-card-header">
                    <h2 class="fm-card-title"><?php echo e(__($group['label'])); ?></h2>
                    <span class="badge text-bg-light border"><?php echo e(count($group['items'])); ?></span>
                </div>
                <div class="list-group list-group-flush">
                    <?php $__currentLoopData = $group['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e($item['url']); ?>" class="list-group-item list-group-item-action fm-search-result">
                            <div class="fm-search-result-icon"><i class="bi <?php echo e($item['icon']); ?>"></i></div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="fw-semibold text-dark"><?php echo e($item['title']); ?></div>
                                <?php if($item['subtitle']): ?><div class="small text-secondary text-truncate"><?php echo e($item['subtitle']); ?></div><?php endif; ?>
                            </div>
                            <?php if($item['meta']): ?><span class="small text-secondary"><?php echo e($item['meta']); ?></span><?php endif; ?>
                            <i class="bi bi-chevron-right text-secondary"></i>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="card fm-card">
                <div class="card-body p-5 text-center text-secondary">
                    <i class="bi bi-search d-block fs-2 mb-2"></i>
                    <?php echo e(__('No results found for this search.')); ?>

                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Projects\flowmanager\resources\views/search/index.blade.php ENDPATH**/ ?>