<?php $__env->startSection('title', __('Sign in')); ?>

<?php $__env->startSection('content'); ?>

    <div class="card fm-login-card">
        <div class="card-body p-4 p-md-5">

            <div class="text-center mb-4">

                <div class="fm-brand-mark mx-auto mb-3">
                    FM
                </div>

                <h1 class="fm-brand h3 mb-2">
                    FlowManager
                </h1>

                <p class="text-secondary mb-0">
                    <?php echo e(__('Business Management Platform')); ?>

                </p>

            </div>

            <?php if(session('status')): ?>
                <div
                    class="alert alert-success"
                    role="alert"
                >
                    <?php echo e(session('status')); ?>

                </div>
            <?php endif; ?>

            <form
                method="POST"
                action="<?php echo e(route('login.attempt')); ?>"
                novalidate
            >
                <?php echo csrf_field(); ?>

                <div class="mb-3">

                    <label
                        for="email"
                        class="form-label fm-form-label"
                    >
                        <?php echo e(__('Email address')); ?>

                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?php echo e(old('email')); ?>"
                        class="form-control fm-form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                        autocomplete="email"
                        autofocus
                        required
                    >

                    <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="invalid-feedback">
                            <?php echo e($message); ?>

                        </div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                </div>

                <div class="mb-3">

                    <label
                        for="password"
                        class="form-label fm-form-label"
                    >
                        <?php echo e(__('Password')); ?>

                    </label>

                    <div class="input-group">

                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control fm-form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                            autocomplete="current-password"
                            required
                        >

                        <button
                            type="button"
                            class="btn btn-outline-secondary fm-password-toggle"
                            data-password-toggle
                            data-target="password"
                            data-label-show="<?php echo e(__('Show password')); ?>"
                            data-label-hide="<?php echo e(__('Hide password')); ?>"
                            aria-label="<?php echo e(__('Show password')); ?>"
                        >
                            <i class="bi bi-eye"></i>
                        </button>

                        <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback">
                                <?php echo e($message); ?>

                            </div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                    </div>

                </div>

                <div class="form-check mb-4">

                    <input
                        type="checkbox"
                        id="remember"
                        name="remember"
                        value="1"
                        class="form-check-input"
                        <?php echo e(old('remember') ? 'checked' : ''); ?>

                    >

                    <label
                        for="remember"
                        class="form-check-label"
                    >
                        <?php echo e(__('Remember me')); ?>

                    </label>

                </div>

                <button
                    type="submit"
                    class="btn btn-primary fm-btn-primary w-100"
                >
                    <?php echo e(__('Sign in')); ?>

                </button>

                <div class="text-center mt-3">
                    <a href="<?php echo e(route('password.request')); ?>" class="small text-decoration-none"><?php echo e(__('Forgot your password?')); ?></a>
                </div>

            </form>

        </div>
    </div>

    <div class="text-center mt-4 fm-login-footer">
        <?php echo e(__('FlowManager Portfolio Project')); ?>

    </div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.guest', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Projects\flowmanager\resources\views/auth/login.blade.php ENDPATH**/ ?>