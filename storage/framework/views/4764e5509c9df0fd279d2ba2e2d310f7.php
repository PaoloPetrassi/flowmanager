<?php $__env->startSection('title', __('Gantt')); ?>
<?php $__env->startSection('page-title', __('Gantt')); ?>
<?php $__env->startSection('page-subtitle', __('Project and task schedule across a shared timeline')); ?>

<?php $__env->startSection('content'); ?>
    <div class="card fm-card mb-4"><div class="card-body"><form class="row g-3 align-items-end" method="GET">
        <div class="col-12 col-sm-4"><label class="form-label"><?php echo e(__('From')); ?></label><input class="form-control" type="date" name="start" value="<?php echo e($start->format('Y-m-d')); ?>"></div>
        <div class="col-12 col-sm-4"><label class="form-label"><?php echo e(__('To')); ?></label><input class="form-control" type="date" name="end" value="<?php echo e($end->format('Y-m-d')); ?>"></div>
        <div class="col-12 col-sm-4 d-flex gap-2"><button class="btn btn-primary flex-grow-1"><?php echo e(__('Apply')); ?></button><a class="btn btn-outline-secondary" href="<?php echo e(route('planning.gantt')); ?>"><?php echo e(__('Reset')); ?></a></div>
    </form></div></div>

    <div class="card fm-card">
        <div class="card-header fm-card-header"><div><h2 class="fm-card-title"><?php echo e(__('Timeline')); ?></h2><p class="fm-card-subtitle"><?php echo e($start->format('d/m/Y')); ?> — <?php echo e($end->format('d/m/Y')); ?></p></div><span class="badge text-bg-light"><?php echo e($projects->count()); ?> <?php echo e(__('projects')); ?></span></div>
        <div class="card-body p-0">
            <div class="fm-gantt-scroll">
                <div class="fm-gantt" style="--fm-gantt-days: <?php echo e($totalDays); ?>;">
                    <div class="fm-gantt-axis"><div class="fm-gantt-label"><?php echo e(__('Project / task')); ?></div><div class="fm-gantt-track"><span><?php echo e($start->format('d M')); ?></span><span><?php echo e($start->copy()->addDays((int) ($totalDays / 2))->format('d M')); ?></span><span><?php echo e($end->format('d M')); ?></span></div></div>
                    <?php $__empty_1 = true; $__currentLoopData = $projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $projectStart = $project->start_date ?: $start;
                            $projectEnd = $project->due_date ?: $projectStart;
                            $visibleStart = $projectStart->lt($start) ? $start : $projectStart;
                            $visibleEnd = $projectEnd->gt($end) ? $end : $projectEnd;
                            $left = max(0, $start->diffInDays($visibleStart, false) / $totalDays * 100);
                            $width = max(1.5, $visibleStart->diffInDays($visibleEnd) / $totalDays * 100);
                        ?>
                        <div class="fm-gantt-row is-project"><div class="fm-gantt-label"><a href="<?php echo e(route('projects.show', $project)); ?>" class="fw-semibold"><?php echo e($project->code); ?></a><small><?php echo e($project->name); ?></small></div><div class="fm-gantt-track"><a href="<?php echo e(route('projects.show', $project)); ?>" class="fm-gantt-bar is-project" style="left: <?php echo e($left); ?>%; width: <?php echo e($width); ?>%;" title="<?php echo e($project->name); ?>"></a></div></div>
                        <?php $__currentLoopData = $project->tasks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $taskDate = $task->due_date;
                                $taskLeft = max(0, min(99, $start->diffInDays($taskDate, false) / $totalDays * 100));
                            ?>
                            <?php if($taskDate->betweenIncluded($start, $end)): ?>
                                <div class="fm-gantt-row"><div class="fm-gantt-label ps-4"><a href="<?php echo e(route('tasks.show', $task)); ?>"><?php echo e(Str::limit($task->title, 48)); ?></a></div><div class="fm-gantt-track"><a href="<?php echo e(route('tasks.show', $task)); ?>" class="fm-gantt-marker" style="left: <?php echo e($taskLeft); ?>%;" title="<?php echo e($task->title); ?> · <?php echo e($taskDate->format('d/m/Y')); ?>"></a></div></div>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="p-5 text-center text-secondary"><?php echo e(__('No projects fall within this time range.')); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Projects\flowmanager\resources\views/planning/gantt.blade.php ENDPATH**/ ?>