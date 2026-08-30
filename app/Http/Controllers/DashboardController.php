<?php

namespace App\Http\Controllers;

use App\Enums\AssetStatus;
use App\Enums\TaskStatus;
use App\Enums\TicketStatus;
use App\Models\Asset;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Project;
use App\Models\Role;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the application dashboard.
     */
    public function index(): View
    {
        $currentUser = Auth::user();

        $currentUser->load('roles.permissions');

        $permissionCount = $currentUser->roles
            ->flatMap(function (Role $role) {
                return $role->permissions;
            })
            ->unique('id')
            ->count();

        $stats = [
            [
                'label' => 'Companies',
                'value' => Company::count(),
                'icon' => 'bi-buildings',
            ],
            [
                'label' => 'Contacts',
                'value' => Contact::count(),
                'icon' => 'bi-person-vcard',
            ],
            [
                'label' => 'Projects',
                'value' => Project::count(),
                'icon' => 'bi-kanban',
            ],
            [
                'label' => 'Open tasks',
                'value' => Task::query()
                    ->whereNotIn('status', [
                        TaskStatus::Completed->value,
                        TaskStatus::Cancelled->value,
                    ])
                    ->count(),
                'icon' => 'bi-check2-square',
            ],
            [
                'label' => 'Assets',
                'value' => Asset::query()
                    ->where('status', '!=', AssetStatus::Retired->value)
                    ->count(),
                'icon' => 'bi-laptop',
            ],
            [
                'label' => 'Open tickets',
                'value' => Ticket::query()
                    ->whereNotIn('status', [
                        TicketStatus::Resolved->value,
                        TicketStatus::Closed->value,
                    ])
                    ->count(),
                'icon' => 'bi-ticket-perforated',
            ],
            [
                'label' => 'Users',
                'value' => User::count(),
                'icon' => 'bi-people',
            ],
            [
                'label' => 'Database',
                'value' => strtoupper(config('database.default')),
                'icon' => 'bi-database',
            ],
        ];

        $roles = Role::withCount('users')
            ->orderBy('name')
            ->get();

        $latestUsers = User::with('roles')
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard.index', [
            'stats' => $stats,
            'roles' => $roles,
            'latestUsers' => $latestUsers,
            'currentUser' => $currentUser,
            'permissionCount' => $permissionCount,
        ]);
    }
}
