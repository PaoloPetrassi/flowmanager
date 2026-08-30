<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Administrator',
                'slug' => 'administrator',
                'description' => 'Full access to all application features.',
                'is_system' => true,
            ],
            [
                'name' => 'Manager',
                'slug' => 'manager',
                'description' => 'Management access to operational modules.',
                'is_system' => true,
            ],
            [
                'name' => 'Operator',
                'slug' => 'operator',
                'description' => 'Operational access to daily activities.',
                'is_system' => true,
            ],
            [
                'name' => 'Viewer',
                'slug' => 'viewer',
                'description' => 'Read-only access to application data.',
                'is_system' => true,
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['slug' => $role['slug']],
                $role
            );
        }

        $permissions = [
            ['name' => 'View users', 'slug' => 'users.view'],
            ['name' => 'Manage users', 'slug' => 'users.manage'],
            ['name' => 'View roles', 'slug' => 'roles.view'],
            ['name' => 'Manage roles', 'slug' => 'roles.manage'],

            ['name' => 'View companies', 'slug' => 'companies.view'],
            ['name' => 'Create companies', 'slug' => 'companies.create'],
            ['name' => 'Update companies', 'slug' => 'companies.update'],
            ['name' => 'Delete companies', 'slug' => 'companies.delete'],

            ['name' => 'View contacts', 'slug' => 'contacts.view'],
            ['name' => 'Create contacts', 'slug' => 'contacts.create'],
            ['name' => 'Update contacts', 'slug' => 'contacts.update'],
            ['name' => 'Delete contacts', 'slug' => 'contacts.delete'],

            ['name' => 'View projects', 'slug' => 'projects.view'],
            ['name' => 'Create projects', 'slug' => 'projects.create'],
            ['name' => 'Update projects', 'slug' => 'projects.update'],
            ['name' => 'Delete projects', 'slug' => 'projects.delete'],

            ['name' => 'View tasks', 'slug' => 'tasks.view'],
            ['name' => 'Create tasks', 'slug' => 'tasks.create'],
            ['name' => 'Update tasks', 'slug' => 'tasks.update'],
            ['name' => 'Delete tasks', 'slug' => 'tasks.delete'],

            ['name' => 'View assets', 'slug' => 'assets.view'],
            ['name' => 'Create assets', 'slug' => 'assets.create'],
            ['name' => 'Update assets', 'slug' => 'assets.update'],
            ['name' => 'Delete assets', 'slug' => 'assets.delete'],
            ['name' => 'Assign assets', 'slug' => 'assets.assign'],

            ['name' => 'View tickets', 'slug' => 'tickets.view'],
            ['name' => 'Create tickets', 'slug' => 'tickets.create'],
            ['name' => 'Update tickets', 'slug' => 'tickets.update'],
            ['name' => 'Delete tickets', 'slug' => 'tickets.delete'],

            ['name' => 'View reports', 'slug' => 'reports.view'],
            ['name' => 'Export reports', 'slug' => 'reports.export'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }

        $administrator = Role::where('slug', 'administrator')->firstOrFail();
        $manager = Role::where('slug', 'manager')->firstOrFail();
        $operator = Role::where('slug', 'operator')->firstOrFail();
        $viewer = Role::where('slug', 'viewer')->firstOrFail();

        $administrator->permissions()->sync(
            Permission::query()->pluck('id')
        );

        $manager->permissions()->sync(
            Permission::query()
                ->whereNotIn('slug', [
                    'users.manage',
                    'roles.manage',
                ])
                ->pluck('id')
        );

        $operator->permissions()->sync(
            Permission::query()
                ->whereIn('slug', [
                    'companies.view',
                    'companies.create',
                    'companies.update',
                    'contacts.view',
                    'contacts.create',
                    'contacts.update',
                    'projects.view',
                    'tasks.view',
                    'tasks.create',
                    'tasks.update',
                    'assets.view',
                    'assets.assign',
                    'tickets.view',
                    'tickets.create',
                    'tickets.update',
                    'reports.view',
                ])
                ->pluck('id')
        );

        $viewer->permissions()->sync(
            Permission::query()
                ->whereIn('slug', [
                    'companies.view',
                    'contacts.view',
                    'projects.view',
                    'tasks.view',
                    'assets.view',
                    'tickets.view',
                    'reports.view',
                ])
                ->pluck('id')
        );
    }
}
