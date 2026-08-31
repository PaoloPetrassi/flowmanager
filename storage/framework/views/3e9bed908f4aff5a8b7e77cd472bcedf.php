<?php $__env->startSection('title', __('Calendar')); ?>
<?php $__env->startSection('page-title', __('Calendar')); ?>
<?php $__env->startSection('page-subtitle', __('Projects, tasks and asset warranty deadlines in one place')); ?>

<?php $__env->startSection('content'); ?>
    <div class="card fm-card">
        <div class="card-header fm-card-header flex-wrap gap-3">
            <div class="d-flex align-items-center gap-2">
                <a href="<?php echo e(route('calendar.index', ['month' => $previousMonth])); ?>" class="btn btn-sm btn-outline-secondary" aria-label="<?php echo e(__('Previous month')); ?>"><i class="bi bi-chevron-left"></i></a>
                <div>
                    <h2 class="fm-card-title text-capitalize"><?php echo e($month->locale(app()->getLocale())->translatedFormat('F Y')); ?></h2>
                    <p class="fm-card-subtitle"><?php echo e(trans_choice('ui.calendar_events', $eventCount, ['count' => $eventCount])); ?></p>
                </div>
                <a href="<?php echo e(route('calendar.index', ['month' => $nextMonth])); ?>" class="btn btn-sm btn-outline-secondary" aria-label="<?php echo e(__('Next month')); ?>"><i class="bi bi-chevron-right"></i></a>
            </div>
            <div class="d-flex gap-2"><a href="<?php echo e(route('calendar.ics')); ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-calendar-plus me-1"></i><?php echo e(__('Export iCalendar')); ?></a><a href="<?php echo e(route('calendar.index', ['month' => now()->format('Y-m')])); ?>" class="btn btn-sm btn-outline-primary"><?php echo e(__('Today')); ?></a></div>
        </div>

        <div class="fm-calendar-scroll">
            <div class="fm-calendar" role="grid">
                <?php $__currentLoopData = [__('Mon'), __('Tue'), __('Wed'), __('Thu'), __('Fri'), __('Sat'), __('Sun')]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $weekday): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="fm-calendar-weekday" role="columnheader"><?php echo e($weekday); ?></div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                <?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $dateKey = $day->toDateString();
                        $dayEvents = $eventsByDate->get($dateKey, collect());
                    ?>
                    <div class="fm-calendar-day <?php echo e($day->month !== $month->month ? 'is-outside' : ''); ?> <?php echo e($day->isToday() ? 'is-today' : ''); ?>" role="gridcell">
                        <div class="fm-calendar-day-number"><?php echo e($day->day); ?></div>
                        <div class="fm-calendar-events">
                            <?php $__currentLoopData = $dayEvents->take(4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <a href="<?php echo e($event['url']); ?>" class="fm-calendar-event fm-calendar-event-<?php echo e($event['type']); ?>" title="<?php echo e($event['label']); ?>">
                                    <i class="bi <?php echo e($event['icon']); ?>"></i>
                                    <span><?php echo e($event['label']); ?></span>
                                </a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php if($dayEvents->count() > 4): ?>
                                <span class="fm-calendar-more">+<?php echo e($dayEvents->count() - 4); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        <div class="card-footer bg-white d-flex flex-wrap gap-3 small text-secondary">
            <span><i class="fm-legend-dot fm-legend-primary"></i><?php echo e(__('Project deadline')); ?></span>
            <span><i class="fm-legend-dot fm-legend-success"></i><?php echo e(__('Task deadline')); ?></span>
            <span><i class="fm-legend-dot fm-legend-warning"></i><?php echo e(__('Warranty expiry')); ?></span>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Projects\flowmanager\resources\views/calendar/index.blade.php ENDPATH**/ ?>