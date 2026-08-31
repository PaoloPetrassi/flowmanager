<?php

namespace App\Http\Controllers;

use App\Models\BackgroundJob;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class SystemJobsController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->hasPermission('system.view'), 403);

        $status = $request->string('status')->toString();
        $type = $request->string('type')->toString();

        $jobs = BackgroundJob::query()
            ->with('user:id,name,email')
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($type !== '', fn ($query) => $query->where('type', $type))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $queueCounts = Schema::hasTable('jobs')
            ? DB::table('jobs')
                ->selectRaw('queue, COUNT(*) as total')
                ->groupBy('queue')
                ->orderBy('queue')
                ->pluck('total', 'queue')
            : collect();

        $failedJobs = $this->failedJobs();

        return view('system.jobs', [
            'jobs' => $jobs,
            'queueCounts' => $queueCounts,
            'failedJobs' => $failedJobs,
            'types' => BackgroundJob::query()
                ->distinct()
                ->orderBy('type')
                ->pluck('type'),
            'status' => $status,
            'type' => $type,
        ]);
    }

    public function retry(Request $request, string $uuid): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('system.manage'), 403);
        abort_unless(Schema::hasTable('failed_jobs'), 404);

        $exists = DB::table('failed_jobs')->where('uuid', $uuid)->exists();
        abort_unless($exists, 404);

        Artisan::call('queue:retry', ['id' => [$uuid]]);

        return back()->with('status', __('Failed job queued for retry.'));
    }

    public function forget(Request $request, string $uuid): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('system.manage'), 403);
        abort_unless(Schema::hasTable('failed_jobs'), 404);

        $exists = DB::table('failed_jobs')->where('uuid', $uuid)->exists();
        abort_unless($exists, 404);

        Artisan::call('queue:forget', ['id' => $uuid]);

        return back()->with('status', __('Failed job removed.'));
    }

    public function clearCompleted(Request $request): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('system.manage'), 403);

        $deleted = BackgroundJob::query()
            ->where('status', BackgroundJob::STATUS_COMPLETED)
            ->where('finished_at', '<', now()->subDays(7))
            ->delete();

        return back()->with('status', __('Cleared :count completed job(s).', [
            'count' => $deleted,
        ]));
    }

    private function failedJobs(): LengthAwarePaginator
    {
        if (! Schema::hasTable('failed_jobs')) {
            return new LengthAwarePaginator([], 0, 15);
        }

        $page = max(1, (int) request('failed_page', 1));
        $perPage = 15;
        $query = DB::table('failed_jobs')->orderByDesc('failed_at');
        $total = $query->count();

        $items = $query
            ->forPage($page, $perPage)
            ->get()
            ->map(function ($row) {
                $payload = json_decode($row->payload, true) ?: [];

                return (object) [
                    'uuid' => $row->uuid,
                    'queue' => $row->queue,
                    'name' => $payload['displayName'] ?? $payload['job'] ?? __('Background job'),
                    'exception' => $this->firstLine($row->exception),
                    'failed_at' => $row->failed_at,
                ];
            });

        return new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'pageName' => 'failed_page',
                'query' => request()->except('failed_page'),
            ],
        );
    }

    private function firstLine(?string $exception): string
    {
        $line = preg_split('/\R/', (string) $exception)[0] ?? '';

        return mb_substr($line, 0, 500);
    }
}
