<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$targets = [
    'app',
    'bootstrap',
    'config',
    'database',
    'routes',
    'tests',
];

$files = [];

foreach ($targets as $target) {
    $path = $root.DIRECTORY_SEPARATOR.$target;

    if (! is_dir($path)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
    );

    foreach ($iterator as $file) {
        if ($file->isFile() && strtolower($file->getExtension()) === 'php') {
            $files[] = $file->getPathname();
        }
    }
}

sort($files);
$failures = [];

foreach ($files as $file) {
    $command = escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($file).' 2>&1';
    exec($command, $output, $exitCode);

    if ($exitCode !== 0) {
        $failures[] = [
            'file' => $file,
            'output' => implode(PHP_EOL, $output),
        ];
    }

    $output = [];
}

if ($failures !== []) {
    fwrite(STDERR, "PHP syntax check failed:\n\n");

    foreach ($failures as $failure) {
        fwrite(STDERR, $failure['file'].PHP_EOL.$failure['output'].PHP_EOL.PHP_EOL);
    }

    exit(1);
}

fwrite(STDOUT, sprintf('PHP syntax OK: %d files checked.%s', count($files), PHP_EOL));
