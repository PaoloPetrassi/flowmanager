<?php

namespace App\Http\Controllers;

use App\Enums\ProjectStatus;
use App\Enums\TaskStatus;
use App\Models\Company;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProjectTemplateController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Project::class);

        return view('project-templates.index', [
            'templates' => Project::query()
                ->where('is_template', true)
                ->withCount(['tasks', 'milestones'])
                ->orderBy('name')
                ->get(),
            'companies' => Company::query()->orderBy('name')->get(['id', 'name']),
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }

    public function storeFromProject(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('create', Project::class);
        $this->authorize('view', $project);
        abort_if($project->is_template, 404);

        $template = DB::transaction(function () use ($project, $request) {
            $template = $project->replicate([
                'code',
                'contact_id',
                'manager_id',
                'status',
                'start_date',
                'due_date',
                'progress_override',
            ]);

            $template->fill([
                'code' => $this->uniqueCode('TPL'),
                'contact_id' => null,
                'manager_id' => null,
                'name' => $project->name.' '.__('Template'),
                'status' => ProjectStatus::Planned,
                'is_template' => true,
                'progress_override' => null,
                'start_date' => null,
                'due_date' => null,
                'created_by' => $request->user()->id,
            ]);
            $template->save();

            $milestoneMap = $this->copyMilestones(
                source: $project,
                target: $template,
                userId: $request->user()->id,
                preserveDates: false
            );

            $this->copyProjectTasks(
                source: $project,
                target: $template,
                userId: $request->user()->id,
                milestoneMap: $milestoneMap,
                preserveAssignments: false,
                preserveDueDates: false
            );

            return $template;
        });

        return redirect()->route('project-templates.index')
            ->with('status', __('Project template created: :name', ['name' => $template->name]));
    }

    public function instantiate(Request $request, Project $template): RedirectResponse
    {
        $this->authorize('create', Project::class);
        abort_unless($template->is_template, 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'company_id' => ['required', 'integer', Rule::exists('companies', 'id')->whereNull('deleted_at')],
            'manager_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $project = DB::transaction(function () use ($template, $data, $request) {
            $project = $template->replicate([
                'code',
                'company_id',
                'contact_id',
                'manager_id',
                'is_template',
                'start_date',
                'due_date',
                'progress_override',
            ]);

            $project->fill([
                'code' => $this->uniqueCode('PRJ'),
                'name' => $data['name'],
                'company_id' => $data['company_id'],
                'contact_id' => null,
                'manager_id' => $data['manager_id'] ?? null,
                'is_template' => false,
                'status' => ProjectStatus::Planned,
                'progress_override' => null,
                'start_date' => $data['start_date'] ?? null,
                'due_date' => $data['due_date'] ?? null,
                'created_by' => $request->user()->id,
            ]);
            $project->save();

            if ($project->manager_id) {
                $project->teamMembers()->syncWithoutDetaching([
                    $project->manager_id => ['role' => __('Project manager')],
                ]);
            }

            $milestoneMap = $this->copyMilestones(
                source: $template,
                target: $project,
                userId: $request->user()->id,
                preserveDates: false
            );

            $this->copyProjectTasks(
                source: $template,
                target: $project,
                userId: $request->user()->id,
                milestoneMap: $milestoneMap,
                preserveAssignments: false,
                preserveDueDates: false
            );

            return $project;
        });

        return redirect()->route('projects.show', $project)
            ->with('status', __('Project created from template.'));
    }

    public function duplicate(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('create', Project::class);
        $this->authorize('view', $project);
        abort_if($project->is_template, 404);

        $copy = DB::transaction(function () use ($project, $request) {
            $copy = $project->replicate(['code', 'status', 'progress_override']);
            $copy->fill([
                'code' => $this->uniqueCode('PRJ'),
                'name' => $project->name.' '.__('Copy'),
                'status' => ProjectStatus::Planned,
                'progress_override' => null,
                'is_template' => false,
                'created_by' => $request->user()->id,
            ]);
            $copy->save();

            $project->teamMembers()->get()->each(function (User $user) use ($copy) {
                $copy->teamMembers()->syncWithoutDetaching([
                    $user->id => ['role' => $user->pivot->role],
                ]);
            });

            if ($copy->manager_id) {
                $copy->teamMembers()->syncWithoutDetaching([
                    $copy->manager_id => ['role' => __('Project manager')],
                ]);
            }

            $milestoneMap = $this->copyMilestones(
                source: $project,
                target: $copy,
                userId: $request->user()->id,
                preserveDates: true
            );

            $this->copyProjectTasks(
                source: $project,
                target: $copy,
                userId: $request->user()->id,
                milestoneMap: $milestoneMap,
                preserveAssignments: true,
                preserveDueDates: true
            );

            return $copy;
        });

        return redirect()->route('projects.show', $copy)
            ->with('status', __('Project duplicated.'));
    }

    public function destroy(Project $template): RedirectResponse
    {
        $this->authorize('delete', $template);
        abort_unless($template->is_template, 404);

        DB::transaction(function () use ($template): void {
            $template->tasks()->delete();
            $template->milestones()->delete();
            $template->teamMembers()->detach();
            $template->delete();
        });

        return back()->with('status', __('Project template deleted.'));
    }

    /**
     * @return array<int, int> Map of source milestone IDs to copied milestone IDs.
     */
    private function copyMilestones(
        Project $source,
        Project $target,
        int $userId,
        bool $preserveDates
    ): array {
        $map = [];

        foreach ($source->milestones()->orderBy('id')->get() as $sourceMilestone) {
            $milestone = $sourceMilestone->replicate(['project_id', 'completed_at', 'created_by']);
            $milestone->fill([
                'project_id' => $target->id,
                'due_date' => $preserveDates ? $sourceMilestone->due_date : null,
                'completed_at' => null,
                'created_by' => $userId,
            ]);
            $milestone->save();

            $map[$sourceMilestone->id] = $milestone->id;
        }

        return $map;
    }

    /**
     * Copy a project's task tree and rebuild task dependencies against the copied IDs.
     *
     * @param  array<int, int>  $milestoneMap
     */
    private function copyProjectTasks(
        Project $source,
        Project $target,
        int $userId,
        array $milestoneMap,
        bool $preserveAssignments,
        bool $preserveDueDates
    ): void {
        $taskMap = [];
        $dependencyMap = [];

        $source->tasks()
            ->whereNull('parent_id')
            ->with('dependencies:id')
            ->orderBy('id')
            ->get()
            ->each(function (Task $task) use (
                $target,
                $userId,
                $milestoneMap,
                $preserveAssignments,
                $preserveDueDates,
                &$taskMap,
                &$dependencyMap
            ): void {
                $this->copyTaskTree(
                    source: $task,
                    project: $target,
                    userId: $userId,
                    milestoneMap: $milestoneMap,
                    preserveAssignments: $preserveAssignments,
                    preserveDueDates: $preserveDueDates,
                    taskMap: $taskMap,
                    dependencyMap: $dependencyMap
                );
            });

        foreach ($dependencyMap as $sourceTaskId => $sourceDependencyIds) {
            $copiedTaskId = $taskMap[$sourceTaskId] ?? null;

            if (! $copiedTaskId) {
                continue;
            }

            $copiedDependencyIds = collect($sourceDependencyIds)
                ->map(fn (int $dependencyId) => $taskMap[$dependencyId] ?? null)
                ->filter()
                ->values()
                ->all();

            Task::query()->findOrFail($copiedTaskId)->dependencies()->sync($copiedDependencyIds);
        }
    }

    /**
     * @param  array<int, int>  $milestoneMap
     * @param  array<int, int>  $taskMap
     * @param  array<int, array<int>>  $dependencyMap
     */
    private function copyTaskTree(
        Task $source,
        Project $project,
        int $userId,
        array $milestoneMap,
        bool $preserveAssignments,
        bool $preserveDueDates,
        array &$taskMap,
        array &$dependencyMap,
        ?Task $parent = null
    ): Task {
        $source->loadMissing('dependencies:id');

        $task = $source->replicate([
            'project_id',
            'parent_id',
            'milestone_id',
            'assigned_to',
            'status',
            'completed_at',
            'due_date',
            'due_reminder_sent_at',
            'recurrence_source_id',
            'next_recurrence_id',
            'created_by',
        ]);

        $task->fill([
            'project_id' => $project->id,
            'parent_id' => $parent?->id,
            'milestone_id' => $source->milestone_id
                ? ($milestoneMap[$source->milestone_id] ?? null)
                : null,
            'assigned_to' => $preserveAssignments ? $source->assigned_to : null,
            'status' => TaskStatus::Todo,
            'completed_at' => null,
            'due_date' => $preserveDueDates ? $source->due_date : null,
            'due_reminder_sent_at' => null,
            'recurrence_source_id' => null,
            'next_recurrence_id' => null,
            'created_by' => $userId,
        ]);
        $task->save();

        $taskMap[$source->id] = $task->id;
        $dependencyMap[$source->id] = $source->dependencies->pluck('id')->map(fn ($id) => (int) $id)->all();

        $source->subtasks()
            ->with('dependencies:id')
            ->orderBy('id')
            ->get()
            ->each(function (Task $subtask) use (
                $project,
                $userId,
                $milestoneMap,
                $preserveAssignments,
                $preserveDueDates,
                &$taskMap,
                &$dependencyMap,
                $task
            ): void {
                $this->copyTaskTree(
                    source: $subtask,
                    project: $project,
                    userId: $userId,
                    milestoneMap: $milestoneMap,
                    preserveAssignments: $preserveAssignments,
                    preserveDueDates: $preserveDueDates,
                    taskMap: $taskMap,
                    dependencyMap: $dependencyMap,
                    parent: $task
                );
            });

        return $task;
    }

    private function uniqueCode(string $prefix): string
    {
        do {
            $code = $prefix.'-'.now()->format('ymd').'-'.strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
        } while (Project::withTrashed()->where('code', $code)->exists());

        return $code;
    }
}
