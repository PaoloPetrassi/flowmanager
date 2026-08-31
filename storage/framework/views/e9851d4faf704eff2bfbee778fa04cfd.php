<?php $__env->startSection('title', __('Reset password')); ?>

<?php $__env->startSection('content'); ?>
    <div class="card fm-login-card">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <div class="fm-brand-mark mx-auto mb-3">FM</div>
                <h1 class="fm-brand h3 mb-2"><?php echo e(__('Reset password')); ?></h1>
                <p class="text-secondary mb-0"><?php echo e(__('Enter your email address and we will send you a reset link.')); ?></p>
            </div>

            <?php if(session('status')): ?>
                <div class="alert alert-success"><?php echo e(session('status')); ?></div>
            <?php endif; ?>

            <form method="POST" action="<?php echo e(route('password.email')); ?>">
                <?php echo csrf_field(); ?>
                <div class="mb-4">
                    <label for="email" class="form-label fm-form-label"><?php echo e(__('Email address')); ?></label>
                    <input id="email" name="email" type="email" value="<?php echo e(old('email')); ?>" class="form-control fm-form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" autocomplete="email" autofocus required>
                    <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <button type="submit" class="btn btn-primary fm-btn-primary w-100"><?php echo e(__('Send reset link')); ?></button>
            </form>

            <div class="text-center mt-4">
                <a href="<?php echo e(route('login')); ?>" class="text-decoration-none"><?php echo e(__('Back to sign in')); ?></a>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.guest', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Projects\flowmanager\resources\views/auth/forgot-password.blade.php ENDPATH**/ ?>