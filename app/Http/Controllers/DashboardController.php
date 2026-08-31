<?php

namespace App\Http\Controllers;

use App\Enums\AssetStatus;
use App\Enums\ProjectStatus;
use App\Enums\TaskStatus;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Asset;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Project;
use App\Models\Role;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the application dashboard.
     */
    public function index(): View
    {
        $currentUser = Auth::user();

        $currentUser->loadMissing('roles.permissions');

        $dashboardWidgets = $currentUser->preference()->firstOrCreate([])->dashboard_widgets ?: ['stats', 'my_work', 'projects', 'access', 'charts'];

        $permissionCount = $currentUser->roles
            ->flatMap(function (Role $role) {
                return $role->permissions;
            })
            ->unique('id')
            ->count();

        $stats = collect([
            [
                'label' => __('Companies'),
                'value' => Company::count(),
                'icon' => 'bi-buildings',
                'url' => Gate::allows('viewAny', Company::class)
                    ? route('companies.index')
                    : null,
            ],
            [
                'label' => __('Contacts'),
                'value' => Contact::count(),
                'icon' => 'bi-person-vcard',
                'url' => Gate::allows('viewAny', Contact::class)
                    ? route('contacts.index')
                    : null,
            ],
            [
                'label' => __('Active projects'),
                'value' => Project::query()
                    ->operational()
                    ->where('status', ProjectStatus::Active->value)
                    ->count(),
                'icon' => 'bi-kanban',
                'url' => Gate::allows('viewAny', Project::class)
                    ? route('projects.index', ['status' => ProjectStatus::Active->value])
                    : null,
            ],
            [
                'label' => __('Open tasks'),
                'value' => Task::query()->operational()->open()->count(),
                'icon' => 'bi-check2-square',
                'url' => Gate::allows('viewAny', Task::class)
                    ? route('tasks.index')
                    : null,
            ],
            [
                'label' => __('Assets in service'),
                'value' => Asset::query()
                    ->where('status', '!=', AssetStatus::Retired->value)
                    ->count(),
                'icon' => 'bi-laptop',
                'url' => Gate::allows('viewAny', Asset::class)
                    ? route('assets.index')
                    : null,
            ],
            [
                'label' => __('Open tickets'),
                'value' => Ticket::query()->open()->count(),
                'icon' => 'bi-ticket-perforated',
                'url' => Gate::allows('viewAny', Ticket::class)
                    ? route('tickets.index')
                    : null,
            ],
        ])->all();

        $myTasks = collect();
        $myTickets = collect();
        $managedProjects = collect();
        $overdueTaskCount = 0;

        if (Gate::allows('viewAny', Task::class)) {
            $overdueTaskCount = Task::query()
                ->operational()
                ->overdue()
                ->where('assigned_to', $currentUser->id)
                ->count();

            $myTasks = Task::query()
                ->operational()
                ->open()
                ->where('assigned_to', $currentUser->id)
                ->with(['project:id,code,name,company_id', 'project.company:id,name'])
                ->orderByRaw('due_date is null')
                ->orderBy('due_date')
                ->limit(6)
                ->get();
        }

        if (Gate::allows('viewAny', Ticket::class)) {
            $myTickets = Ticket::query()
                ->open()
                ->where('assigned_to', $currentUser->id)
                ->with('company:id,name')
                ->latest('updated_at')
                ->limit(6)
                ->get();
        }

        if (Gate::allows('viewAny', Project::class)) {
            $managedProjects = Project::query()
                ->operational()
                ->where('manager_id', $currentUser->id)
                ->whereNotIn('status', [
                    ProjectStatus::Completed->value,
                    ProjectStatus::Cancelled->value,
                ])
                ->with('company:id,name')
                ->withCount([
                    'tasks',
                    'tasks as open_tasks_count' => fn ($query) => $query->open(),
                ])
                ->orderByRaw('due_date is null')
                ->orderBy('due_date')
                ->limit(6)
                ->get();
        }

        $latestUsers = collect();
        $teamWorkload = collect();

        if (Gate::allows('viewAny', User::class)) {
            $latestUsers = User::with('roles')
                ->latest()
                ->limit(5)
                ->get();

            $teamWorkload = User::query()
                ->withCount([
                    'assignedTasks as open_tasks_count' => fn ($query) => $query->open(),
                    'assignedTickets as open_tickets_count' => fn ($query) => $query->open(),
                ])
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(function (User $user) {
                    $user->workload_total = $user->open_tasks_count + $user->open_tickets_count;

                    return $user;
                })
                ->filter(fn (User $user) => $user->workload_total > 0)
                ->sortByDesc('workload_total')
                ->take(6)
                ->values();
        }

        return view('dashboard.index', [
            'stats' => $stats,
            'latestUsers' => $latestUsers,
            'currentUser' => $currentUser,
            'permissionCount' => $permissionCount,
            'myTasks' => $myTasks,
            'myTickets' => $myTickets,
            'managedProjects' => $managedProjects,
            'overdueTaskCount' => $overdueTaskCount,
            'taskStatusChart' => $this->taskStatusChart(),
            'ticketPriorityChart' => $this->ticketPriorityChart(),
            'trendSeries' => $this->trendSeries(),
            'teamWorkload' => $teamWorkload,
            'dashboardWidgets' => $dashboardWidgets,
        ]);
    }

    private function taskStatusChart(): array
    {
        if (! Gate::allows('viewAny', Task::class)) {
            return [];
        }

        $counts = Task::query()
            ->operational()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return collect(TaskStatus::cases())
            ->map(fn (TaskStatus $status) => [
                'key' => $status->value,
                'label' => $status->label(),
                'value' => (int) ($counts[$status->value] ?? 0),
            ])
            ->all();
    }

    private function ticketPriorityChart(): array
    {
        if (! Gate::allows('viewAny', Ticket::class)) {
            return [];
        }

        $counts = Ticket::query()
            ->open()
            ->selectRaw('priority, COUNT(*) as aggregate')
            ->groupBy('priority')
            ->pluck('aggregate', 'priority');

        return collect(TicketPriority::cases())
            ->map(fn (TicketPriority $priority) => [
                'key' => $priority->value,
                'label' => $priority->label(),
                'value' => (int) ($counts[$priority->value] ?? 0),
            ])
            ->all();
    }

    private function trendSeries(): array
    {
        $endMonth = CarbonImmutable::now()->startOfMonth();
        $months = collect(range(5, 0))
            ->map(fn (int $offset) => $endMonth->subMonths($offset));

        $start = $months->first()->startOfMonth();
        $end = $months->last()->endOfMonth();

        $completedTasks = Gate::allows('viewAny', Task::class)
            ? Task::query()
                ->operational()
                ->whereNotNull('completed_at')
                ->whereBetween('completed_at', [$start, $end])
                ->get(['completed_at'])
                ->groupBy(fn (Task $task) => $task->completed_at->format('Y-m'))
                ->map->count()
            : collect();

        $resolvedTickets = Gate::allows('viewAny', Ticket::class)
            ? Ticket::query()
                ->whereNotNull('resolved_at')
                ->whereBetween('resolved_at', [$start, $end])
                ->get(['resolved_at'])
                ->groupBy(fn (Ticket $ticket) => $ticket->resolved_at->format('Y-m'))
                ->map->count()
            : collect();

        $series = $months->map(function (CarbonImmutable $month) use ($completedTasks, $resolvedTickets) {
            $key = $month->format('Y-m');

            return [
                'key' => $key,
                'label' => $month->locale(app()->getLocale())->translatedFormat('M'),
                'tasks' => (int) ($completedTasks[$key] ?? 0),
                'tickets' => (int) ($resolvedTickets[$key] ?? 0),
            ];
        });

        $max = max(
            1,
            (int) $series->max('tasks'),
            (int) $series->max('tickets')
        );

        return [
            'items' => $series->all(),
            'max' => $max,
        ];
    }
}
