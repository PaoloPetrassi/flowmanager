<?php

namespace Database\Factories;

use App\Enums\AssetStatus;
use App\Models\Asset;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Asset>
 */
class AssetFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $purchaseDate = fake()->dateTimeBetween('-4 years', 'now');

        return [
            'company_id' => fake()->boolean(75) ? Company::factory() : null,
            'assigned_to' => null,
            'asset_tag' => 'AST-'.fake()->unique()->numerify('#####'),
            'name' => fake()->randomElement([
                'Notebook',
                'Desktop workstation',
                'Monitor',
                'Smartphone',
                'Tablet',
                'Network appliance',
                'Printer',
            ]),
            'category' => fake()->randomElement([
                'Computer',
                'Display',
                'Mobile',
                'Network',
                'Office',
            ]),
            'brand' => fake()->company(),
            'model' => strtoupper(fake()->bothify('??-####')),
            'serial_number' => strtoupper(fake()->unique()->bothify('SN-########')),
            'status' => AssetStatus::Available,
            'purchase_date' => $purchaseDate,
            'purchase_cost' => fake()->randomFloat(2, 150, 4500),
            'warranty_expires_at' => fake()->dateTimeBetween($purchaseDate, '+5 years'),
            'notes' => fake()->optional(0.25)->sentence(),
            'created_by' => User::query()->first()?->id,
        ];
    }
}
