<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;
use ZipArchive;

class FlowManagerPackageReleaseCommand extends Command
{
    protected $signature = 'flowmanager:package-release
        {--output= : Destination ZIP path. Defaults to dist/flowmanager-vVERSION.zip}';

    protected $description = 'Create a clean FlowManager source release ZIP without secrets, dependencies or runtime data';

    private const EXCLUDED_DIRECTORIES = [
        '.git',
        '.idea',
        '.vscode',
        '.claude',
        '.codex',
        '.cursor',
        'dist',
        'node_modules',
        'vendor',
    ];

    private const EXCLUDED_FILES = [
        '.env',
        '.env.backup',
        '.env.production',
        '.mcp.json',
        '.phpunit.result.cache',
        'AGENTS.md',
        'CLAUDE.md',
        'boost.json',
        'auth.json',
        'database/database.sqlite',
        'public/hot',
    ];

    private const RUNTIME_PREFIXES = [
        'storage/app/private/',
        'storage/app/public/',
        'storage/app/backups/',
        'storage/framework/cache/data/',
        'storage/framework/sessions/',
        'storage/framework/views/',
        'storage/logs/',
    ];

    public function handle(): int
    {
        if (! class_exists(ZipArchive::class)) {
            $this->error('PHP ZipArchive is required. Enable the PHP zip extension first.');

            return self::FAILURE;
        }

        $root = base_path();
        $output = $this->resolveOutputPath((string) $this->option('output'));
        $outputDirectory = dirname($output);

        if (! is_dir($outputDirectory) && ! mkdir($outputDirectory, 0775, true) && ! is_dir($outputDirectory)) {
            throw new RuntimeException('Unable to create release directory: '.$outputDirectory);
        }

        if (is_file($output)) {
            unlink($output);
        }

        $zip = new ZipArchive;
        $openResult = $zip->open($output, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if ($openResult !== true) {
            $this->error('Unable to create release ZIP: '.$output);

            return self::FAILURE;
        }

        $filesAdded = 0;
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if (! $file->isFile()) {
                continue;
            }

            $absolute = $file->getPathname();
            $relative = str_replace('\\', '/', substr($absolute, strlen($root) + 1));

            if ($this->shouldExclude($relative, $output)) {
                continue;
            }

            $zip->addFile($absolute, 'flowmanager/'.$relative);
            $filesAdded++;
        }

        $zip->close();

        $this->info('Clean release package created successfully.');
        $this->line('Version: '.config('flowmanager.version'));
        $this->line('Files: '.$filesAdded);
        $this->line('Output: '.$output);
        $this->newLine();
        $this->comment('Excluded: .env, Git history, vendor, node_modules, local database, caches, sessions, logs, backups and private runtime files.');

        return self::SUCCESS;
    }

    private function resolveOutputPath(string $option): string
    {
        if ($option === '') {
            return base_path('dist/flowmanager-v'.config('flowmanager.version').'.zip');
        }

        if (str_starts_with($option, '/') || preg_match('/^[A-Za-z]:[\\\\\/]/', $option) === 1) {
            return $option;
        }

        return base_path($option);
    }

    private function shouldExclude(string $relative, string $output): bool
    {
        $parts = explode('/', $relative);

        if (in_array($parts[0], self::EXCLUDED_DIRECTORIES, true)) {
            return true;
        }

        if (in_array($relative, self::EXCLUDED_FILES, true)) {
            return true;
        }

        if ($relative === str_replace('\\', '/', ltrim(substr($output, strlen(base_path())), '/\\'))) {
            return true;
        }

        foreach (self::RUNTIME_PREFIXES as $prefix) {
            if (str_starts_with($relative, $prefix) && basename($relative) !== '.gitignore') {
                return true;
            }
        }

        return false;
    }
}
