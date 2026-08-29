<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
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
                'label' => 'Users',
                'value' => User::count(),
                'icon' => 'bi-people',
            ],
            [
                'label' => 'Roles',
                'value' => Role::count(),
                'icon' => 'bi-shield-check',
            ],
            [
                'label' => 'Permissions',
                'value' => Permission::count(),
                'icon' => 'bi-key',
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