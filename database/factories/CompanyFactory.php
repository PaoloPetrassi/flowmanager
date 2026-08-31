<?php

namespace Database\Factories;

use App\Enums\CompanyStatus;
use App\Enums\CompanyType;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $companyName = fake()->company();

        return [
            'name' => $companyName,
            'legal_name' => $companyName,

            'type' => fake()->randomElement(
                CompanyType::cases()
            ),

            'status' => fake()->randomElement(
                CompanyStatus::cases()
            ),

            'vat_number' => 'IT'.fake()->unique()->numerify('###########'),

            'tax_code' => fake()->unique()->numerify('###########'),

            'email' => fake()->unique()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'website' => fake()->url(),

            'industry' => fake()->randomElement([
                'Technology',
                'Finance',
                'Manufacturing',
                'Healthcare',
                'Retail',
                'Consulting',
                'Logistics',
                'Energy',
                'Construction',
                'Agriculture',
            ]),

            'employees' => fake()->numberBetween(5, 1500),

            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'province' => fake()->state(),
            'postal_code' => fake()->postcode(),
            'country_code' => 'IT',

            'notes' => fake()->optional(0.35)->paragraph(),

            'created_by' => User::query()->first()?->id,
        ];
    }
}
