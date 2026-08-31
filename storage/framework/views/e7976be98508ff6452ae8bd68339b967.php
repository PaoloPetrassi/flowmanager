<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <meta
        name="csrf-token"
        content="<?php echo e(csrf_token()); ?>"
    >

    <title>
        <?php echo $__env->yieldContent('title', 'FlowManager'); ?> | <?php echo e(config('app.name')); ?>

    </title>

    <?php echo app('Illuminate\Foundation\Vite')([
        'resources/css/app.css',
        'resources/js/app.js',
    ]); ?>
</head>

<body class="fm-login-page">

    <main class="fm-login-container">
        <div class="d-flex justify-content-end mb-3">
            <?php echo $__env->make('partials.language-switcher', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>

        <?php echo $__env->yieldContent('content'); ?>
    </main>

</body>
</html><?php /**PATH C:\Projects\flowmanager\resources\views/layouts/guest.blade.php ENDPATH**/ ?>