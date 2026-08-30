<?php

namespace App\Http\Controllers;

use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Project::class);

        $search = trim((string) $request->query('search'));
        $companyId = (string) $request->query('company_id');
        $status = (string) $request->query('status');
        $priority = (string) $request->query('priority');
        $managerId = (string) $request->query('manager_id');

        $allowedSorts = [
            'code',
            'name',
            'status',
            'priority',
            'start_date',
            'due_date',
            'created_at',
        ];

        $sort = (string) $request->query('sort', 'name');

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'name';
        }

        $direction = strtolower((string) $request->query('direction', 'asc'));

        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        $projects = Project::query()
            ->with([
                'company:id,name',
                'contact:id,first_name,last_name',
                'manager:id,name',
            ])
            ->withCount('tasks')
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $query) use ($search) {
                    $query
                        ->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas(
                            'company',
                            fn (Builder $companyQuery) => $companyQuery
                                ->where('name', 'like', "%{$search}%")
                        );
                });
            })
            ->when(
                ctype_digit($companyId),
                fn (Builder $query) => $query->where('company_id', (int) $companyId)
            )
            ->when(
                ProjectStatus::tryFrom($status) !== null,
                fn (Builder $query) => $query->where('status', $status)
            )
            ->when(
                ProjectPriority::tryFrom($priority) !== null,
                fn (Builder $query) => $query->where('priority', $priority)
            )
            ->when(
                ctype_digit($managerId),
                fn (Builder $query) => $query->where('manager_id', (int) $managerId)
            )
            ->orderBy($sort, $direction)
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('projects.index', [
            'projects' => $projects,
            'companies' => $this->companyOptions(),
            'managers' => $this->userOptions(),
            'statuses' => ProjectStatus::cases(),
            'priorities' => ProjectPriority::cases(),
            'filters' => [
                'search' => $search,
                'company_id' => $companyId,
                'status' => $status,
                'priority' => $priority,
                'manager_id' => $managerId,
                'sort' => $sort,
                'direction' => $direction,
            ],
        ]);
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', Project::class);

        $companyId = (int) $request->query('company', 0);
        $project = new Project([
            'company_id' => Company::query()->whereKey($companyId)->exists()
                ? $companyId
                : null,
            'status' => ProjectStatus::Planned,
            'priority' => ProjectPriority::Medium,
        ]);

        return view('projects.create', $this->formData($project));
    }

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        Gate::authorize('create', Project::class);

        $project = Project::create([
            ...$request->validated(),
            'created_by' => Auth::id(),
        ]);

        return redirect()
            ->route('projects.show', $project)
            ->with('status', 'Project created successfully.');
    }

    public function show(Project $project): View
    {
        Gate::authorize('view', $project);

        $project->load([
            'company',
            'contact',
            'manager',
            'creator',
            'tasks' => fn ($query) => $query
                ->with('assignee:id,name')
                ->orderByRaw('due_date is null')
                ->orderBy('due_date')
                ->limit(10),
        ]);

        return view('projects.show', [
            'project' => $project,
        ]);
    }

    public function edit(Project $project): View
    {
        Gate::authorize('update', $project);

        return view('projects.edit', $this->formData($project));
    }

    public function update(
        UpdateProjectRequest $request,
        Project $project
    ): RedirectResponse {
        Gate::authorize('update', $project);

        $project->update($request->validated());

        return redirect()
            ->route('projects.show', $project)
            ->with('status', 'Project updated successfully.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        Gate::authorize('delete', $project);

        DB::transaction(function () use ($project) {
            $project->tasks()->delete();
            $project->delete();
        });

        return redirect()
            ->route('projects.index')
            ->with('status', 'Project deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Project $project): array
    {
        return [
            'project' => $project,
            'companies' => $this->companyOptions(),
            'contacts' => Contact::query()
                ->with('company:id,name')
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get(),
            'managers' => $this->userOptions(),
            'statuses' => ProjectStatus::cases(),
            'priorities' => ProjectPriority::cases(),
        ];
    }

    private function companyOptions()
    {
        return Company::query()
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function userOptions()
    {
        return User::query()
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }
}
