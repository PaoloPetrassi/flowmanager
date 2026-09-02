<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        if (! config('flowmanager.demo.enabled')) {
            return;
        }

        $demo = config('flowmanager.demo');

        if (blank($demo['email']) || blank($demo['password'])) {
            throw new RuntimeException(
                'FLOWMANAGER_DEMO_EMAIL and FLOWMANAGER_DEMO_PASSWORD must be configured when demo mode is enabled.'
            );
        }

        $role = Role::query()
            ->where('slug', $demo['role'])
            ->firstOrFail();

        $user = User::query()->updateOrCreate(
            ['email' => $demo['email']],
            [
                'name' => $demo['name'],
                'password' => $demo['password'],
                'is_demo' => true,
                'two_factor_secret' => null,
                'two_factor_confirmed_at' => null,
            ]
        );

        if ($user->email_verified_at === null) {
            $user->forceFill([
                'email_verified_at' => now(),
            ])->saveQuietly();
        }

        $user->roles()->sync([$role->id]);
    }
}
