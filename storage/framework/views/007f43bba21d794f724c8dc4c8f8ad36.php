<?php
    $currentLocale = app()->getLocale();
?>

<div
    class="fm-language-switcher"
    role="group"
    aria-label="<?php echo e(__('Change language')); ?>"
>
    <span class="fm-language-icon" aria-hidden="true">
        <i class="bi bi-translate"></i>
    </span>

    <?php $__currentLoopData = config('app.supported_locales'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $localeCode => $localeName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <form
            method="POST"
            action="<?php echo e(route('locale.update', $localeCode)); ?>"
            class="fm-language-form"
        >
            <?php echo csrf_field(); ?>

            <button
                type="submit"
                class="fm-language-option <?php echo e($currentLocale === $localeCode ? 'is-active' : ''); ?>"
                lang="<?php echo e($localeCode); ?>"
                aria-pressed="<?php echo e($currentLocale === $localeCode ? 'true' : 'false'); ?>"
                title="<?php echo e($localeName); ?>"
            >
                <?php echo e(strtoupper($localeCode)); ?>

            </button>
        </form>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php /**PATH C:\Projects\flowmanager\resources\views/partials/language-switcher.blade.php ENDPATH**/ ?>