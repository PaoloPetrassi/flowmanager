<?php $__env->startSection('title', __('Projects')); ?>
<?php $__env->startSection('page-title', __('Projects')); ?>
<?php $__env->startSection('page-subtitle', __('Plan work, owners, deadlines and company delivery')); ?>
<?php $__env->startSection('content'); ?>
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div class="text-secondary"><?php echo e(trans_choice('ui.counts.projects', $projects->total(), ['count' => $projects->total()])); ?></div>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\Project::class)): ?>
            <a href="<?php echo e(route('projects.create')); ?>" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> <?php echo e(__('New project')); ?></a>
        <?php endif; ?>
    </div>

    <div class="card fm-card mb-4">
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('projects.index')); ?>">
                <div class="row g-3">
                    <div class="col-12 col-lg-4">
                        <label for="search" class="form-label fw-semibold"><?php echo e(__('Search')); ?></label>
                        <input id="search" name="search" class="form-control" value="<?php echo e($filters['search']); ?>" placeholder="<?php echo e(__('Code, name, company...')); ?>">
                    </div>
                    <div class="col-12 col-md-6 col-lg-2">
                        <label for="company_id" class="form-label fw-semibold"><?php echo e(__('Company')); ?></label>
                        <select id="company_id" name="company_id" class="form-select">
                            <option value=""><?php echo e(__('All companies')); ?></option>
                            <?php $__currentLoopData = $companies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $company): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($company->id); ?>" <?php if((string) $filters['company_id'] === (string) $company->id): echo 'selected'; endif; ?>><?php echo e($company->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <label for="status" class="form-label fw-semibold"><?php echo e(__('Status')); ?></label>
                        <select id="status" name="status" class="form-select"><option value=""><?php echo e(__('All statuses')); ?></option><?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($status->value); ?>" <?php if($filters['status'] === $status->value): echo 'selected'; endif; ?>><?php echo e($status->label()); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <label for="priority" class="form-label fw-semibold"><?php echo e(__('Priority')); ?></label>
                        <select id="priority" name="priority" class="form-select"><option value=""><?php echo e(__('All priorities')); ?></option><?php $__currentLoopData = $priorities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $priority): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($priority->value); ?>" <?php if($filters['priority'] === $priority->value): echo 'selected'; endif; ?>><?php echo e($priority->label()); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select>
                    </div>
                    <div class="col-12 col-md-6 col-lg-2">
                        <label for="manager_id" class="form-label fw-semibold"><?php echo e(__('Manager')); ?></label>
                        <select id="manager_id" name="manager_id" class="form-select"><option value=""><?php echo e(__('All managers')); ?></option><?php $__currentLoopData = $managers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $manager): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($manager->id); ?>" <?php if((string) $filters['manager_id'] === (string) $manager->id): echo 'selected'; endif; ?>><?php echo e($manager->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select>
                    </div>
                    <div class="col-12 col-md-6 col-lg-3">
                        <label for="sort" class="form-label fw-semibold"><?php echo e(__('Sort by')); ?></label>
                        <select id="sort" name="sort" class="form-select">
                            <?php $__currentLoopData = ['name' => 'Name', 'code' => 'Code', 'status' => 'Status', 'priority' => 'Priority', 'start_date' => 'Start date', 'due_date' => 'Due date', 'created_at' => 'Created']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($value); ?>" <?php if($filters['sort'] === $value): echo 'selected'; endif; ?>><?php echo e(__($label)); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <label for="direction" class="form-label fw-semibold"><?php echo e(__('Order')); ?></label>
                        <select id="direction" name="direction" class="form-select"><option value="asc" <?php if($filters['direction'] === 'asc'): echo 'selected'; endif; ?>><?php echo e(__('Ascending')); ?></option><option value="desc" <?php if($filters['direction'] === 'desc'): echo 'selected'; endif; ?>><?php echo e(__('Descending')); ?></option></select>
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2 mt-3"><a href="<?php echo e(route('projects.index')); ?>" class="btn btn-outline-secondary"><?php echo e(__('Reset')); ?></a><button type="submit" class="btn btn-primary"><?php echo e(__('Apply filters')); ?></button></div>
            </form>
        </div>
    </div>

    <div class="card fm-card">
        <div class="table-responsive">
            <table class="table align-middle mb-0 fm-table">
                <thead><tr><th><?php echo e(__('Project')); ?></th><th><?php echo e(__('Company')); ?></th><th><?php echo e(__('Status')); ?></th><th><?php echo e(__('Priority')); ?></th><th><?php echo e(__('Manager')); ?></th><th><?php echo e(__('Due date')); ?></th><th><?php echo e(__('Progress')); ?></th><th><?php echo e(__('Tasks')); ?></th><th class="text-end"><?php echo e(__('Actions')); ?></th></tr></thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $statusClass = match ($project->status->value) {'active' => 'text-bg-primary', 'completed' => 'text-bg-success', 'on_hold' => 'text-bg-warning', 'cancelled' => 'text-bg-secondary', default => 'text-bg-light border text-secondary'};
                            $priorityClass = match ($project->priority->value) {'urgent' => 'text-bg-danger', 'high' => 'text-bg-warning', 'low' => 'text-bg-light border text-secondary', default => 'text-bg-primary'};
                        ?>
                        <tr>
                            <td><a class="fw-semibold text-dark" href="<?php echo e(route('projects.show', $project)); ?>"><?php echo e($project->name); ?></a><div class="small text-secondary"><?php echo e($project->code); ?></div></td>
                            <td>
                                <?php if($project->company->trashed()): ?>
                                    <span><?php echo e($project->company->name); ?></span>
                                    <span class="badge text-bg-light border text-secondary ms-1"><?php echo e(__('Archived')); ?></span>
                                <?php else: ?>
                                    <a href="<?php echo e(route('companies.show', $project->company)); ?>"><?php echo e($project->company->name); ?></a>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge <?php echo e($statusClass); ?>"><?php echo e($project->status->label()); ?></span></td>
                            <td><span class="badge <?php echo e($priorityClass); ?>"><?php echo e($project->priority->label()); ?></span></td>
                            <td><?php echo e($project->manager?->name ?: '—'); ?></td>
                            <td><?php echo e($project->due_date?->format('d/m/Y') ?: '—'); ?></td>
                            <td style="min-width:120px"><div class="d-flex align-items-center gap-2"><div class="progress flex-grow-1" style="height:6px"><div class="progress-bar" style="width: <?php echo e($project->progressPercentage()); ?>%"></div></div><span class="small"><?php echo e($project->progressPercentage()); ?>%</span></div></td>
                            <td><?php echo e($project->tasks_count); ?></td>
                            <td class="text-end"><div class="btn-group"><a href="<?php echo e(route('projects.show', $project)); ?>" class="btn btn-sm btn-outline-secondary" title="<?php echo e(__('View')); ?>"><i class="bi bi-eye"></i></a><?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $project)): ?><a href="<?php echo e(route('projects.edit', $project)); ?>" class="btn btn-sm btn-outline-secondary" title="<?php echo e(__('Edit')); ?>"><i class="bi bi-pencil"></i></a><?php endif; ?> <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $project)): ?><form method="POST" action="<?php echo e(route('projects.destroy', $project)); ?>" onsubmit="return confirm(<?php echo \Illuminate\Support\Js::from(__('Delete this project?'))->toHtml() ?>);"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button class="btn btn-sm btn-outline-danger rounded-start-0" title="<?php echo e(__('Delete')); ?>"><i class="bi bi-trash"></i></button></form><?php endif; ?></div></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="9" class="text-center py-5"><i class="bi bi-kanban fs-1 text-secondary"></i><div class="fw-semibold mt-3"><?php echo e(__('No projects found')); ?></div><div class="text-secondary"><?php echo e(__('Create a project or change the active filters.')); ?></div></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if($projects->hasPages()): ?><div class="card-footer bg-white p-3"><?php echo e($projects->links()); ?></div><?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Projects\flowmanager\resources\views/projects/index.blade.php ENDPATH**/ ?>