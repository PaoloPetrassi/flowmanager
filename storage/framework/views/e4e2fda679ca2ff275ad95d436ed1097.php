<div class="row g-4 mt-1">
    <div class="col-12 col-xl-7">
        <div class="card fm-card h-100">
            <div class="card-header fm-card-header">
                <div>
                    <h2 class="fm-card-title"><?php echo e(__('Discussion')); ?></h2>
                    <p class="fm-card-subtitle"><?php echo e(__('Comments and operational notes shared on this record')); ?></p>
                </div>
                <span class="badge text-bg-light border"><?php echo e($collaboration['comments']->count()); ?></span>
            </div>

            <?php if(auth()->user()->hasPermission('comments.create')): ?>
                <div class="card-body border-bottom">
                    <form method="POST" action="<?php echo e(route('comments.store', [$collaborationType, $collaborationTarget->id])); ?>">
                        <?php echo csrf_field(); ?>

                        <label for="collaboration-comment" class="form-label fw-semibold"><?php echo e(__('Add comment')); ?></label>
                        <textarea
                            id="collaboration-comment"
                            name="body"
                            rows="3"
                            maxlength="5000"
                            class="form-control <?php $__errorArgs = ['body'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                            placeholder="<?php echo e(__('Write an update, note or decision...')); ?>"
                            required
                        ><?php echo e(old('body')); ?></textarea>

                        <?php $__errorArgs = ['body'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                        <div class="d-flex justify-content-end mt-3">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-send me-1"></i>
                                <?php echo e(__('Publish comment')); ?>

                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <div class="fm-collaboration-list">
                <?php $__empty_1 = true; $__currentLoopData = $collaboration['comments']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <article class="fm-comment-item">
                        <div class="fm-comment-avatar">
                            <?php echo e(strtoupper(substr($comment->user?->name ?: '?', 0, 1))); ?>

                        </div>

                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex align-items-start justify-content-between gap-3">
                                <div>
                                    <div class="fw-semibold"><?php echo e($comment->user?->name ?: __('Deleted user')); ?></div>
                                    <div class="small text-secondary">
                                        <?php echo e($comment->created_at->format('d/m/Y H:i')); ?>

                                        · <?php echo e($comment->created_at->diffForHumans()); ?>

                                    </div>
                                </div>

                                <?php if($comment->user_id === auth()->id() || auth()->user()->hasPermission('comments.delete')): ?>
                                    <form method="POST" action="<?php echo e(route('comments.destroy', $comment)); ?>" onsubmit="return confirm(<?php echo \Illuminate\Support\Js::from(__('Delete this comment?'))->toHtml() ?>);">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-sm btn-link text-danger p-1" title="<?php echo e(__('Delete')); ?>">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>

                            <div class="fm-comment-body mt-2"><?php echo nl2br(e($comment->body)); ?></div>
                        </div>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="p-4 text-center text-secondary">
                        <i class="bi bi-chat-left-text d-block fs-4 mb-2"></i>
                        <?php echo e(__('No comments yet.')); ?>

                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-5">
        <div class="card fm-card mb-4">
            <div class="card-header fm-card-header">
                <div>
                    <h2 class="fm-card-title"><?php echo e(__('Attachments')); ?></h2>
                    <p class="fm-card-subtitle"><?php echo e(__('Private files linked to this record')); ?></p>
                </div>
                <span class="badge text-bg-light border"><?php echo e($collaboration['attachments']->count()); ?></span>
            </div>

            <?php if(auth()->user()->hasPermission('attachments.create')): ?>
                <div class="card-body border-bottom">
                    <form method="POST" action="<?php echo e(route('attachments.store', [$collaborationType, $collaborationTarget->id])); ?>" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>

                        <label for="collaboration-file" class="form-label fw-semibold"><?php echo e(__('Upload file')); ?></label>
                        <div class="input-group">
                            <input
                                id="collaboration-file"
                                type="file"
                                name="file"
                                class="form-control <?php $__errorArgs = ['file'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.txt,.png,.jpg,.jpeg,.zip"
                                required
                            >
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="bi bi-upload"></i>
                                <span class="d-none d-sm-inline ms-1"><?php echo e(__('Upload')); ?></span>
                            </button>
                        </div>

                        <?php $__errorArgs = ['file'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="text-danger small mt-1"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                        <div class="form-text"><?php echo e(__('PDF, Office, CSV, text, images or ZIP. Maximum 10 MB.')); ?></div>
                    </form>
                </div>
            <?php endif; ?>

            <div class="list-group list-group-flush">
                <?php $__empty_1 = true; $__currentLoopData = $collaboration['attachments']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attachment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="list-group-item fm-attachment-item">
                        <div class="fm-file-icon">
                            <i class="bi bi-paperclip"></i>
                        </div>

                        <div class="flex-grow-1 min-w-0">
                            <a href="<?php echo e(route('attachments.download', $attachment)); ?>" class="fw-semibold text-dark d-block text-truncate">
                                <?php echo e($attachment->original_name); ?>

                            </a>
                            <div class="small text-secondary">
                                <?php echo e($attachment->formattedSize()); ?>

                                · <?php echo e($attachment->user?->name ?: __('Deleted user')); ?>

                                · <?php echo e($attachment->created_at->format('d/m/Y H:i')); ?>

                            </div>
                        </div>

                        <?php if($attachment->user_id === auth()->id() || auth()->user()->hasPermission('attachments.delete')): ?>
                            <form method="POST" action="<?php echo e(route('attachments.destroy', $attachment)); ?>" onsubmit="return confirm(<?php echo \Illuminate\Support\Js::from(__('Delete this attachment?'))->toHtml() ?>);">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="<?php echo e(__('Delete')); ?>">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="p-4 text-center text-secondary">
                        <?php echo e(__('No attachments yet.')); ?>

                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if(auth()->user()->hasPermission('audit.view')): ?>
            <div class="card fm-card">
                <div class="card-header fm-card-header">
                    <div>
                        <h2 class="fm-card-title"><?php echo e(__('Recent activity')); ?></h2>
                        <p class="fm-card-subtitle"><?php echo e(__('Latest tracked changes on this record')); ?></p>
                    </div>
                    <a href="<?php echo e(route('activity.index', ['search' => $collaborationTarget instanceof App\Models\Contact ? $collaborationTarget->full_name : App\Support\FlowResourceRegistry::labelForModel($collaborationTarget)])); ?>" class="btn btn-sm btn-outline-secondary">
                        <?php echo e(__('View all')); ?>

                    </a>
                </div>

                <div class="list-group list-group-flush">
                    <?php $__empty_1 = true; $__currentLoopData = $collaboration['auditLogs']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="list-group-item fm-activity-mini">
                            <span class="fm-activity-dot"></span>
                            <div class="min-w-0">
                                <div class="small">
                                    <strong><?php echo e($log->user?->name ?: __('System')); ?></strong>
                                    <?php echo e(__(str_replace('_', ' ', $log->event))); ?>

                                </div>
                                <div class="small text-secondary"><?php echo e($log->created_at->format('d/m/Y H:i')); ?></div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="p-4 text-center text-secondary"><?php echo e(__('No tracked activity yet.')); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php /**PATH C:\Projects\flowmanager\resources\views/partials/collaboration-panel.blade.php ENDPATH**/ ?>