<?php

namespace App\Http\Controllers;

use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class BulkActionController extends Controller
{
    public function update(Request $request, string $resource): RedirectResponse
    {
        $config = [
            'projects' => [Project::class, ProjectStatus::class, ProjectPriority::class],
            'tasks' => [Task::class, TaskStatus::class, TaskPriority::class],
            'tickets' => [Ticket::class, TicketStatus::class, TicketPriority::class],
        ][$resource] ?? null;

        abort_unless($config, 404);

        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'max:100'],
            'ids.*' => ['integer'],
            'action' => ['required', 'in:status,priority,delete'],
            'value' => ['nullable', 'string'],
        ]);

        [$modelClass, $statusEnum, $priorityEnum] = $config;
        $models = $modelClass::query()->whereIn('id', array_unique($data['ids']))->get();

        if ($models->isEmpty()) {
            throw ValidationException::withMessages(['ids' => __('No matching records were found.')]);
        }

        if ($data['action'] === 'status' && $statusEnum::tryFrom((string) $data['value']) === null) {
            throw ValidationException::withMessages(['value' => __('Select a valid status.')]);
        }

        if ($data['action'] === 'priority' && $priorityEnum::tryFrom((string) $data['value']) === null) {
            throw ValidationException::withMessages(['value' => __('Select a valid priority.')]);
        }

        DB::transaction(function () use ($models, $data): void {
            foreach ($models as $model) {
                Gate::authorize($data['action'] === 'delete' ? 'delete' : 'update', $model);

                if ($data['action'] === 'delete') {
                    $model->delete();
                    continue;
                }

                $model->update([$data['action'] => $data['value']]);
            }
        });

        return back()->with('status', __('Bulk action completed for :count records.', ['count' => $models->count()]));
    }
}
