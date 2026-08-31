<?php

namespace App\Jobs;

use App\Models\ImportRun;
use App\Services\CsvImportService;
use Illuminate\Support\Facades\Storage;

class ProcessImportJob extends TrackedJob
{
    public function __construct(
        string $trackingUuid,
        public int $userId,
        public string $resourceType,
        public string $storedPath,
        public string $originalFilename,
        public array $mapping,
    ) {
        parent::__construct($trackingUuid);
    }

    public function handle(CsvImportService $service): void
    {
        $this->begin(__('Preparing import...'));
        $this->progress(10, __('Reading source file...'));

        $result = $service->import(
            $this->resourceType,
            Storage::path($this->storedPath),
            $this->mapping,
            $this->userId,
        );

        $this->progress(90, __('Saving import summary...'));

        ImportRun::create([
            'user_id' => $this->userId,
            'resource_type' => $this->resourceType,
            'original_filename' => $this->originalFilename,
            'total_rows' => $result['total'],
            'imported_rows' => $result['imported'],
            'failed_rows' => $result['failed'],
            'errors' => $result['errors'],
        ]);

        Storage::delete($this->storedPath);

        $this->complete(
            $result,
            __('Import completed: :ok imported, :failed failed.', [
                'ok' => $result['imported'],
                'failed' => $result['failed'],
            ]),
        );
    }
}
