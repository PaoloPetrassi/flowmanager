<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessImportJob;
use App\Models\BackgroundJob;
use App\Models\ImportRun;
use App\Services\CsvImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ImportController extends Controller
{
    public function __construct(private CsvImportService $service) {}

    public function index(): View
    {
        abort_unless(auth()->user()->hasPermission('imports.manage'), 403);

        return view('imports.index', [
            'resources' => $this->service->resources(),
            'runs' => ImportRun::query()
                ->where('user_id', auth()->id())
                ->latest()
                ->limit(15)
                ->get(),
            'backgroundImports' => BackgroundJob::query()
                ->where('user_id', auth()->id())
                ->where('type', 'import')
                ->latest()
                ->limit(10)
                ->get(),
        ]);
    }

    public function preview(Request $request): View
    {
        abort_unless(auth()->user()->hasPermission('imports.manage'), 403);

        $validated = $request->validate([
            'resource_type' => ['required', 'in:companies,contacts,projects,tasks,tickets'],
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx', 'max:10240'],
        ]);

        $file = $request->file('file');
        $path = $file->store('imports/tmp');
        $preview = $this->service->preview(Storage::path($path));

        return view('imports.preview', [
            'resource' => $validated['resource_type'],
            'storedPath' => $path,
            'originalFilename' => $file->getClientOriginalName(),
            'preview' => $preview,
            'fields' => $this->service->resources()[$validated['resource_type']]['fields'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->hasPermission('imports.manage'), 403);

        $validated = $request->validate([
            'resource_type' => ['required', 'in:companies,contacts,projects,tasks,tickets'],
            'stored_path' => ['required', 'string'],
            'original_filename' => ['required', 'string', 'max:255'],
            'mapping' => ['required', 'array'],
        ]);

        abort_unless(
            Str::startsWith($validated['stored_path'], 'imports/tmp/')
                && Storage::exists($validated['stored_path']),
            422,
            __('The staged import file is no longer available.'),
        );

        $tracking = BackgroundJob::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $request->user()->id,
            'type' => 'import',
            'name' => __('Import :file', ['file' => $validated['original_filename']]),
            'queue' => config('flowmanager.queues.imports', 'imports'),
            'payload' => [
                'resource_type' => $validated['resource_type'],
                'original_filename' => $validated['original_filename'],
            ],
        ]);

        ProcessImportJob::dispatch(
            $tracking->uuid,
            $request->user()->id,
            $validated['resource_type'],
            $validated['stored_path'],
            $validated['original_filename'],
            $validated['mapping'],
        )->onQueue($tracking->queue);

        return redirect()
            ->route('imports.index')
            ->with('status', __('Import queued. It will continue in the background.'));
    }
}
