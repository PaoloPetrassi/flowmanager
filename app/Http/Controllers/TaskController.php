<?php

namespace App\Http\Controllers;

use App\Enums\TaskPriority;
use App\Enums\TaskRecurrence;
use App\Enums\TaskStatus;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\CollaborationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Task::class);

        $search = trim((string) $request->query('search'));
        $projectId = (string) $request->query('project_id');
        $status = (string) $request->query('status');
        $priority = (string) $request->query('priority');
        $assigneeId = (string) $request->query('assigned_to');

        $allowedSorts = ['title', 'status', 'priority', 'due_date', 'created_at'];
        $sort = (string) $request->query('sort', 'due_date');
        $sort = in_array($sort, $allowedSorts, true) ? $sort : 'due_date';
        $direction = strtolower((string) $request->query('direction', 'asc'));
        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'asc';

        $tasks = Task::query()
            ->operational()
            ->with(['project:id,code,name,company_id', 'project.company:id,name', 'assignee:id,name', 'milestone:id,name'])
            ->withCount('subtasks')
            ->withSum('timeEntries as tracked_minutes_sum', 'minutes')
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('project', fn (Builder $projectQuery) => $projectQuery
                            ->where('code', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%"));
                });
            })
            ->when(ctype_digit($projectId), fn (Builder $query) => $query->where('project_id', (int) $projectId))
            ->when(TaskStatus::tryFrom($status) !== null, fn (Builder $query) => $query->where('status', $status))
            ->when(TaskPriority::tryFrom($priority) !== null, fn (Builder $query) => $query->where('priority', $priority))
            ->when(ctype_digit($assigneeId), fn (Builder $query) => $query->where('assigned_to', (int) $assigneeId))
            ->when($sort === 'due_date', fn (Builder $query) => $query->orderByRaw('due_date is null'))
            ->orderBy($sort, $direction)
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('tasks.index', [
            'tasks' => $tasks,
            'projects' => $this->projectOptions(),
            'users' => $this->userOptions(),
            'statuses' => TaskStatus::cases(),
            'priorities' => TaskPriority::cases(),
            'filters' => [
                'search' => $search,
                'project_id' => $projectId,
                'status' => $status,
                'priority' => $priority,
                'assigned_to' => $assigneeId,
                'sort' => $sort,
                'direction' => $direction,
            ],
        ]);
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', Task::class);

        $parent = Task::query()->operational()->find((int) $request->query('parent', 0));
        $projectId = $parent?->project_id ?: (int) $request->query('project', 0);

        $task = new Task([
            'project_id' => Project::query()->whereKey($projectId)->where('is_template', false)->exists() ? $projectId : null,
            'parent_id' => $parent?->id,
            'assigned_to' => $request->boolean('assign_to_me') ? Auth::id() : null,
            'status' => TaskStatus::Todo,
            'priority' => TaskPriority::Medium,
            'recurrence' => TaskRecurrence::None,
            'recurrence_interval' => 1,
        ]);

        return view('tasks.create', $this->formData($task));
    }

    public function store(StoreTaskRequest $request): RedirectResponse
    {
        Gate::authorize('create', Task::class);

        $data = $request->validated();
        $dependencyIds = $data['dependency_ids'] ?? [];
        unset($data['dependency_ids']);
        $data = $this->normalizeCompletion($data);

        $task = DB::transaction(function () use ($data, $dependencyIds) {
            $task = Task::create([...$data, 'created_by' => Auth::id()]);
            $task->dependencies()->sync($dependencyIds);

            return $task;
        });

        return redirect()->route('tasks.show', $task)
            ->with('status', __('Task created successfully.'));
    }

    public function show(Task $task): View
    {
        Gate::authorize('view', $task);

        $task->load([
            'project.company', 'assignee', 'creator', 'parent:id,title', 'milestone:id,name,due_date,completed_at',
            'subtasks' => fn ($query) => $query->with('assignee:id,name')->orderByRaw('due_date is null')->orderBy('due_date'),
            'dependencies:id,title,status,project_id', 'dependents:id,title,status,project_id',
            'timeEntries' => fn ($query) => $query->with('user:id,name')->latest('started_at')->limit(30),
        ]);

        return view('tasks.show', [
            'task' => $task,
            'runningTimer' => $task->timeEntries->first(fn ($entry) => $entry->user_id === Auth::id() && $entry->ended_at === null),
            'collaboration' => CollaborationService::dataFor($task),
            'collaborationType' => 'task',
        ]);
    }

    public function edit(Task $task): View
    {
        Gate::authorize('update', $task);
        $task->load('dependencies:id');

        return view('tasks.edit', $this->formData($task));
    }

    public function update(UpdateTaskRequest $request, Task $task): RedirectResponse
    {
        Gate::authorize('update', $task);

        $data = $request->validated();
        $dependencyIds = $data['dependency_ids'] ?? [];
        unset($data['dependency_ids']);

        DB::transaction(function () use ($data, $dependencyIds, $task) {
            $task->update($this->normalizeCompletion($data, $task));
            $task->dependencies()->sync($dependencyIds);
        });

        return redirect()->route('tasks.show', $task)
            ->with('status', __('Task updated successfully.'));
    }

    public function complete(Task $task): RedirectResponse
    {
        Gate::authorize('update', $task);

        if ($task->hasBlockingDependencies()) {
            return back()->with('error', __('This task cannot be completed until its dependencies are completed.'));
        }

        $task->update([
            'status' => TaskStatus::Completed,
            'completed_at' => $task->completed_at ?? now(),
        ]);

        $task->refresh();

        return back()->with('status', $task->next_recurrence_id
            ? __('Task completed and the next recurring task was created.')
            : __('Task marked as completed.'));
    }

    public function reopen(Task $task): RedirectResponse
    {
        Gate::authorize('update', $task);

        $task->update(['status' => TaskStatus::InProgress, 'completed_at' => null]);

        return back()->with('status', __('Task reopened.'));
    }

    public function destroy(Task $task): RedirectResponse
    {
        Gate::authorize('delete', $task);

        DB::transaction(function () use ($task) {
            $task->subtasks()->delete();
            $task->delete();
        });

        return redirect()->route('tasks.index')
            ->with('status', __('Task deleted successfully.'));
    }

    private function formData(Task $task): array
    {
        return [
            'task' => $task,
            'projects' => $this->projectOptions(),
            'users' => $this->userOptions(),
            'statuses' => TaskStatus::cases(),
            'priorities' => TaskPriority::cases(),
            'recurrences' => TaskRecurrence::cases(),
            'taskOptions' => Task::query()
                ->when($task->exists, fn (Builder $query) => $query->where('id', '!=', $task->id))
                ->whereHas('project', fn (Builder $query) => $query->where('is_template', false))
                ->orderBy('title')
                ->get(['id', 'project_id', 'title']),
            'milestones' => Milestone::query()->orderBy('due_date')->orderBy('name')->get(['id', 'project_id', 'name', 'due_date']),
            'selectedDependencies' => $task->exists && $task->relationLoaded('dependencies')
                ? $task->dependencies->pluck('id')->all()
                : [],
        ];
    }

    private function normalizeCompletion(array $data, ?Task $task = null): array
    {
        if (($data['status'] ?? null) === TaskStatus::Completed->value) {
            $data['completed_at'] = $task?->completed_at ?? now();
        } else {
            $data['completed_at'] = null;
        }

        return $data;
    }

    private function projectOptions()
    {
        return Project::query()
            ->where('is_template', false)
            ->with('company:id,name')
            ->orderBy('name')
            ->get(['id', 'company_id', 'code', 'name']);
    }

    private function userOptions()
    {
        return User::query()->orderBy('name')->get(['id', 'name', 'email']);
    }
}
