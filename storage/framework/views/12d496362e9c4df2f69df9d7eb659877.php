<?php $__env->startSection('title', __('Companies')); ?>

<?php $__env->startSection('page-title', __('Companies')); ?>

<?php $__env->startSection('page-subtitle', __('Manage customers, suppliers, partners and prospects')); ?>

<?php $__env->startSection('content'); ?>

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <span class="text-secondary">
                <?php echo e(trans_choice('ui.counts.companies', $companies->total(), ['count' => $companies->total()])); ?>

            </span>
        </div>

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\Company::class)): ?>

            <a
                href="<?php echo e(route('companies.create')); ?>"
                class="btn btn-primary"
            >
                <i class="bi bi-plus-lg me-1"></i>
                <?php echo e(__('New company')); ?>

            </a>

        <?php endif; ?>

    </div>

    <div class="card fm-card mb-4">

        <div class="card-body p-4">

            <form
                method="GET"
                action="<?php echo e(route('companies.index')); ?>"
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
                                placeholder="<?php echo e(__('Name, VAT, email, city...')); ?>"
                            >

                        </div>

                    </div>

                    <div class="col-12 col-md-6 col-xl-2">

                        <label
                            for="type"
                            class="form-label fw-semibold"
                        >
                            <?php echo e(__('Type')); ?>

                        </label>

                        <select
                            id="type"
                            name="type"
                            class="form-select"
                        >

                            <option value="">
                                <?php echo e(__('All types')); ?>

                            </option>

                            <?php $__currentLoopData = $types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                                <option
                                    value="<?php echo e($type->value); ?>"
                                    <?php if(
                                        $filters['type'] === $type->value
                                    ): echo 'selected'; endif; ?>
                                >
                                    <?php echo e($type->label()); ?>

                                </option>

                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                        </select>

                    </div>

                    <div class="col-12 col-md-6 col-xl-2">

                        <label
                            for="status"
                            class="form-label fw-semibold"
                        >
                            <?php echo e(__('Status')); ?>

                        </label>

                        <select
                            id="status"
                            name="status"
                            class="form-select"
                        >

                            <option value="">
                                <?php echo e(__('All statuses')); ?>

                            </option>

                            <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                                <option
                                    value="<?php echo e($status->value); ?>"
                                    <?php if(
                                        $filters['status'] === $status->value
                                    ): echo 'selected'; endif; ?>
                                >
                                    <?php echo e($status->label()); ?>

                                </option>

                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

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
                                value="name"
                                <?php if($filters['sort'] === 'name'): echo 'selected'; endif; ?>
                            >
                                <?php echo e(__('Name')); ?>

                            </option>

                            <option
                                value="type"
                                <?php if($filters['sort'] === 'type'): echo 'selected'; endif; ?>
                            >
                                <?php echo e(__('Type')); ?>

                            </option>

                            <option
                                value="status"
                                <?php if($filters['sort'] === 'status'): echo 'selected'; endif; ?>
                            >
                                <?php echo e(__('Status')); ?>

                            </option>

                            <option
                                value="city"
                                <?php if($filters['sort'] === 'city'): echo 'selected'; endif; ?>
                            >
                                <?php echo e(__('City')); ?>

                            </option>

                            <option
                                value="industry"
                                <?php if($filters['sort'] === 'industry'): echo 'selected'; endif; ?>
                            >
                                <?php echo e(__('Industry')); ?>

                            </option>

                            <option
                                value="created_at"
                                <?php if($filters['sort'] === 'created_at'): echo 'selected'; endif; ?>
                            >
                                <?php echo e(__('Created')); ?>

                            </option>

                        </select>

                    </div>

                    <div class="col-12 col-md-6 col-xl-2">

                        <label
                            for="direction"
                            class="form-label fw-semibold"
                        >
                            <?php echo e(__('Direction')); ?>

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
                                <?php echo e(__('Ascending')); ?>

                            </option>

                            <option
                                value="desc"
                                <?php if($filters['direction'] === 'desc'): echo 'selected'; endif; ?>
                            >
                                <?php echo e(__('Descending')); ?>

                            </option>

                        </select>

                    </div>

                </div>

                <div class="d-flex justify-content-end gap-2 mt-3">

                    <a
                        href="<?php echo e(route('companies.index')); ?>"
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
                        <th><?php echo e(__('Company')); ?></th>
                        <th><?php echo e(__('Type')); ?></th>
                        <th><?php echo e(__('Status')); ?></th>
                        <th><?php echo e(__('Industry')); ?></th>
                        <th><?php echo e(__('Location')); ?></th>
                        <th><?php echo e(__('Contact')); ?></th>
                        <th class="text-end"><?php echo e(__('Actions')); ?></th>
                    </tr>
                </thead>

                <tbody>

                    <?php $__empty_1 = true; $__currentLoopData = $companies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $company): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                        <tr>

                            <td>

                                <a
                                    href="<?php echo e(route('companies.show', $company)); ?>"
                                    class="fw-semibold text-dark"
                                >
                                    <?php echo e($company->name); ?>

                                </a>

                                <?php if($company->legal_name): ?>

                                    <div class="small text-secondary">
                                        <?php echo e($company->legal_name); ?>

                                    </div>

                                <?php endif; ?>

                                <?php if($company->vat_number): ?>

                                    <div class="small text-secondary">
                                        VAT <?php echo e($company->vat_number); ?>

                                    </div>

                                <?php endif; ?>

                            </td>

                            <td>
                                <?php echo e($company->type->label()); ?>

                            </td>

                            <td>

                                <?php switch($company->status->value):

                                    case ('active'): ?>
                                        <span class="badge text-bg-success">
                                            <?php echo e($company->status->label()); ?>

                                        </span>
                                        <?php break; ?>

                                    <?php case ('prospect'): ?>
                                        <span class="badge text-bg-primary">
                                            <?php echo e($company->status->label()); ?>

                                        </span>
                                        <?php break; ?>

                                    <?php case ('suspended'): ?>
                                        <span class="badge text-bg-warning">
                                            <?php echo e($company->status->label()); ?>

                                        </span>
                                        <?php break; ?>

                                    <?php default: ?>
                                        <span class="badge text-bg-secondary">
                                            <?php echo e($company->status->label()); ?>

                                        </span>

                                <?php endswitch; ?>

                            </td>

                            <td>
                                <?php echo e($company->industry ?: '—'); ?>

                            </td>

                            <td>

                                <?php echo e($company->city ?: '—'); ?>


                                <?php if($company->country_code): ?>

                                    <div class="small text-secondary">
                                        <?php echo e($company->country_code); ?>

                                    </div>

                                <?php endif; ?>

                            </td>

                            <td>

                                <?php if($company->email): ?>

                                    <div>
                                        <?php echo e($company->email); ?>

                                    </div>

                                <?php endif; ?>

                                <?php if($company->phone): ?>

                                    <div class="small text-secondary">
                                        <?php echo e($company->phone); ?>

                                    </div>

                                <?php endif; ?>

                                <?php if(! $company->email && ! $company->phone): ?>
                                    —
                                <?php endif; ?>

                            </td>

                            <td class="text-end">

                                <div class="btn-group">

                                    <a
                                        href="<?php echo e(route('companies.show', $company)); ?>"
                                        class="btn btn-sm btn-outline-secondary"
                                        title="<?php echo e(__('View')); ?>"
                                    >
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $company)): ?>

                                        <a
                                            href="<?php echo e(route('companies.edit', $company)); ?>"
                                            class="btn btn-sm btn-outline-secondary"
                                            title="<?php echo e(__('Edit')); ?>"
                                        >
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                    <?php endif; ?>

                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $company)): ?>

                                        <form
                                            method="POST"
                                            action="<?php echo e(route('companies.destroy', $company)); ?>"
                                            onsubmit="return confirm(<?php echo \Illuminate\Support\Js::from(__('Delete this company?'))->toHtml() ?>);"
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

                                <i class="bi bi-buildings fs-1 text-secondary"></i>

                                <div class="fw-semibold mt-3">
                                    <?php echo e(__('No companies found')); ?>

                                </div>

                                <div class="text-secondary">
                                    <?php echo e(__('Try changing the search filters or create a new company.')); ?>

                                </div>

                            </td>

                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

        <?php if($companies->hasPages()): ?>

            <div class="card-footer bg-white p-3">

                <?php echo e($companies->links()); ?>


            </div>

        <?php endif; ?>

    </div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Projects\flowmanager\resources\views/companies/index.blade.php ENDPATH**/ ?>