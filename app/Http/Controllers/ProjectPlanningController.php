<?php

namespace App\Http\Controllers;

use App\Enums\ProjectStatus;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectPlanningController extends Controller
{
    public function gantt(Request $request): View
    {
        $this->authorize('viewAny', Project::class);

        $start = $request->date('start') ?? today()->startOfMonth();
        $end = $request->date('end') ?? today()->addMonths(3)->endOfMonth();

        if ($end->lte($start)) {
            $end = $start->copy()->addMonths(3);
        }

        $projects = Project::query()
            ->operational()
            ->whereNotIn('status', [ProjectStatus::Cancelled->value])
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('start_date', [$start, $end])
                    ->orWhereBetween('due_date', [$start, $end])
                    ->orWhere(function ($query) use ($start, $end) {
                        $query->whereDate('start_date', '<=', $start)
                            ->whereDate('due_date', '>=', $end);
                    });
            })
            ->with(['tasks' => fn ($query) => $query
                ->whereNotNull('due_date')
                ->orderBy('due_date'), 'company:id,name'])
            ->orderBy('start_date')
            ->get();

        return view('planning.gantt', [
            'projects' => $projects,
            'start' => $start,
            'end' => $end,
            'totalDays' => max(1, $start->diffInDays($end)),
        ]);
    }

    public function workload(Request $request): View
    {
        abort_unless($request->user()->hasPermission('workload.view'), 403);

        $users = User::query()
            ->withCount([
                'assignedTasks as open_tasks_count' => fn ($query) => $query
                    ->whereHas('project', fn ($projectQuery) => $projectQuery->where('is_template', false))
                    ->whereNotIn('status', [
                        TaskStatus::Completed->value,
                        TaskStatus::Cancelled->value,
                    ]),
                'managedProjects as active_projects_count' => fn ($query) => $query
                    ->where('is_template', false)
                    ->whereNotIn('status', [
                        ProjectStatus::Completed->value,
                        ProjectStatus::Cancelled->value,
                    ]),
            ])
            ->withSum([
                'assignedTasks as estimated_minutes_sum' => fn ($query) => $query
                    ->whereHas('project', fn ($projectQuery) => $projectQuery->where('is_template', false))
                    ->whereNotIn('status', [
                        TaskStatus::Completed->value,
                        TaskStatus::Cancelled->value,
                    ]),
            ], 'estimated_minutes')
            ->withSum('timeEntries as tracked_minutes_sum', 'minutes')
            ->orderByDesc('open_tasks_count')
            ->orderBy('name')
            ->get();

        return view('planning.workload', ['users' => $users]);
    }
}
