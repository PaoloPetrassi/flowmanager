<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Throwable;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', User::class);

        $search = trim((string) $request->query('search'));
        $roleId = (string) $request->query('role_id');

        $users = User::query()
            ->with('roles:id,name,slug')
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $query) use ($search) {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when(
                ctype_digit($roleId),
                fn (Builder $query) => $query->whereHas(
                    'roles',
                    fn (Builder $roleQuery) => $roleQuery->whereKey((int) $roleId)
                )
            )
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('users.index', [
            'users' => $users,
            'roles' => $this->roleOptions(),
            'filters' => [
                'search' => $search,
                'role_id' => $roleId,
            ],
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', User::class);

        return view('users.create', [
            'user' => new User,
            'roles' => $this->roleOptions(),
            'selectedRoles' => collect(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        Gate::authorize('create', User::class);

        $user = DB::transaction(function () use ($request) {
            $data = $request->validated();
            $roles = $data['roles'] ?? [];
            unset($data['roles']);

            $data['password_changed_at'] = now();

            $user = User::create($data);
            $user->roles()->sync($roles);

            AuditService::record(
                $user,
                'roles_updated',
                [],
                [
                    'roles' => Role::query()
                        ->whereIn('id', $roles)
                        ->orderBy('slug')
                        ->pluck('slug')
                        ->all(),
                ]
            );

            return $user;
        });

        if (config('flowmanager.security.require_email_verification', false)) {
            try {
                $user->sendEmailVerificationNotification();
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        return redirect()
            ->route('users.show', $user)
            ->with('status', __('User created successfully.'));
    }

    public function show(User $user): View
    {
        Gate::authorize('view', $user);

        $user->load('roles.permissions');

        return view('users.show', [
            'user' => $user,
        ]);
    }

    public function edit(User $user): View
    {
        Gate::authorize('update', $user);

        $user->load('roles');

        return view('users.edit', [
            'user' => $user,
            'roles' => $this->roleOptions(),
            'selectedRoles' => $user->roles->pluck('id'),
        ]);
    }

    public function update(
        UpdateUserRequest $request,
        User $user
    ): RedirectResponse {
        Gate::authorize('update', $user);

        DB::transaction(function () use ($request, $user) {
            $data = $request->validated();
            $roles = $data['roles'] ?? [];
            unset($data['roles']);

            $oldRoles = $user->roles()
                ->orderBy('slug')
                ->pluck('slug')
                ->all();

            $passwordChanged = ! empty($data['password']);
            $emailChanged = strtolower((string) $user->email) !== strtolower((string) $data['email']);

            if (! $passwordChanged) {
                unset($data['password']);
            } else {
                $data['password_changed_at'] = now();
            }

            $user->fill($data);

            if ($emailChanged) {
                $user->email_verified_at = null;
            }

            $user->save();
            $user->roles()->sync($roles);

            $newRoles = Role::query()
                ->whereIn('id', $roles)
                ->orderBy('slug')
                ->pluck('slug')
                ->all();

            if ($oldRoles !== $newRoles) {
                AuditService::record(
                    $user,
                    'roles_updated',
                    ['roles' => $oldRoles],
                    ['roles' => $newRoles]
                );
            }

            if ($passwordChanged) {
                AuditService::record(
                    $user,
                    'password_changed'
                );
            }
        });

        return redirect()
            ->route('users.show', $user)
            ->with('status', __('User updated successfully.'));
    }

    public function destroy(User $user): RedirectResponse
    {
        Gate::authorize('delete', $user);

        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('status', __('User deleted successfully.'));
    }

    private function roleOptions()
    {
        return Role::query()
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'is_system']);
    }
}
