<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Project;
use App\Models\Task;
use App\Models\Ticket;
use App\Support\FlowResourceRegistry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TrashController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(
            $request->user()->hasPermission('trash.view'),
            403
        );

        $type = (string) $request->query('type');
        $search = trim((string) $request->query('search'));
        $resources = FlowResourceRegistry::trashResources();

        $selected = isset($resources[$type])
            ? [$type => $resources[$type]]
            : $resources;

        $items = collect();

        foreach ($selected as $resourceType => $class) {
            $class::onlyTrashed()
                ->latest('deleted_at')
                ->limit(100)
                ->get()
                ->each(function (Model $model) use (&$items, $resourceType, $search): void {
                    $label = FlowResourceRegistry::labelForModel($model);

                    if (
                        $search !== ''
                        && ! str_contains(
                            mb_strtolower($label),
                            mb_strtolower($search)
                        )
                    ) {
                        return;
                    }

                    $items->push([
                        'type' => $resourceType,
                        'resource' => FlowResourceRegistry::labelForClass($model::class),
                        'label' => $label,
                        'model' => $model,
                        'deleted_at' => $model->deleted_at,
                    ]);
                });
        }

        $items = $items
            ->sortByDesc('deleted_at')
            ->values();

        $page = max(1, $request->integer('page', 1));
        $perPage = 25;

        $paginated = new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('trash.index', [
            'items' => $paginated,
            'resources' => array_keys($resources),
            'filters' => [
                'type' => $type,
                'search' => $search,
            ],
        ]);
    }

    public function restore(
        Request $request,
        string $type,
        int $id
    ): RedirectResponse {
        abort_unless(
            $request->user()->hasPermission('trash.restore'),
            403
        );

        $model = FlowResourceRegistry::findTrashed($type, $id);
        abort_unless(method_exists($model, 'restore'), 404);

        if ($message = $this->restoreBlockMessage($model)) {
            return back()->with('error', $message);
        }

        $model->restore();

        return back()->with(
            'status',
            __('Item restored successfully.')
        );
    }

    public function destroy(
        Request $request,
        string $type,
        int $id
    ): RedirectResponse {
        abort_unless(
            $request->user()->hasPermission('trash.delete'),
            403
        );

        $model = FlowResourceRegistry::findTrashed($type, $id);
        abort_unless(method_exists($model, 'forceDelete'), 404);

        if ($message = $this->deleteBlockMessage($model)) {
            return back()->with('error', $message);
        }

        $this->deleteCollaboration($model);
        $model->forceDelete();

        return back()->with(
            'status',
            __('Item permanently deleted.')
        );
    }

    private function restoreBlockMessage(Model $model): ?string
    {
        if ($model instanceof Project) {
            $companyExists = Company::query()
                ->whereKey($model->company_id)
                ->exists();

            if (! $companyExists) {
                return __('Restore the parent company before restoring this project.');
            }

            if (
                $model->contact_id
                && Contact::onlyTrashed()->whereKey($model->contact_id)->exists()
            ) {
                return __('Restore the linked contact before restoring this project.');
            }
        }

        if ($model instanceof Task) {
            $projectExists = Project::query()
                ->whereKey($model->project_id)
                ->exists();

            if (! $projectExists) {
                return __('Restore the parent project before restoring this task.');
            }
        }

        if ($model instanceof Contact && $model->company_id) {
            $companyIsTrashed = Company::onlyTrashed()
                ->whereKey($model->company_id)
                ->exists();

            if ($companyIsTrashed) {
                return __('Restore the parent company before restoring this contact.');
            }
        }

        if ($model instanceof Asset && $model->company_id) {
            if (Company::onlyTrashed()->whereKey($model->company_id)->exists()) {
                return __('Restore the parent company before restoring this asset.');
            }
        }

        if ($model instanceof Ticket) {
            if (
                $model->company_id
                && Company::onlyTrashed()->whereKey($model->company_id)->exists()
            ) {
                return __('Restore the parent company before restoring this ticket.');
            }

            if (
                $model->contact_id
                && Contact::onlyTrashed()->whereKey($model->contact_id)->exists()
            ) {
                return __('Restore the linked contact before restoring this ticket.');
            }
        }

        return null;
    }

    private function deleteBlockMessage(Model $model): ?string
    {
        if (
            $model instanceof Company
            && $model->projects()->withTrashed()->exists()
        ) {
            return __('This company cannot be permanently deleted while projects are linked to it.');
        }

        if (
            $model instanceof Project
            && $model->tasks()->withTrashed()->exists()
        ) {
            return __('This project cannot be permanently deleted while tasks are linked to it.');
        }

        return null;
    }

    private function deleteCollaboration(Model $model): void
    {
        if (method_exists($model, 'attachments')) {
            $model->attachments()->get()->each(function ($attachment): void {
                Storage::disk($attachment->disk)->delete($attachment->path);
                $attachment->delete();
            });
        }

        if (method_exists($model, 'comments')) {
            $model->comments()->delete();
        }
    }
}
