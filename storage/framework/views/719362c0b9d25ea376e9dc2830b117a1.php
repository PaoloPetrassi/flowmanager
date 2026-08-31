<?php $__env->startSection('title', __('Verify email')); ?>
<?php $__env->startSection('page-title', __('Verify email')); ?>
<?php $__env->startSection('page-subtitle', __('Confirm ownership of your email address')); ?>

<?php $__env->startSection('content'); ?>
    <div class="card fm-card mx-auto" style="max-width: 720px;">
        <div class="card-body p-4 p-md-5 text-center">
            <div class="fm-security-hero-icon mx-auto mb-3"><i class="bi bi-envelope-check"></i></div>
            <h2 class="h4 mb-2"><?php echo e(__('Verify your email address')); ?></h2>
            <p class="text-secondary mb-4"><?php echo e(__('We will send a signed verification link to :email.', ['email' => auth()->user()->email])); ?></p>
            <form method="POST" action="<?php echo e(route('verification.send')); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit" class="btn btn-primary"><i class="bi bi-send me-1"></i><?php echo e(__('Send verification link')); ?></button>
            </form>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Projects\flowmanager\resources\views/auth/verify-email.blade.php ENDPATH**/ ?>