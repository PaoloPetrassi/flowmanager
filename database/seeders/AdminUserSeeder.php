<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = config('flowmanager.admin');

        if (blank($admin['email']) || blank($admin['password'])) {
            throw new RuntimeException(
                'FLOWMANAGER_ADMIN_EMAIL and FLOWMANAGER_ADMIN_PASSWORD must be configured.'
            );
        }

        $user = User::updateOrCreate(
            [
                'email' => $admin['email'],
            ],
            [
                'name' => $admin['name'],
                'password' => $admin['password'],
            ]
        );

        if ($user->email_verified_at === null) {
            $user->forceFill([
                'email_verified_at' => now(),
            ])->save();
        }

        $administratorRole = Role::where('slug', 'administrator')
            ->firstOrFail();

        $user->roles()->sync([
            $administratorRole->id,
        ]);
    }
}
