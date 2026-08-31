<?php $__env->startSection('title', __('Security')); ?>
<?php $__env->startSection('page-title', __('Security')); ?>
<?php $__env->startSection('page-subtitle', __('Password, two-factor authentication and active sessions')); ?>

<?php $__env->startSection('content'); ?>
    <div class="row g-4">
        <div class="col-12 col-xl-7">
            <div class="card fm-card mb-4">
                <div class="card-header fm-card-header">
                    <div><h2 class="fm-card-title"><?php echo e(__('Account security')); ?></h2><p class="fm-card-subtitle"><?php echo e(__('Protect your FlowManager account')); ?></p></div>
                    <span class="badge <?php echo e($user->hasTwoFactorEnabled() ? 'text-bg-success' : 'text-bg-secondary'); ?>"><?php echo e($user->hasTwoFactorEnabled() ? __('2FA enabled') : __('2FA disabled')); ?></span>
                </div>
                <div class="card-body">
                    <div class="row g-4 align-items-start">
                        <div class="col-12 col-md-6">
                            <h3 class="h6 mb-3"><?php echo e(__('Change password')); ?></h3>
                            <form method="POST" action="<?php echo e(route('security.password')); ?>">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('PUT'); ?>
                                <div class="mb-3">
                                    <label class="form-label" for="current_password"><?php echo e(__('Current password')); ?></label>
                                    <input class="form-control <?php $__errorArgs = ['current_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" type="password" id="current_password" name="current_password" autocomplete="current-password" required>
                                    <?php $__errorArgs = ['current_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="password"><?php echo e(__('New password')); ?></label>
                                    <input class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" type="password" id="password" name="password" autocomplete="new-password" required>
                                    <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="password_confirmation"><?php echo e(__('Confirm password')); ?></label>
                                    <input class="form-control" type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" required>
                                </div>
                                <button class="btn btn-primary" type="submit"><i class="bi bi-key me-1"></i><?php echo e(__('Update password')); ?></button>
                            </form>
                        </div>

                        <div class="col-12 col-md-6">
                            <h3 class="h6 mb-3"><?php echo e(__('Two-factor authentication')); ?></h3>
                            <?php if($user->hasTwoFactorEnabled()): ?>
                                <div class="fm-security-status is-success mb-3"><i class="bi bi-shield-check"></i><div><strong><?php echo e(__('Your account is protected by 2FA.')); ?></strong><small><?php echo e(__('A 6-digit code will be required after your password.')); ?></small></div></div>
                                <form method="POST" action="<?php echo e(route('security.two-factor.disable')); ?>">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <div class="mb-3">
                                        <label class="form-label" for="disable_current_password"><?php echo e(__('Current password')); ?></label>
                                        <input class="form-control" type="password" id="disable_current_password" name="current_password" required>
                                    </div>
                                    <button class="btn btn-outline-danger" type="submit" data-confirm="<?php echo e(__('Disable two-factor authentication?')); ?>"><i class="bi bi-shield-x me-1"></i><?php echo e(__('Disable 2FA')); ?></button>
                                </form>
                            <?php elseif($pendingSecret): ?>
                                <div class="alert alert-info small"><?php echo e(__('Add this secret to your authenticator app, then enter a generated code to confirm setup.')); ?></div>
                                <div class="fm-secret-box mb-3"><code><?php echo e($pendingSecret); ?></code></div>
                                <div class="small text-secondary text-break mb-3"><?php echo e($provisioningUri); ?></div>
                                <form method="POST" action="<?php echo e(route('security.two-factor.confirm')); ?>">
                                    <?php echo csrf_field(); ?>
                                    <div class="input-group">
                                        <input class="form-control <?php $__errorArgs = ['code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="code" inputmode="numeric" maxlength="6" placeholder="123456" required>
                                        <button class="btn btn-primary" type="submit"><?php echo e(__('Confirm 2FA')); ?></button>
                                    </div>
                                    <?php $__errorArgs = ['code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger small mt-1"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </form>
                            <?php else: ?>
                                <p class="text-secondary small"><?php echo e(__('Use any TOTP-compatible authenticator app. No external service is required.')); ?></p>
                                <form method="POST" action="<?php echo e(route('security.two-factor.begin')); ?>">
                                    <?php echo csrf_field(); ?>
                                    <div class="mb-3">
                                        <label class="form-label" for="two_factor_current_password"><?php echo e(__('Current password')); ?></label>
                                        <input class="form-control" type="password" id="two_factor_current_password" name="current_password" required>
                                    </div>
                                    <button class="btn btn-outline-primary" type="submit"><i class="bi bi-shield-plus me-1"></i><?php echo e(__('Set up 2FA')); ?></button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>

                    <hr class="my-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <div class="fw-semibold"><?php echo e(__('Email verification')); ?></div>
                            <div class="small text-secondary"><?php echo e($user->hasVerifiedEmail() ? __('Verified on :date', ['date' => $user->email_verified_at?->format('d/m/Y H:i')]) : __('Your email address is not verified.')); ?></div>
                        </div>
                        <?php if (! ($user->hasVerifiedEmail())): ?>
                            <a class="btn btn-outline-primary btn-sm" href="<?php echo e(route('verification.notice')); ?>"><?php echo e(__('Verify email')); ?></a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="card fm-card">
                <div class="card-header fm-card-header"><div><h2 class="fm-card-title"><?php echo e(__('Active sessions')); ?></h2><p class="fm-card-subtitle"><?php echo e(__('Devices currently signed in with your account')); ?></p></div></div>
                <div class="card-body p-0">
                    <?php if($sessions->isEmpty()): ?>
                        <div class="p-4 text-secondary"><?php echo e(__('Session details are available when SESSION_DRIVER=database.')); ?></div>
                    <?php else: ?>
                        <div class="table-responsive"><table class="table align-middle mb-0">
                            <thead><tr><th><?php echo e(__('Device')); ?></th><th><?php echo e(__('IP address')); ?></th><th><?php echo e(__('Last activity')); ?></th><th class="text-end"><?php echo e(__('Actions')); ?></th></tr></thead>
                            <tbody>
                                <?php $__currentLoopData = $sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><div class="fw-semibold"><?php echo e(Str::limit($session->user_agent ?: __('Unknown device'), 70)); ?></div><?php if($session->is_current): ?><span class="badge text-bg-success"><?php echo e(__('Current session')); ?></span><?php endif; ?></td>
                                        <td><?php echo e($session->ip_address ?: '—'); ?></td>
                                        <td><?php echo e($session->last_active_at->diffForHumans()); ?></td>
                                        <td class="text-end">
                                            <?php if (! ($session->is_current)): ?>
                                                <form method="POST" action="<?php echo e(route('security.sessions.destroy', $session->id)); ?>"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button class="btn btn-sm btn-outline-danger" type="submit"><?php echo e(__('Terminate')); ?></button></form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table></div>
                    <?php endif; ?>
                </div>
                <?php if($sessions->count() > 1): ?>
                    <div class="card-footer bg-white">
                        <form class="row g-2 align-items-end" method="POST" action="<?php echo e(route('security.sessions.destroy-others')); ?>">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <div class="col"><label class="form-label small" for="sessions_password"><?php echo e(__('Current password')); ?></label><input class="form-control" type="password" id="sessions_password" name="current_password" required></div>
                            <div class="col-auto"><button class="btn btn-outline-danger" type="submit"><?php echo e(__('Sign out other sessions')); ?></button></div>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-12 col-xl-5">
            <div class="card fm-card">
                <div class="card-header fm-card-header"><div><h2 class="fm-card-title"><?php echo e(__('Recent sign-in activity')); ?></h2><p class="fm-card-subtitle"><?php echo e(__('Successful, failed and blocked authentication attempts')); ?></p></div></div>
                <div class="list-group list-group-flush">
                    <?php $__empty_1 = true; $__currentLoopData = $loginActivities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="list-group-item py-3">
                            <div class="d-flex justify-content-between gap-3"><strong><?php echo e(__($activity->event)); ?></strong><span class="small text-secondary"><?php echo e($activity->created_at->diffForHumans()); ?></span></div>
                            <div class="small text-secondary"><?php echo e($activity->ip_address ?: '—'); ?> · <?php echo e(Str::limit($activity->user_agent ?: __('Unknown device'), 64)); ?></div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="p-4 text-secondary"><?php echo e(__('No sign-in activity recorded yet.')); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Projects\flowmanager\resources\views/security/index.blade.php ENDPATH**/ ?>