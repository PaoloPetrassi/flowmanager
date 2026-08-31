<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Project;
use App\Models\Task;
use App\Models\Ticket;
use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

class CsvImportService
{
    public function resources(): array
    {
        return [
            'companies' => [
                'model' => Company::class,
                'fields' => ['name', 'vat_number', 'email', 'phone', 'website', 'address', 'city', 'country'],
            ],
            'contacts' => [
                'model' => Contact::class,
                'fields' => ['first_name', 'last_name', 'email', 'phone', 'job_title', 'department'],
            ],
            'projects' => [
                'model' => Project::class,
                'fields' => ['code', 'name', 'description', 'budget'],
            ],
            'tasks' => [
                'model' => Task::class,
                'fields' => ['title', 'description'],
            ],
            'tickets' => [
                'model' => Ticket::class,
                'fields' => ['subject', 'description'],
            ],
        ];
    }

    public function preview(string $path): array
    {
        [$headers, $rows] = $this->readRows($path, 8);

        return ['headers' => $headers, 'rows' => $rows];
    }

    public function import(
        string $resource,
        string $path,
        array $mapping,
        ?int $actorId = null,
    ): array {
        $config = $this->resources()[$resource] ?? null;
        abort_unless($config, 404);

        [, $rows] = $this->readRows($path);
        $imported = 0;
        $failed = 0;
        $errors = [];

        foreach ($rows as $index => $source) {
            try {
                $data = [];

                foreach ($mapping as $target => $sourceKey) {
                    if ($sourceKey !== '' && in_array($target, $config['fields'], true)) {
                        $data[$target] = $source[$sourceKey] ?? null;
                    }
                }

                $data = $this->defaults($resource, $data, $actorId ?? auth()->id());
                $config['model']::create($data);
                $imported++;
            } catch (\Throwable $exception) {
                $failed++;

                if (count($errors) < 50) {
                    $errors[] = [
                        'row' => $index + 2,
                        'message' => $exception->getMessage(),
                    ];
                }
            }
        }

        return [
            'total' => $imported + $failed,
            'imported' => $imported,
            'failed' => $failed,
            'errors' => $errors,
        ];
    }

    private function defaults(string $resource, array $data, ?int $actorId): array
    {
        return match ($resource) {
            'companies' => $data + [
                'type' => 'customer',
                'status' => 'active',
                'created_by' => $actorId,
            ],
            'contacts' => $data + [
                'is_primary' => false,
                'created_by' => $actorId,
            ],
            'projects' => $data + [
                'status' => 'planned',
                'priority' => 'medium',
                'is_template' => false,
                'created_by' => $actorId,
            ],
            'tasks' => $data + [
                'status' => 'todo',
                'priority' => 'medium',
                'recurrence' => 'none',
                'recurrence_interval' => 1,
                'created_by' => $actorId,
            ],
            'tickets' => $data + [
                'reference' => 'TKT-'.now()->format('Y').'-'.strtoupper(Str::random(6)),
                'status' => 'open',
                'priority' => 'medium',
                'category' => 'other',
                'created_by' => $actorId,
            ],
            default => $data,
        };
    }

    private function readRows(string $path, ?int $limit = null): array
    {
        return strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'xlsx'
            ? $this->readXlsx($path, $limit)
            : $this->readCsv($path, $limit);
    }

    private function readCsv(string $path, ?int $limit): array
    {
        $handle = fopen($path, 'rb');

        if (! $handle) {
            throw new RuntimeException('Unable to open import file.');
        }

        $headers = array_map('trim', fgetcsv($handle) ?: []);
        $rows = [];

        while (($row = fgetcsv($handle)) !== false) {
            $row = array_pad($row, count($headers), null);
            $rows[] = array_combine($headers, array_slice($row, 0, count($headers))) ?: [];

            if ($limit !== null && count($rows) >= $limit) {
                break;
            }
        }

        fclose($handle);

        return [$headers, $rows];
    }

    private function readXlsx(string $path, ?int $limit): array
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('The PHP ZIP extension is required to import XLSX files.');
        }

        $zip = new ZipArchive();

        if ($zip->open($path) !== true) {
            throw new RuntimeException('Unable to open XLSX file.');
        }

        $sharedStrings = [];
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');

        if ($sharedXml !== false) {
            $xml = simplexml_load_string($sharedXml);

            foreach ($xml?->si ?? [] as $item) {
                $sharedStrings[] = isset($item->t)
                    ? (string) $item->t
                    : collect($item->r ?? [])
                        ->map(fn ($run) => (string) $run->t)
                        ->implode('');
            }
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if ($sheetXml === false) {
            throw new RuntimeException('The first XLSX worksheet could not be read.');
        }

        $xml = simplexml_load_string($sheetXml);
        $matrix = [];

        foreach ($xml?->sheetData?->row ?? [] as $row) {
            $values = [];

            foreach ($row->c as $cell) {
                $reference = (string) $cell['r'];
                preg_match('/^[A-Z]+/', $reference, $match);
                $column = $this->columnIndex($match[0] ?? 'A');
                $type = (string) $cell['t'];

                $value = match ($type) {
                    's' => $sharedStrings[(int) $cell->v] ?? '',
                    'inlineStr' => (string) $cell->is->t,
                    'b' => (string) ((int) $cell->v),
                    default => (string) $cell->v,
                };

                $values[$column] = $value;
            }

            if ($values === []) {
                continue;
            }

            $max = max(array_keys($values));
            $matrix[] = array_map(
                fn ($index) => $values[$index] ?? null,
                range(0, $max),
            );

            if ($limit !== null && count($matrix) >= $limit + 1) {
                break;
            }
        }

        $headers = array_map(
            fn ($value) => trim((string) $value),
            array_shift($matrix) ?: [],
        );

        $rows = collect($matrix)
            ->map(function (array $row) use ($headers) {
                $row = array_pad($row, count($headers), null);

                return array_combine(
                    $headers,
                    array_slice($row, 0, count($headers)),
                ) ?: [];
            })
            ->all();

        return [$headers, $rows];
    }

    private function columnIndex(string $letters): int
    {
        $index = 0;

        foreach (str_split($letters) as $letter) {
            $index = ($index * 26) + (ord($letter) - 64);
        }

        return $index - 1;
    }
}
