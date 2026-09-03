<?php

namespace App\Http\Controllers;

use App\Enums\AssetStatus;
use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Asset;
use App\Models\Project;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class BulkActionController extends Controller
{
    public function update(Request $request, string $resource): RedirectResponse
    {
        $config = $this->resourceConfig($resource);

        abort_unless($config, 404);

        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'max:100'],
            'ids.*' => ['integer'],
            'action' => ['required', 'in:status,priority,assign,delete'],
            'value' => ['nullable', 'string', 'max:100'],
        ]);

        $ids = array_values(array_unique(array_map('intval', $data['ids'])));
        $modelClass = $config['model'];
        $models = $modelClass::query()->whereIn('id', $ids)->get();

        if ($models->count() !== count($ids)) {
            throw ValidationException::withMessages([
                'ids' => __('One or more selected records no longer exist.'),
            ]);
        }

        $this->validateAction($models, $config, $data['action'], $data['value'] ?? null);

        DB::transaction(function () use ($models, $config, $data): void {
            foreach ($models as $model) {
                Gate::authorize($data['action'] === 'delete' ? 'delete' : 'update', $model);

                match ($data['action']) {
                    'delete' => $model->delete(),
                    'status' => $this->applyStatus($model, $data['value']),
                    'priority' => $model->update(['priority' => $data['value']]),
                    'assign' => $this->applyAssignment($model, $config['assignment'], $data['value'] ?? null),
                };
            }
        });

        return back()->with('status', __('Bulk action completed for :count records.', [
            'count' => $models->count(),
        ]));
    }

    /**
     * @return array{model: class-string<Model>, status: class-string<\BackedEnum>, priority: class-string<\BackedEnum>|null, assignment: string}|null
     */
    private function resourceConfig(string $resource): ?array
    {
        return [
            'projects' => [
                'model' => Project::class,
                'status' => ProjectStatus::class,
                'priority' => ProjectPriority::class,
                'assignment' => 'manager_id',
            ],
            'tasks' => [
                'model' => Task::class,
                'status' => TaskStatus::class,
                'priority' => TaskPriority::class,
                'assignment' => 'assigned_to',
            ],
            'tickets' => [
                'model' => Ticket::class,
                'status' => TicketStatus::class,
                'priority' => TicketPriority::class,
                'assignment' => 'assigned_to',
            ],
            'assets' => [
                'model' => Asset::class,
                'status' => AssetStatus::class,
                'priority' => null,
                'assignment' => 'assigned_to',
            ],
        ][$resource] ?? null;
    }

    /**
     * @param  iterable<int, Model>  $models
     * @param  array{model: class-string<Model>, status: class-string<\BackedEnum>, priority: class-string<\BackedEnum>|null, assignment: string}  $config
     */
    private function validateAction(iterable $models, array $config, string $action, ?string $value): void
    {
        if ($action === 'status' && $config['status']::tryFrom((string) $value) === null) {
            throw ValidationException::withMessages([
                'value' => __('Select a valid status.'),
            ]);
        }

        if ($action === 'priority') {
            if (! $config['priority']) {
                throw ValidationException::withMessages([
                    'action' => __('Priority changes are not available for this resource.'),
                ]);
            }

            if ($config['priority']::tryFrom((string) $value) === null) {
                throw ValidationException::withMessages([
                    'value' => __('Select a valid priority.'),
                ]);
            }
        }

        if ($action === 'assign' && filled($value)) {
            if (! ctype_digit((string) $value) || ! User::query()->whereKey((int) $value)->exists()) {
                throw ValidationException::withMessages([
                    'value' => __('Select a valid user.'),
                ]);
            }
        }

        if ($action === 'status'
            && $config['model'] === Asset::class
            && $value === AssetStatus::Assigned->value
            && collect($models)->contains(fn (Asset $asset) => $asset->assigned_to === null)) {
            throw ValidationException::withMessages([
                'value' => __('Assign an owner before setting assets to assigned.'),
            ]);
        }
    }

    private function applyStatus(Model $model, ?string $status): void
    {
        if ($model instanceof Asset) {
            $attributes = ['status' => $status];

            if (in_array($status, [AssetStatus::Available->value, AssetStatus::Retired->value], true)) {
                $attributes['assigned_to'] = null;
            }

            $model->update($attributes);

            return;
        }

        if ($model instanceof Task) {
            $model->update([
                'status' => $status,
                'completed_at' => $status === TaskStatus::Completed->value
                    ? ($model->completed_at ?? now())
                    : null,
            ]);

            return;
        }

        if ($model instanceof Ticket) {
            $isResolved = in_array($status, [TicketStatus::Resolved->value, TicketStatus::Closed->value], true);

            $model->update([
                'status' => $status,
                'resolved_at' => $isResolved
                    ? ($model->resolved_at ?? now())
                    : null,
            ]);

            return;
        }

        $model->update(['status' => $status]);
    }

    private function applyAssignment(Model $model, string $field, ?string $value): void
    {
        $assignedId = filled($value) ? (int) $value : null;
        $attributes = [$field => $assignedId];

        if ($model instanceof Asset) {
            if ($assignedId && $model->status === AssetStatus::Available) {
                $attributes['status'] = AssetStatus::Assigned->value;
            }

            if (! $assignedId && $model->status === AssetStatus::Assigned) {
                $attributes['status'] = AssetStatus::Available->value;
            }
        }

        $model->update($attributes);
    }
}
