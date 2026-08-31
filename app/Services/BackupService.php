<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class BackupService
{
    private const FORMAT = 'flowmanager-db-backup-v2';

    private const TABLES = [
        'users', 'roles', 'permissions', 'role_user', 'permission_role',
        'companies', 'contacts', 'projects', 'project_user', 'milestones',
        'tasks', 'task_dependencies', 'time_entries', 'assets', 'tickets',
        'comments', 'attachments', 'notifications', 'audit_logs',
        'login_activities', 'automation_rules', 'automation_runs',
    ];

    private const FILE_ROOTS = [
        'flowmanager/attachments',
    ];

    public function create(): string
    {
        $basename = 'flowmanager-'.now()->format('Ymd-His-u');
        $filename = 'backups/'.$basename.'.json';
        $snapshotPath = 'backups/files/'.$basename;

        $payload = [
            'format' => self::FORMAT,
            'created_at' => now()->toIso8601String(),
            'app_version' => '0.9',
            'tables' => [],
            'files' => [
                'snapshot_path' => $snapshotPath,
                'count' => 0,
                'bytes' => 0,
                'roots' => self::FILE_ROOTS,
            ],
        ];

        try {
            foreach (self::TABLES as $table) {
                if (! Schema::hasTable($table)) {
                    continue;
                }

                $payload['tables'][$table] = DB::table($table)
                    ->orderBy($this->orderColumn($table))
                    ->get()
                    ->map(fn ($row) => (array) $row)
                    ->all();
            }

            foreach (self::FILE_ROOTS as $root) {
                foreach (Storage::disk('local')->allFiles($root) as $source) {
                    $destination = $snapshotPath.'/'.$source;

                    if (! Storage::disk('local')->copy($source, $destination)) {
                        throw new RuntimeException('Unable to copy backup file: '.$source);
                    }

                    $payload['files']['count']++;
                    $payload['files']['bytes'] += Storage::disk('local')->size($source);
                }
            }

            $json = json_encode(
                $payload,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
            );

            if (! Storage::disk('local')->put($filename, $json)) {
                throw new RuntimeException('Unable to write backup manifest.');
            }

            $this->prune();

            return $filename;
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($filename);
            Storage::disk('local')->deleteDirectory($snapshotPath);

            throw $exception;
        }
    }

    public function verify(string $filename): bool
    {
        $filename = $this->normalizeManifestPath($filename);

        if (! Storage::disk('local')->exists($filename)) {
            return false;
        }

        try {
            $data = json_decode(
                Storage::disk('local')->get($filename),
                true,
                flags: JSON_THROW_ON_ERROR
            );
        } catch (Throwable) {
            return false;
        }

        if (! is_array($data)
            || ! in_array($data['format'] ?? null, [self::FORMAT, 'flowmanager-db-backup-v1'], true)
            || ! is_array($data['tables'] ?? null)) {
            return false;
        }

        if (($data['format'] ?? null) === self::FORMAT) {
            $files = $data['files'] ?? null;

            if (! is_array($files)
                || ! is_string($files['snapshot_path'] ?? null)
                || ! is_array($files['roots'] ?? null)) {
                return false;
            }
        }

        return true;
    }

    public function restore(string $filename): void
    {
        $filename = $this->normalizeManifestPath($filename);

        if (! $this->verify($filename)) {
            throw new RuntimeException('Invalid FlowManager backup.');
        }

        $data = json_decode(
            Storage::disk('local')->get($filename),
            true,
            flags: JSON_THROW_ON_ERROR
        );
        $tables = $data['tables'];

        Schema::disableForeignKeyConstraints();

        try {
            DB::transaction(function () use ($tables): void {
                foreach (array_reverse(self::TABLES) as $table) {
                    if (Schema::hasTable($table) && array_key_exists($table, $tables)) {
                        DB::table($table)->delete();
                    }
                }

                foreach (self::TABLES as $table) {
                    if (! Schema::hasTable($table) || empty($tables[$table])) {
                        continue;
                    }

                    foreach (array_chunk($tables[$table], 250) as $chunk) {
                        DB::table($table)->insert($chunk);
                    }
                }
            });
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        $this->restoreFiles($data);
    }

    public function delete(string $filename): void
    {
        $filename = $this->normalizeManifestPath($filename);

        if (! Storage::disk('local')->exists($filename)) {
            return;
        }

        $snapshotPath = $this->snapshotPathFromManifest($filename);

        Storage::disk('local')->delete($filename);

        if ($snapshotPath) {
            Storage::disk('local')->deleteDirectory($snapshotPath);
        }
    }

    public function list(): array
    {
        return collect(Storage::disk('local')->files('backups'))
            ->filter(fn (string $file) => str_ends_with($file, '.json'))
            ->sortDesc()
            ->values()
            ->map(function (string $file): array {
                $manifestSize = Storage::disk('local')->size($file);
                $snapshotPath = $this->snapshotPathFromManifest($file);
                $snapshotSize = $snapshotPath
                    ? collect(Storage::disk('local')->allFiles($snapshotPath))
                        ->sum(fn (string $snapshotFile) => Storage::disk('local')->size($snapshotFile))
                    : 0;

                return [
                    'path' => $file,
                    'name' => basename($file),
                    'size' => $manifestSize + $snapshotSize,
                    'modified_at' => Storage::disk('local')->lastModified($file),
                    'valid' => $this->verify($file),
                    'has_files' => $snapshotPath !== null,
                ];
            })
            ->all();
    }

    private function restoreFiles(array $data): void
    {
        if (($data['format'] ?? null) !== self::FORMAT) {
            return;
        }

        $files = $data['files'] ?? [];
        $snapshotPath = $files['snapshot_path'] ?? null;
        $roots = $files['roots'] ?? [];

        if (! is_string($snapshotPath) || ! is_array($roots)) {
            return;
        }

        foreach ($roots as $root) {
            if (! is_string($root) || $root === '') {
                continue;
            }

            Storage::disk('local')->deleteDirectory($root);
        }

        foreach (Storage::disk('local')->allFiles($snapshotPath) as $snapshotFile) {
            $prefix = rtrim($snapshotPath, '/').'/';
            $destination = str_starts_with($snapshotFile, $prefix)
                ? substr($snapshotFile, strlen($prefix))
                : null;

            if (! $destination) {
                continue;
            }

            if (! Storage::disk('local')->copy($snapshotFile, $destination)) {
                throw new RuntimeException('Unable to restore backup file: '.$destination);
            }
        }
    }

    private function prune(): void
    {
        $keep = max(1, (int) config('flowmanager.backups.keep', 14));
        $manifests = collect(Storage::disk('local')->files('backups'))
            ->filter(fn (string $file) => str_ends_with($file, '.json'))
            ->sortDesc()
            ->values();

        $manifests->slice($keep)->each(fn (string $file) => $this->delete($file));
    }

    private function snapshotPathFromManifest(string $filename): ?string
    {
        if (! Storage::disk('local')->exists($filename)) {
            return null;
        }

        try {
            $data = json_decode(
                Storage::disk('local')->get($filename),
                true,
                flags: JSON_THROW_ON_ERROR
            );
        } catch (Throwable) {
            return null;
        }

        $snapshotPath = $data['files']['snapshot_path'] ?? null;

        return is_string($snapshotPath) && $snapshotPath !== ''
            ? $snapshotPath
            : null;
    }

    private function normalizeManifestPath(string $filename): string
    {
        $filename = str_replace('\\', '/', $filename);

        if (str_starts_with($filename, 'backups/')) {
            return $filename;
        }

        return 'backups/'.basename($filename);
    }

    private function orderColumn(string $table): string
    {
        $columns = Schema::getColumnListing($table);

        return in_array('id', $columns, true)
            ? 'id'
            : ($columns[0] ?? 'id');
    }
}
