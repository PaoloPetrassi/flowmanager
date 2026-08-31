<?php $__env->startSection('title', __('Kanban')); ?>
<?php $__env->startSection('page-title', __('Kanban')); ?>
<?php $__env->startSection('page-subtitle', __('Visual workflow for tasks and support tickets')); ?>

<?php $__env->startSection('content'); ?>
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div class="btn-group" role="group" aria-label="<?php echo e(__('Board type')); ?>">
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('viewAny', App\Models\Task::class)): ?>
                <a href="<?php echo e(route('boards.index', ['type' => 'tasks'])); ?>" class="btn <?php echo e($type === 'tasks' ? 'btn-primary' : 'btn-outline-primary'); ?>"><?php echo e(__('Tasks')); ?></a>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('viewAny', App\Models\Ticket::class)): ?>
                <a href="<?php echo e(route('boards.index', ['type' => 'tickets'])); ?>" class="btn <?php echo e($type === 'tickets' ? 'btn-primary' : 'btn-outline-primary'); ?>"><?php echo e(__('Tickets')); ?></a>
            <?php endif; ?>
        </div>

        <div class="d-flex align-items-center flex-wrap gap-2">
            <div class="btn-group btn-group-sm" role="group" aria-label="<?php echo e(__('Assignment filter')); ?>">
                <a href="<?php echo e(route('boards.index', ['type' => $type])); ?>" class="btn <?php echo e($assigneeId ? 'btn-outline-secondary' : 'btn-secondary'); ?>"><?php echo e(__('All')); ?></a>
                <a href="<?php echo e(route('boards.index', ['type' => $type, 'assigned_to' => auth()->id()])); ?>" class="btn <?php echo e($assigneeId === auth()->id() ? 'btn-secondary' : 'btn-outline-secondary'); ?>"><?php echo e(__('My work')); ?></a>
            </div>
            <div class="text-secondary small">
                <i class="bi bi-arrows-move me-1"></i><?php echo e($canUpdate ? __('Drag cards between columns or use the status selector.') : __('Read-only board.')); ?>

            </div>
        </div>
    </div>

    <div class="fm-kanban-scroll">
        <div class="fm-kanban" data-kanban-board data-kanban-enabled="<?php echo e($canUpdate ? '1' : '0'); ?>">
            <?php $__currentLoopData = $columns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status => $statusLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $columnItems = $items->get($status, collect());
                ?>
                <section class="fm-kanban-column" data-kanban-status="<?php echo e($status); ?>">
                    <header class="fm-kanban-column-header">
                        <span><?php echo e($statusLabel); ?></span>
                        <span class="fm-kanban-count" data-kanban-count><?php echo e($columnItems->count()); ?></span>
                    </header>

                    <div class="fm-kanban-list" data-kanban-list>
                        <?php $__currentLoopData = $columnItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $isTask = $type === 'tasks';
                                $updateUrl = $isTask
                                    ? route('boards.tasks.status', $item)
                                    : route('boards.tickets.status', $item);
                                $showUrl = $isTask
                                    ? route('tasks.show', $item)
                                    : route('tickets.show', $item);
                            ?>
                            <article
                                class="fm-kanban-card"
                                draggable="<?php echo e($canUpdate ? 'true' : 'false'); ?>"
                                data-kanban-card
                                data-update-url="<?php echo e($updateUrl); ?>"
                            >
                                <div class="d-flex justify-content-between gap-2 align-items-start">
                                    <a href="<?php echo e($showUrl); ?>" class="fw-semibold text-dark text-decoration-none"><?php echo e($isTask ? $item->title : $item->subject); ?></a>
                                    <span class="badge <?php echo e(in_array($item->priority->value, ['urgent', 'high'], true) ? 'text-bg-warning' : 'text-bg-light'); ?>"><?php echo e($item->priority->label()); ?></span>
                                </div>
                                <div class="small text-secondary mt-2">
                                    <?php if($isTask): ?>
                                        <?php echo e($item->project?->code); ?> · <?php echo e($item->project?->name); ?>

                                    <?php else: ?>
                                        <?php echo e($item->reference); ?><?php if($item->company): ?> · <?php echo e($item->company->name); ?><?php endif; ?>
                                    <?php endif; ?>
                                </div>
                                <?php if($isTask && $item->due_date): ?>
                                    <div class="small mt-2 <?php echo e($item->due_date->isPast() && $item->status->value !== 'completed' ? 'text-danger' : 'text-secondary'); ?>">
                                        <i class="bi bi-calendar3 me-1"></i><?php echo e($item->due_date->format('d/m/Y')); ?>

                                    </div>
                                <?php endif; ?>
                                <?php if($item->assignee): ?>
                                    <div class="fm-kanban-assignee mt-2"><span><?php echo e(strtoupper(substr($item->assignee->name, 0, 1))); ?></span><?php echo e($item->assignee->name); ?></div>
                                <?php endif; ?>

                                <?php if($canUpdate): ?>
                                    <form method="POST" action="<?php echo e($updateUrl); ?>" class="fm-kanban-status-form mt-3" data-kanban-status-form>
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('PATCH'); ?>
                                        <select name="status" class="form-select form-select-sm" aria-label="<?php echo e(__('Status')); ?>">
                                            <?php $__currentLoopData = $columns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $optionStatus => $optionLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($optionStatus); ?>" <?php if($optionStatus === $status): echo 'selected'; endif; ?>><?php echo e($optionLabel); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-outline-secondary"><?php echo e(__('Move')); ?></button>
                                    </form>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </section>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Projects\flowmanager\resources\views/boards/index.blade.php ENDPATH**/ ?>