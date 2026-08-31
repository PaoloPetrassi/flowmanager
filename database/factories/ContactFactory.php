<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contact>
 */
class ContactFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'job_title' => fake()->jobTitle(),
            'department' => fake()->randomElement([
                'Administration',
                'Finance',
                'Human Resources',
                'IT',
                'Operations',
                'Procurement',
                'Sales',
            ]),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'mobile' => fake()->optional(0.7)->phoneNumber(),
            'is_primary' => false,
            'notes' => fake()->optional(0.3)->paragraph(),
            'created_by' => User::query()->first()?->id,
        ];
    }
}
