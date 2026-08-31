<?php $__env->startSection('title', $contact->full_name); ?>
<?php $__env->startSection('page-title', $contact->full_name); ?>
<?php $__env->startSection('page-subtitle', __('Contact workspace and related activity')); ?>

<?php $__env->startSection('content'); ?>
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <a href="<?php echo e(route('contacts.index')); ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            <?php echo e(__('Contacts')); ?>

        </a>

        <div class="d-flex flex-wrap gap-2">
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\Project::class)): ?>
                <?php if($contact->company): ?>
                    <a href="<?php echo e(route('projects.create', ['company' => $contact->company_id, 'contact' => $contact->id])); ?>" class="btn btn-outline-primary">
                        <i class="bi bi-kanban me-1"></i>
                        <?php echo e(__('Project')); ?>

                    </a>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\Ticket::class)): ?>
                <a href="<?php echo e(route('tickets.create', ['company' => $contact->company_id, 'contact' => $contact->id])); ?>" class="btn btn-outline-primary">
                    <i class="bi bi-ticket-perforated me-1"></i>
                    <?php echo e(__('Ticket')); ?>

                </a>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $contact)): ?>
                <a href="<?php echo e(route('contacts.edit', $contact)); ?>" class="btn btn-primary">
                    <i class="bi bi-pencil me-1"></i>
                    <?php echo e(__('Edit')); ?>

                </a>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $contact)): ?>
                <form method="POST" action="<?php echo e(route('contacts.destroy', $contact)); ?>" onsubmit="return confirm(<?php echo \Illuminate\Support\Js::from(__('Delete this contact?'))->toHtml() ?>);">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('DELETE'); ?>
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="bi bi-trash me-1"></i>
                        <?php echo e(__('Delete')); ?>

                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('viewAny', App\Models\Project::class)): ?>
            <div class="col-6 col-md-3">
                <div class="card fm-card h-100">
                    <div class="card-body">
                        <div class="small text-secondary"><?php echo e(__('Projects')); ?></div>
                        <div class="h4 mb-0 mt-1"><?php echo e($relatedCounts['projects']); ?></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('viewAny', App\Models\Ticket::class)): ?>
            <div class="col-6 col-md-3">
                <div class="card fm-card h-100">
                    <div class="card-body">
                        <div class="small text-secondary"><?php echo e(__('Tickets')); ?></div>
                        <div class="h4 mb-0 mt-1"><?php echo e($relatedCounts['tickets']); ?></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="row g-4">
        <div class="col-12 col-xl-8">
            <div class="card fm-card mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start gap-3 mb-4">
                        <div class="fm-contact-avatar">
                            <?php echo e(strtoupper(substr($contact->first_name, 0, 1))); ?><?php echo e(strtoupper(substr($contact->last_name, 0, 1))); ?>

                        </div>
                        <div class="min-w-0">
                            <h2 class="h4 mb-1"><?php echo e($contact->full_name); ?></h2>
                            <?php if($contact->job_title): ?>
                                <div class="text-secondary"><?php echo e($contact->job_title); ?></div>
                            <?php endif; ?>
                            <div class="mt-2 d-flex flex-wrap gap-1">
                                <?php if($contact->is_primary): ?>
                                    <span class="badge text-bg-primary"><?php echo e(__('Primary contact')); ?></span>
                                <?php endif; ?>
                                <?php if($contact->department): ?>
                                    <span class="badge text-bg-light border"><?php echo e($contact->department); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row g-4">
                        <div class="col-12 col-md-6"><div class="small text-secondary"><?php echo e(__('Email')); ?></div><div class="fw-semibold"><?php if($contact->email): ?><a href="mailto:<?php echo e($contact->email); ?>"><?php echo e($contact->email); ?></a><?php else: ?>—<?php endif; ?></div></div>
                        <div class="col-12 col-md-6"><div class="small text-secondary"><?php echo e(__('Department')); ?></div><div class="fw-semibold"><?php echo e($contact->department ?: '—'); ?></div></div>
                        <div class="col-12 col-md-6"><div class="small text-secondary"><?php echo e(__('Phone')); ?></div><div class="fw-semibold"><?php echo e($contact->phone ?: '—'); ?></div></div>
                        <div class="col-12 col-md-6"><div class="small text-secondary"><?php echo e(__('Mobile')); ?></div><div class="fw-semibold"><?php echo e($contact->mobile ?: '—'); ?></div></div>
                        <?php if($contact->notes): ?>
                            <div class="col-12"><div class="small text-secondary mb-1"><?php echo e(__('Notes')); ?></div><div class="border rounded p-3 bg-light text-break"><?php echo nl2br(e($contact->notes)); ?></div></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('viewAny', App\Models\Project::class)): ?>
                <div class="card fm-card mb-4">
                    <div class="card-header fm-card-header">
                        <div><h2 class="fm-card-title"><?php echo e(__('Projects')); ?></h2><p class="fm-card-subtitle"><?php echo e(__('Projects where this person is the reference contact')); ?></p></div>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0 fm-table">
                            <thead><tr><th><?php echo e(__('Project')); ?></th><th><?php echo e(__('Company')); ?></th><th><?php echo e(__('Status')); ?></th><th><?php echo e(__('Manager')); ?></th><th><?php echo e(__('Tasks')); ?></th></tr></thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><a href="<?php echo e(route('projects.show', $project)); ?>" class="fw-semibold text-dark"><?php echo e($project->name); ?></a><div class="small text-secondary"><?php echo e($project->code); ?></div></td>
                                        <td><?php echo e($project->company->name); ?></td>
                                        <td><?php echo e($project->status->label()); ?></td>
                                        <td><?php echo e($project->manager?->name ?: '—'); ?></td>
                                        <td><?php echo e($project->tasks_count); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr><td colspan="5" class="text-center py-4 text-secondary"><?php echo e(__('No projects use this contact as a reference.')); ?></td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('viewAny', App\Models\Ticket::class)): ?>
                <div class="card fm-card">
                    <div class="card-header fm-card-header">
                        <div><h2 class="fm-card-title"><?php echo e(__('Tickets')); ?></h2><p class="fm-card-subtitle"><?php echo e(__('Support history associated with this contact')); ?></p></div>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0 fm-table">
                            <thead><tr><th><?php echo e(__('Ticket')); ?></th><th><?php echo e(__('Company')); ?></th><th><?php echo e(__('Status')); ?></th><th><?php echo e(__('Priority')); ?></th><th><?php echo e(__('Operator')); ?></th></tr></thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $tickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><a href="<?php echo e(route('tickets.show', $ticket)); ?>" class="fw-semibold text-dark"><?php echo e($ticket->subject); ?></a><div class="small text-secondary"><?php echo e($ticket->reference); ?></div></td>
                                        <td><?php echo e($ticket->company?->name ?: '—'); ?></td>
                                        <td><?php echo e($ticket->status->label()); ?></td>
                                        <td><?php echo e($ticket->priority->label()); ?></td>
                                        <td><?php echo e($ticket->assignee?->name ?: '—'); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr><td colspan="5" class="text-center py-4 text-secondary"><?php echo e(__('No tickets associated with this contact.')); ?></td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-12 col-xl-4">
            <div class="card fm-card mb-4">
                <div class="card-header fm-card-header"><h2 class="fm-card-title"><?php echo e(__('Company')); ?></h2></div>
                <div class="card-body p-4">
                    <?php if($contact->company): ?>
                        <div class="d-flex align-items-center gap-3">
                            <div class="fm-small-avatar"><?php echo e(strtoupper(substr($contact->company->name, 0, 1))); ?></div>
                            <div class="min-w-0">
                                <a href="<?php echo e(route('companies.show', $contact->company)); ?>" class="fw-semibold text-dark"><?php echo e($contact->company->name); ?></a>
                                <?php if($contact->company->industry): ?>
                                    <div class="small text-secondary"><?php echo e($contact->company->industry); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-secondary"><?php echo e(__('This contact is not associated with a company.')); ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card fm-card">
                <div class="card-body">
                    <h2 class="fm-card-title mb-3"><?php echo e(__('Record information')); ?></h2>
                    <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-secondary"><?php echo e(__('Created by')); ?></span><strong><?php echo e($contact->creator?->name ?: __('System')); ?></strong></div>
                    <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-secondary"><?php echo e(__('Created')); ?></span><strong><?php echo e($contact->created_at->format('d/m/Y H:i')); ?></strong></div>
                    <div class="d-flex justify-content-between pt-2"><span class="text-secondary"><?php echo e(__('Updated')); ?></span><strong><?php echo e($contact->updated_at->format('d/m/Y H:i')); ?></strong></div>
                </div>
            </div>
        </div>
    </div>

    <?php echo $__env->make('partials.collaboration-panel', ['collaborationTarget' => $contact], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Projects\flowmanager\resources\views/contacts/show.blade.php ENDPATH**/ ?>