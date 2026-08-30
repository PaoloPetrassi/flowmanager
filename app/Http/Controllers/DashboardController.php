<?php

namespace App\Http\Controllers;

use App\Enums\AssetStatus;
use App\Enums\ProjectStatus;
use App\Models\Asset;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Project;
use App\Models\Role;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\User;
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
                    ->where('status', ProjectStatus::Active->value)
                    ->count(),
                'icon' => 'bi-kanban',
                'url' => Gate::allows('viewAny', Project::class)
                    ? route('projects.index', ['status' => ProjectStatus::Active->value])
                    : null,
            ],
            [
                'label' => __('Open tasks'),
                'value' => Task::query()->open()->count(),
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
                ->overdue()
                ->where('assigned_to', $currentUser->id)
                ->count();

            $myTasks = Task::query()
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

        if (Gate::allows('viewAny', User::class)) {
            $latestUsers = User::with('roles')
                ->latest()
                ->limit(5)
                ->get();
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
        ]);
    }
}
