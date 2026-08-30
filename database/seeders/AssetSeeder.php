<?php

namespace Database\Seeders;

use App\Enums\AssetStatus;
use App\Models\Asset;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;

class AssetSeeder extends Seeder
{
    public function run(): void
    {
        if (Asset::query()->exists()) {
            return;
        }

        $companies = Company::query()->get();
        $users = User::query()->get();

        if ($companies->isEmpty() || $users->isEmpty()) {
            return;
        }

        Asset::factory()
            ->count(40)
            ->recycle($companies)
            ->create()
            ->each(function (Asset $asset) use ($users) {
                if (fake()->boolean(45)) {
                    $asset->update([
                        'assigned_to' => $users->random()->id,
                        'status' => AssetStatus::Assigned,
                    ]);
                }
            });
    }
}
