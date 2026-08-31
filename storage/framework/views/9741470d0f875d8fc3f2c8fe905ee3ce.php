<?php $__env->startSection('title', $company->name); ?>
<?php $__env->startSection('page-title', $company->name); ?>
<?php $__env->startSection('page-subtitle', __('Company workspace and related operational records')); ?>

<?php $__env->startSection('content'); ?>
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <a href="<?php echo e(route('companies.index')); ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            <?php echo e(__('Companies')); ?>

        </a>

        <div class="d-flex flex-wrap gap-2">
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\Contact::class)): ?>
                <a href="<?php echo e(route('contacts.create', ['company' => $company->id])); ?>" class="btn btn-outline-primary">
                    <i class="bi bi-person-plus me-1"></i>
                    <?php echo e(__('Contact')); ?>

                </a>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\Project::class)): ?>
                <a href="<?php echo e(route('projects.create', ['company' => $company->id])); ?>" class="btn btn-outline-primary">
                    <i class="bi bi-kanban me-1"></i>
                    <?php echo e(__('Project')); ?>

                </a>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\Ticket::class)): ?>
                <a href="<?php echo e(route('tickets.create', ['company' => $company->id])); ?>" class="btn btn-outline-primary">
                    <i class="bi bi-ticket-perforated me-1"></i>
                    <?php echo e(__('Ticket')); ?>

                </a>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $company)): ?>
                <a href="<?php echo e(route('companies.edit', $company)); ?>" class="btn btn-primary">
                    <i class="bi bi-pencil me-1"></i>
                    <?php echo e(__('Edit')); ?>

                </a>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $company)): ?>
                <form method="POST" action="<?php echo e(route('companies.destroy', $company)); ?>" onsubmit="return confirm(<?php echo \Illuminate\Support\Js::from(__('Delete this company?'))->toHtml() ?>);">
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
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('viewAny', App\Models\Contact::class)): ?>
            <div class="col-6 col-lg-3">
                <a href="<?php echo e(route('contacts.index', ['company_id' => $company->id])); ?>" class="card fm-card fm-related-stat text-reset h-100">
                    <div class="card-body">
                        <div class="small text-secondary"><?php echo e(__('Contacts')); ?></div>
                        <div class="h4 mb-0 mt-1"><?php echo e($relatedCounts['contacts']); ?></div>
                    </div>
                </a>
            </div>
        <?php endif; ?>

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('viewAny', App\Models\Project::class)): ?>
            <div class="col-6 col-lg-3">
                <a href="<?php echo e(route('projects.index', ['company_id' => $company->id])); ?>" class="card fm-card fm-related-stat text-reset h-100">
                    <div class="card-body">
                        <div class="small text-secondary"><?php echo e(__('Projects')); ?></div>
                        <div class="h4 mb-0 mt-1"><?php echo e($relatedCounts['projects']); ?></div>
                    </div>
                </a>
            </div>
        <?php endif; ?>

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('viewAny', App\Models\Asset::class)): ?>
            <div class="col-6 col-lg-3">
                <a href="<?php echo e(route('assets.index', ['company_id' => $company->id])); ?>" class="card fm-card fm-related-stat text-reset h-100">
                    <div class="card-body">
                        <div class="small text-secondary"><?php echo e(__('Assets')); ?></div>
                        <div class="h4 mb-0 mt-1"><?php echo e($relatedCounts['assets']); ?></div>
                    </div>
                </a>
            </div>
        <?php endif; ?>

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('viewAny', App\Models\Ticket::class)): ?>
            <div class="col-6 col-lg-3">
                <a href="<?php echo e(route('tickets.index', ['company_id' => $company->id])); ?>" class="card fm-card fm-related-stat text-reset h-100">
                    <div class="card-body">
                        <div class="small text-secondary"><?php echo e(__('Tickets')); ?></div>
                        <div class="h4 mb-0 mt-1"><?php echo e($relatedCounts['tickets']); ?></div>
                    </div>
                </a>
            </div>
        <?php endif; ?>
    </div>

    <div class="row g-4">
        <div class="col-12 col-xl-8">
            <div class="card fm-card mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start gap-3 mb-4">
                        <div class="fm-company-avatar"><?php echo e(strtoupper(substr($company->name, 0, 1))); ?></div>
                        <div class="min-w-0">
                            <h2 class="h4 mb-1"><?php echo e($company->name); ?></h2>
                            <?php if($company->legal_name): ?>
                                <div class="text-secondary"><?php echo e($company->legal_name); ?></div>
                            <?php endif; ?>
                            <div class="mt-2 d-flex flex-wrap gap-1">
                                <span class="badge text-bg-light border"><?php echo e($company->type->label()); ?></span>
                                <?php switch($company->status->value):
                                    case ('active'): ?>
                                        <span class="badge text-bg-success"><?php echo e($company->status->label()); ?></span>
                                        <?php break; ?>
                                    <?php case ('prospect'): ?>
                                        <span class="badge text-bg-primary"><?php echo e($company->status->label()); ?></span>
                                        <?php break; ?>
                                    <?php case ('suspended'): ?>
                                        <span class="badge text-bg-warning"><?php echo e($company->status->label()); ?></span>
                                        <?php break; ?>
                                    <?php default: ?>
                                        <span class="badge text-bg-secondary"><?php echo e($company->status->label()); ?></span>
                                <?php endswitch; ?>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row g-4">
                        <div class="col-12 col-md-6"><div class="small text-secondary"><?php echo e(__('Industry')); ?></div><div class="fw-semibold"><?php echo e($company->industry ?: '—'); ?></div></div>
                        <div class="col-12 col-md-6"><div class="small text-secondary"><?php echo e(__('Employees')); ?></div><div class="fw-semibold"><?php echo e($company->employees !== null ? number_format($company->employees) : '—'); ?></div></div>
                        <div class="col-12 col-md-6"><div class="small text-secondary"><?php echo e(__('VAT number')); ?></div><div class="fw-semibold"><?php echo e($company->vat_number ?: '—'); ?></div></div>
                        <div class="col-12 col-md-6"><div class="small text-secondary"><?php echo e(__('Tax code')); ?></div><div class="fw-semibold"><?php echo e($company->tax_code ?: '—'); ?></div></div>
                        <div class="col-12 col-md-6"><div class="small text-secondary"><?php echo e(__('Email')); ?></div><div class="fw-semibold"><?php if($company->email): ?><a href="mailto:<?php echo e($company->email); ?>"><?php echo e($company->email); ?></a><?php else: ?>—<?php endif; ?></div></div>
                        <div class="col-12 col-md-6"><div class="small text-secondary"><?php echo e(__('Phone')); ?></div><div class="fw-semibold"><?php echo e($company->phone ?: '—'); ?></div></div>
                        <div class="col-12"><div class="small text-secondary"><?php echo e(__('Address')); ?></div><div class="fw-semibold"><?php echo e(collect([$company->address, $company->postal_code, $company->city, $company->province, $company->country_code])->filter()->implode(', ') ?: '—'); ?></div></div>
                        <?php if($company->notes): ?>
                            <div class="col-12"><div class="small text-secondary mb-1"><?php echo e(__('Notes')); ?></div><div class="border rounded p-3 bg-light text-break"><?php echo nl2br(e($company->notes)); ?></div></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('viewAny', App\Models\Project::class)): ?>
                <div class="card fm-card mb-4">
                    <div class="card-header fm-card-header">
                        <div><h2 class="fm-card-title"><?php echo e(__('Recent projects')); ?></h2><p class="fm-card-subtitle"><?php echo e(__('Delivery linked to this company')); ?></p></div>
                        <a href="<?php echo e(route('projects.index', ['company_id' => $company->id])); ?>" class="btn btn-sm btn-outline-secondary"><?php echo e(__('View all')); ?></a>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0 fm-table">
                            <thead><tr><th><?php echo e(__('Project')); ?></th><th><?php echo e(__('Status')); ?></th><th><?php echo e(__('Manager')); ?></th><th><?php echo e(__('Tasks')); ?></th><th><?php echo e(__('Due')); ?></th></tr></thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><a href="<?php echo e(route('projects.show', $project)); ?>" class="fw-semibold text-dark"><?php echo e($project->name); ?></a><div class="small text-secondary"><?php echo e($project->code); ?></div></td>
                                        <td><?php echo e($project->status->label()); ?></td>
                                        <td><?php echo e($project->manager?->name ?: '—'); ?></td>
                                        <td><?php echo e($project->tasks_count); ?></td>
                                        <td><?php echo e($project->due_date?->format('d/m/Y') ?: '—'); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr><td colspan="5" class="text-center py-4 text-secondary"><?php echo e(__('No projects linked to this company.')); ?></td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('viewAny', App\Models\Ticket::class)): ?>
                <div class="card fm-card">
                    <div class="card-header fm-card-header">
                        <div><h2 class="fm-card-title"><?php echo e(__('Recent tickets')); ?></h2><p class="fm-card-subtitle"><?php echo e(__('Support activity for this company')); ?></p></div>
                        <a href="<?php echo e(route('tickets.index', ['company_id' => $company->id])); ?>" class="btn btn-sm btn-outline-secondary"><?php echo e(__('View all')); ?></a>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0 fm-table">
                            <thead><tr><th><?php echo e(__('Ticket')); ?></th><th><?php echo e(__('Status')); ?></th><th><?php echo e(__('Priority')); ?></th><th><?php echo e(__('Operator')); ?></th></tr></thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $tickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><a href="<?php echo e(route('tickets.show', $ticket)); ?>" class="fw-semibold text-dark"><?php echo e($ticket->subject); ?></a><div class="small text-secondary"><?php echo e($ticket->reference); ?></div></td>
                                        <td><?php echo e($ticket->status->label()); ?></td>
                                        <td><?php echo e($ticket->priority->label()); ?></td>
                                        <td><?php echo e($ticket->assignee?->name ?: '—'); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr><td colspan="4" class="text-center py-4 text-secondary"><?php echo e(__('No tickets linked to this company.')); ?></td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-12 col-xl-4">
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('viewAny', App\Models\Contact::class)): ?>
                <div class="card fm-card mb-4">
                    <div class="card-header fm-card-header">
                        <div><h2 class="fm-card-title"><?php echo e(__('Contacts')); ?></h2><p class="fm-card-subtitle"><?php echo e(__('People associated with this company')); ?></p></div>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\Contact::class)): ?>
                            <a href="<?php echo e(route('contacts.create', ['company' => $company->id])); ?>" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg"></i></a>
                        <?php endif; ?>
                    </div>
                    <div class="list-group list-group-flush">
                        <?php $__empty_1 = true; $__currentLoopData = $contacts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $contact): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <a href="<?php echo e(route('contacts.show', $contact)); ?>" class="list-group-item list-group-item-action fm-related-list-item">
                                <div class="min-w-0">
                                    <div class="fw-semibold"><?php echo e($contact->full_name); ?> <?php if($contact->is_primary): ?><span class="badge text-bg-primary ms-1"><?php echo e(__('Primary')); ?></span><?php endif; ?></div>
                                    <div class="small text-secondary"><?php echo e($contact->job_title ?: ($contact->email ?: __('No role specified'))); ?></div>
                                </div>
                                <i class="bi bi-chevron-right text-secondary"></i>
                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="p-4 text-center text-secondary"><?php echo e(__('No contacts linked to this company.')); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('viewAny', App\Models\Asset::class)): ?>
                <div class="card fm-card mb-4">
                    <div class="card-header fm-card-header">
                        <div><h2 class="fm-card-title"><?php echo e(__('Assets')); ?></h2><p class="fm-card-subtitle"><?php echo e(__('Equipment associated with this company')); ?></p></div>
                        <div class="d-flex gap-2">
                            <a href="<?php echo e(route('assets.index', ['company_id' => $company->id])); ?>" class="btn btn-sm btn-outline-secondary"><?php echo e(__('All')); ?></a>
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\Asset::class)): ?>
                                <a href="<?php echo e(route('assets.create', ['company' => $company->id])); ?>" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg"></i></a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="list-group list-group-flush">
                        <?php $__empty_1 = true; $__currentLoopData = $assets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $asset): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <a href="<?php echo e(route('assets.show', $asset)); ?>" class="list-group-item list-group-item-action fm-related-list-item">
                                <div class="min-w-0"><div class="fw-semibold"><?php echo e($asset->name); ?></div><div class="small text-secondary"><?php echo e($asset->asset_tag); ?> · <?php echo e($asset->status->label()); ?></div></div>
                                <i class="bi bi-chevron-right text-secondary"></i>
                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="p-4 text-center text-secondary"><?php echo e(__('No assets linked to this company.')); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="card fm-card">
                <div class="card-body">
                    <h2 class="fm-card-title mb-3"><?php echo e(__('Record information')); ?></h2>
                    <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-secondary"><?php echo e(__('Created by')); ?></span><strong><?php echo e($company->creator?->name ?: __('System')); ?></strong></div>
                    <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-secondary"><?php echo e(__('Created')); ?></span><strong><?php echo e($company->created_at->format('d/m/Y H:i')); ?></strong></div>
                    <div class="d-flex justify-content-between pt-2"><span class="text-secondary"><?php echo e(__('Updated')); ?></span><strong><?php echo e($company->updated_at->format('d/m/Y H:i')); ?></strong></div>
                </div>
            </div>
        </div>
    </div>

    <?php echo $__env->make('partials.collaboration-panel', ['collaborationTarget' => $company], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Projects\flowmanager\resources\views/companies/show.blade.php ENDPATH**/ ?>