<?php

namespace App\Http\Controllers;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        $allowedSorts = [
            'title',
            'status',
            'priority',
            'due_date',
            'created_at',
        ];

        $sort = (string) $request->query('sort', 'due_date');

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'due_date';
        }

        $direction = strtolower((string) $request->query('direction', 'asc'));

        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        $tasks = Task::query()
            ->with([
                'project:id,code,name,company_id',
                'project.company:id,name',
                'assignee:id,name',
            ])
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $query) use ($search) {
                    $query
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas(
                            'project',
                            fn (Builder $projectQuery) => $projectQuery
                                ->where('code', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%")
                        );
                });
            })
            ->when(
                ctype_digit($projectId),
                fn (Builder $query) => $query->where('project_id', (int) $projectId)
            )
            ->when(
                TaskStatus::tryFrom($status) !== null,
                fn (Builder $query) => $query->where('status', $status)
            )
            ->when(
                TaskPriority::tryFrom($priority) !== null,
                fn (Builder $query) => $query->where('priority', $priority)
            )
            ->when(
                ctype_digit($assigneeId),
                fn (Builder $query) => $query->where('assigned_to', (int) $assigneeId)
            )
            ->when(
                $sort === 'due_date',
                fn (Builder $query) => $query->orderByRaw('due_date is null')
            )
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

        $projectId = (int) $request->query('project', 0);
        $task = new Task([
            'project_id' => Project::query()->whereKey($projectId)->exists()
                ? $projectId
                : null,
            'assigned_to' => $request->boolean('assign_to_me')
                ? Auth::id()
                : null,
            'status' => TaskStatus::Todo,
            'priority' => TaskPriority::Medium,
        ]);

        return view('tasks.create', $this->formData($task));
    }

    public function store(StoreTaskRequest $request): RedirectResponse
    {
        Gate::authorize('create', Task::class);

        $data = $this->normalizeCompletion($request->validated());

        $task = Task::create([
            ...$data,
            'created_by' => Auth::id(),
        ]);

        return redirect()
            ->route('tasks.show', $task)
            ->with('status', 'Task created successfully.');
    }

    public function show(Task $task): View
    {
        Gate::authorize('view', $task);

        $task->load([
            'project.company',
            'assignee',
            'creator',
        ]);

        return view('tasks.show', [
            'task' => $task,
        ]);
    }

    public function edit(Task $task): View
    {
        Gate::authorize('update', $task);

        return view('tasks.edit', $this->formData($task));
    }

    public function update(
        UpdateTaskRequest $request,
        Task $task
    ): RedirectResponse {
        Gate::authorize('update', $task);

        $task->update(
            $this->normalizeCompletion($request->validated(), $task)
        );

        return redirect()
            ->route('tasks.show', $task)
            ->with('status', 'Task updated successfully.');
    }

    public function complete(Task $task): RedirectResponse
    {
        Gate::authorize('update', $task);

        $task->update([
            'status' => TaskStatus::Completed,
            'completed_at' => $task->completed_at ?? now(),
        ]);

        return back()->with('status', 'Task marked as completed.');
    }

    public function reopen(Task $task): RedirectResponse
    {
        Gate::authorize('update', $task);

        $task->update([
            'status' => TaskStatus::InProgress,
            'completed_at' => null,
        ]);

        return back()->with('status', 'Task reopened.');
    }

    public function destroy(Task $task): RedirectResponse
    {
        Gate::authorize('delete', $task);

        $task->delete();

        return redirect()
            ->route('tasks.index')
            ->with('status', 'Task deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Task $task): array
    {
        return [
            'task' => $task,
            'projects' => $this->projectOptions(),
            'users' => $this->userOptions(),
            'statuses' => TaskStatus::cases(),
            'priorities' => TaskPriority::cases(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizeCompletion(array $data, ?Task $task = null): array
    {
        if ($data['status'] === TaskStatus::Completed->value) {
            $data['completed_at'] = $task?->completed_at ?? now();
        } else {
            $data['completed_at'] = null;
        }

        return $data;
    }

    private function projectOptions()
    {
        return Project::query()
            ->with('company:id,name')
            ->orderBy('name')
            ->get(['id', 'company_id', 'code', 'name']);
    }

    private function userOptions()
    {
        return User::query()
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }
}
