<?php $__env->startSection('title', __('Contacts')); ?>

<?php $__env->startSection('page-title', __('Contacts')); ?>

<?php $__env->startSection('page-subtitle', __('Manage people and company relationships')); ?>

<?php $__env->startSection('content'); ?>

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <span class="text-secondary">
                <?php echo e(trans_choice('ui.counts.contacts', $contacts->total(), ['count' => $contacts->total()])); ?>

            </span>
        </div>

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\Contact::class)): ?>

            <a
                href="<?php echo e(route('contacts.create')); ?>"
                class="btn btn-primary"
            >
                <i class="bi bi-plus-lg me-1"></i>
                <?php echo e(__('New contact')); ?>

            </a>

        <?php endif; ?>

    </div>

    <div class="card fm-card mb-4">

        <div class="card-body p-4">

            <form
                method="GET"
                action="<?php echo e(route('contacts.index')); ?>"
            >

                <div class="row g-3">

                    <div class="col-12 col-xl-4">

                        <label
                            for="search"
                            class="form-label fw-semibold"
                        >
                            <?php echo e(__('Search')); ?>

                        </label>

                        <div class="input-group">

                            <span class="input-group-text">
                                <i class="bi bi-search"></i>
                            </span>

                            <input
                                type="search"
                                id="search"
                                name="search"
                                value="<?php echo e($filters['search']); ?>"
                                class="form-control"
                                placeholder="<?php echo e(__('Name, company, email, role...')); ?>"
                            >

                        </div>

                    </div>

                    <div class="col-12 col-md-6 col-xl-3">

                        <label
                            for="company_id"
                            class="form-label fw-semibold"
                        >
                            <?php echo e(__('Company')); ?>

                        </label>

                        <select
                            id="company_id"
                            name="company_id"
                            class="form-select"
                        >

                            <option value="">
                                <?php echo e(__('All companies')); ?>

                            </option>

                            <?php $__currentLoopData = $companies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $company): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                                <option
                                    value="<?php echo e($company->id); ?>"
                                    <?php if(
                                        $filters['company_id'] === (string) $company->id
                                    ): echo 'selected'; endif; ?>
                                >
                                    <?php echo e($company->name); ?>

                                </option>

                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                        </select>

                    </div>

                    <div class="col-12 col-md-6 col-xl-2">

                        <label
                            for="primary"
                            class="form-label fw-semibold"
                        >
                            <?php echo e(__('Contact type')); ?>

                        </label>

                        <select
                            id="primary"
                            name="primary"
                            class="form-select"
                        >

                            <option value="">
                                <?php echo e(__('All contacts')); ?>

                            </option>

                            <option
                                value="1"
                                <?php if($filters['primary'] === '1'): echo 'selected'; endif; ?>
                            >
                                <?php echo e(__('Primary only')); ?>

                            </option>

                            <option
                                value="0"
                                <?php if($filters['primary'] === '0'): echo 'selected'; endif; ?>
                            >
                                <?php echo e(__('Non-primary')); ?>

                            </option>

                        </select>

                    </div>

                    <div class="col-12 col-md-6 col-xl-2">

                        <label
                            for="sort"
                            class="form-label fw-semibold"
                        >
                            <?php echo e(__('Sort by')); ?>

                        </label>

                        <select
                            id="sort"
                            name="sort"
                            class="form-select"
                        >

                            <option
                                value="last_name"
                                <?php if($filters['sort'] === 'last_name'): echo 'selected'; endif; ?>
                            >
                                <?php echo e(__('Last name')); ?>

                            </option>

                            <option
                                value="first_name"
                                <?php if($filters['sort'] === 'first_name'): echo 'selected'; endif; ?>
                            >
                                <?php echo e(__('First name')); ?>

                            </option>

                            <option
                                value="job_title"
                                <?php if($filters['sort'] === 'job_title'): echo 'selected'; endif; ?>
                            >
                                <?php echo e(__('Job title')); ?>

                            </option>

                            <option
                                value="created_at"
                                <?php if($filters['sort'] === 'created_at'): echo 'selected'; endif; ?>
                            >
                                <?php echo e(__('Created')); ?>

                            </option>

                        </select>

                    </div>

                    <div class="col-12 col-md-6 col-xl-1">

                        <label
                            for="direction"
                            class="form-label fw-semibold"
                        >
                            <?php echo e(__('Order')); ?>

                        </label>

                        <select
                            id="direction"
                            name="direction"
                            class="form-select"
                        >

                            <option
                                value="asc"
                                <?php if($filters['direction'] === 'asc'): echo 'selected'; endif; ?>
                            >
                                <?php echo e(__('A-Z')); ?>

                            </option>

                            <option
                                value="desc"
                                <?php if($filters['direction'] === 'desc'): echo 'selected'; endif; ?>
                            >
                                <?php echo e(__('Z-A')); ?>

                            </option>

                        </select>

                    </div>

                </div>

                <div class="d-flex justify-content-end gap-2 mt-3">

                    <a
                        href="<?php echo e(route('contacts.index')); ?>"
                        class="btn btn-outline-secondary"
                    >
                        <?php echo e(__('Reset')); ?>

                    </a>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        <?php echo e(__('Apply filters')); ?>

                    </button>

                </div>

            </form>

        </div>

    </div>

    <div class="card fm-card">

        <div class="table-responsive">

            <table class="table align-middle mb-0 fm-table">

                <thead>
                    <tr>
                        <th><?php echo e(__('Contact')); ?></th>
                        <th><?php echo e(__('Company')); ?></th>
                        <th><?php echo e(__('Role')); ?></th>
                        <th><?php echo e(__('Email')); ?></th>
                        <th><?php echo e(__('Phone')); ?></th>
                        <th><?php echo e(__('Type')); ?></th>
                        <th class="text-end"><?php echo e(__('Actions')); ?></th>
                    </tr>
                </thead>

                <tbody>

                    <?php $__empty_1 = true; $__currentLoopData = $contacts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $contact): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                        <tr>

                            <td>

                                <a
                                    href="<?php echo e(route('contacts.show', $contact)); ?>"
                                    class="fw-semibold text-dark"
                                >
                                    <?php echo e($contact->full_name); ?>

                                </a>

                                <?php if($contact->department): ?>

                                    <div class="small text-secondary">
                                        <?php echo e($contact->department); ?>

                                    </div>

                                <?php endif; ?>

                            </td>

                            <td>

                                <?php if($contact->company): ?>

                                    <a
                                        href="<?php echo e(route('companies.show', $contact->company)); ?>"
                                        class="text-decoration-none"
                                    >
                                        <?php echo e($contact->company->name); ?>

                                    </a>

                                <?php else: ?>
                                    —
                                <?php endif; ?>

                            </td>

                            <td>
                                <?php echo e($contact->job_title ?: '—'); ?>

                            </td>

                            <td>

                                <?php if($contact->email): ?>

                                    <a href="mailto:<?php echo e($contact->email); ?>">
                                        <?php echo e($contact->email); ?>

                                    </a>

                                <?php else: ?>
                                    —
                                <?php endif; ?>

                            </td>

                            <td>

                                <?php if($contact->mobile): ?>

                                    <div>
                                        <?php echo e($contact->mobile); ?>

                                    </div>

                                    <?php if($contact->phone): ?>
                                        <div class="small text-secondary">
                                            <?php echo e($contact->phone); ?>

                                        </div>
                                    <?php endif; ?>

                                <?php elseif($contact->phone): ?>
                                    <?php echo e($contact->phone); ?>

                                <?php else: ?>
                                    —
                                <?php endif; ?>

                            </td>

                            <td>

                                <?php if($contact->is_primary): ?>

                                    <span class="badge text-bg-primary">
                                        <?php echo e(__('Primary')); ?>

                                    </span>

                                <?php else: ?>

                                    <span class="badge text-bg-light border text-secondary">
                                        <?php echo e(__('Standard')); ?>

                                    </span>

                                <?php endif; ?>

                            </td>

                            <td class="text-end">

                                <div class="btn-group">

                                    <a
                                        href="<?php echo e(route('contacts.show', $contact)); ?>"
                                        class="btn btn-sm btn-outline-secondary"
                                        title="<?php echo e(__('View')); ?>"
                                    >
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $contact)): ?>

                                        <a
                                            href="<?php echo e(route('contacts.edit', $contact)); ?>"
                                            class="btn btn-sm btn-outline-secondary"
                                            title="<?php echo e(__('Edit')); ?>"
                                        >
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                    <?php endif; ?>

                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $contact)): ?>

                                        <form
                                            method="POST"
                                            action="<?php echo e(route('contacts.destroy', $contact)); ?>"
                                            onsubmit="return confirm(<?php echo \Illuminate\Support\Js::from(__('Delete this contact?'))->toHtml() ?>);"
                                        >
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>

                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-outline-danger rounded-start-0"
                                                title="<?php echo e(__('Delete')); ?>"
                                            >
                                                <i class="bi bi-trash"></i>
                                            </button>

                                        </form>

                                    <?php endif; ?>

                                </div>

                            </td>

                        </tr>

                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                        <tr>

                            <td
                                colspan="7"
                                class="text-center py-5"
                            >

                                <i class="bi bi-person-vcard fs-1 text-secondary"></i>

                                <div class="fw-semibold mt-3">
                                    <?php echo e(__('No contacts found')); ?>

                                </div>

                                <div class="text-secondary">
                                    <?php echo e(__('Try changing the search filters or create a new contact.')); ?>

                                </div>

                            </td>

                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

        <?php if($contacts->hasPages()): ?>

            <div class="card-footer bg-white p-3">

                <?php echo e($contacts->links()); ?>


            </div>

        <?php endif; ?>

    </div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Projects\flowmanager\resources\views/contacts/index.blade.php ENDPATH**/ ?>