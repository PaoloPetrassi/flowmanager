<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Company::query()->exists()) {
            return;
        }

        Company::factory()
            ->count(25)
            ->create();
    }
}