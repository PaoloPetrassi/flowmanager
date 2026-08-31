<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            AdminUserSeeder::class,
            CompanySeeder::class,
            ContactSeeder::class,
            ProjectSeeder::class,
            TaskSeeder::class,
            AssetSeeder::class,
            TicketSeeder::class,
            V09DemoSeeder::class,
        ]);
    }
}
