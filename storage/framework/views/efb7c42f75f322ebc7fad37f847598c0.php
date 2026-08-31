<?php $__env->startSection('title', __('Reports')); ?>
<?php $__env->startSection('page-title', __('Reports')); ?>
<?php $__env->startSection('page-subtitle', __('Operational analysis and exportable datasets')); ?>

<?php $__env->startSection('content'); ?>
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg">
            <div class="card fm-card h-100"><div class="card-body"><div class="text-secondary small"><?php echo e(__('Active projects')); ?></div><div class="fm-report-kpi"><?php echo e($summary['projects_active']); ?></div></div></div>
        </div>
        <div class="col-6 col-lg">
            <div class="card fm-card h-100"><div class="card-body"><div class="text-secondary small"><?php echo e(__('Open tasks')); ?></div><div class="fm-report-kpi"><?php echo e($summary['tasks_open']); ?></div></div></div>
        </div>
        <div class="col-6 col-lg">
            <div class="card fm-card h-100"><div class="card-body"><div class="text-secondary small"><?php echo e(__('Overdue tasks')); ?></div><div class="fm-report-kpi"><?php echo e($summary['tasks_overdue']); ?></div></div></div>
        </div>
        <div class="col-6 col-lg">
            <div class="card fm-card h-100"><div class="card-body"><div class="text-secondary small"><?php echo e(__('Open tickets')); ?></div><div class="fm-report-kpi"><?php echo e($summary['tickets_open']); ?></div></div></div>
        </div>
        <div class="col-6 col-lg">
            <div class="card fm-card h-100"><div class="card-body"><div class="text-secondary small"><?php echo e(__('Assets in service')); ?></div><div class="fm-report-kpi"><?php echo e($summary['assets_active']); ?></div></div></div>
        </div>
    </div>

    <div class="card fm-card mb-4">
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('reports.index')); ?>" class="row g-3 align-items-end">
                <div class="col-12 col-md-4">
                    <label for="report-type" class="form-label"><?php echo e(__('Dataset')); ?></label>
                    <select id="report-type" name="type" class="form-select">
                        <?php $__currentLoopData = $types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reportType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($reportType); ?>" <?php if($type === $reportType): echo 'selected'; endif; ?>><?php echo e(__(ucfirst($reportType))); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label for="date-from" class="form-label"><?php echo e(__('Created from')); ?></label>
                    <input id="date-from" type="date" name="date_from" value="<?php echo e($dateFrom); ?>" class="form-control">
                </div>
                <div class="col-6 col-md-3">
                    <label for="date-to" class="form-label"><?php echo e(__('Created to')); ?></label>
                    <input id="date-to" type="date" name="date_to" value="<?php echo e($dateTo); ?>" class="form-control">
                </div>
                <div class="col-12 col-md-2 d-grid">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-funnel me-1"></i><?php echo e(__('Apply')); ?></button>
                </div>
            </form>
        </div>
    </div>

    <div class="card fm-card">
        <div class="card-header fm-card-header flex-wrap gap-3">
            <div>
                <h2 class="fm-card-title"><?php echo e(__(ucfirst($type))); ?></h2>
                <p class="fm-card-subtitle"><?php echo e(trans_choice('ui.report_rows', $totalRows, ['count' => $totalRows])); ?></p>
            </div>
            <?php if(auth()->user()->hasPermission('reports.export')): ?>
                <?php ($query = ['type' => $type, 'date_from' => $dateFrom, 'date_to' => $dateTo]); ?>
                <div class="d-flex flex-wrap gap-2">
                    <a href="<?php echo e(route('reports.csv', $query)); ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-filetype-csv me-1"></i>CSV</a>
                    <a href="<?php echo e(route('reports.excel', $query)); ?>" class="btn btn-sm btn-outline-success"><i class="bi bi-file-earmark-excel me-1"></i>Excel</a>
                    <a href="<?php echo e(route('reports.pdf', $query)); ?>" class="btn btn-sm btn-outline-danger"><i class="bi bi-file-earmark-pdf me-1"></i>PDF</a>
                    <a href="<?php echo e(route('reports.print', $query)); ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-printer me-1"></i><?php echo e(__('Print')); ?></a>
                </div>
            <?php endif; ?>
        </div>

        <div class="table-responsive">
            <table class="table fm-table align-middle mb-0">
                <thead>
                    <tr>
                        <?php $__currentLoopData = $columns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <th><?php echo e($label); ?></th>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <?php $__currentLoopData = array_keys($columns); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <td><?php echo e($row[$key] ?: '—'); ?></td>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="<?php echo e(count($columns)); ?>" class="text-center py-5 text-secondary"><?php echo e(__('No data matches the selected filters.')); ?></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if($totalRows > 200): ?>
            <div class="card-footer bg-white text-secondary small"><?php echo e(__('The on-screen preview is limited to 200 rows. Exports include the complete dataset.')); ?></div>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Projects\flowmanager\resources\views/reports/index.blade.php ENDPATH**/ ?>