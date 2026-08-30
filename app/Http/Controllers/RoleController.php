<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Role::class);

        $search = trim((string) $request->query('search'));

        $roles = Role::query()
            ->withCount(['users', 'permissions'])
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $query) use ($search) {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('is_system')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('roles.index', [
            'roles' => $roles,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Role::class);

        return view('roles.create', [
            'role' => new Role(),
            'permissionGroups' => $this->permissionGroups(),
            'selectedPermissions' => collect(),
        ]);
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        Gate::authorize('create', Role::class);

        $role = DB::transaction(function () use ($request) {
            $data = $request->validated();
            $permissions = $data['permissions'] ?? [];
            unset($data['permissions']);

            $role = Role::create([
                ...$data,
                'is_system' => false,
            ]);

            $role->permissions()->sync($permissions);

            return $role;
        });

        return redirect()
            ->route('roles.show', $role)
            ->with('status', __('Role created successfully.'));
    }

    public function show(Role $role): View
    {
        Gate::authorize('view', $role);

        $role->load([
            'permissions',
            'users' => fn ($query) => $query->orderBy('name'),
        ]);

        return view('roles.show', [
            'role' => $role,
        ]);
    }

    public function edit(Role $role): View
    {
        Gate::authorize('update', $role);

        $role->load('permissions');

        return view('roles.edit', [
            'role' => $role,
            'permissionGroups' => $this->permissionGroups(),
            'selectedPermissions' => $role->permissions->pluck('id'),
        ]);
    }

    public function update(
        UpdateRoleRequest $request,
        Role $role
    ): RedirectResponse {
        Gate::authorize('update', $role);

        DB::transaction(function () use ($request, $role) {
            $data = $request->validated();
            $permissions = $data['permissions'] ?? [];
            unset($data['permissions']);

            $role->update($data);
            $role->permissions()->sync($permissions);
        });

        return redirect()
            ->route('roles.show', $role)
            ->with('status', __('Role updated successfully.'));
    }

    public function destroy(Role $role): RedirectResponse
    {
        Gate::authorize('delete', $role);

        $role->delete();

        return redirect()
            ->route('roles.index')
            ->with('status', __('Role deleted successfully.'));
    }

    private function permissionGroups()
    {
        return Permission::query()
            ->orderBy('slug')
            ->get()
            ->groupBy(function (Permission $permission) {
                return explode('.', $permission->slug, 2)[0];
            });
    }
}
