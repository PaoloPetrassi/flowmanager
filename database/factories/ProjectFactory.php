<?php

namespace Database\Factories;

use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-4 months', '+2 months');

        return [
            'company_id' => Company::factory(),
            'contact_id' => null,
            'manager_id' => User::factory(),
            'code' => 'PRJ-'.fake()->unique()->numerify('#####'),
            'name' => fake()->catchPhrase(),
            'status' => fake()->randomElement(ProjectStatus::cases()),
            'priority' => fake()->randomElement(ProjectPriority::cases()),
            'is_template' => false,
            'progress_override' => null,
            'estimated_minutes' => null,
            'start_date' => $startDate,
            'due_date' => fake()->dateTimeBetween($startDate, '+10 months'),
            'budget' => fake()->optional(0.8)->randomFloat(2, 5000, 250000),
            'description' => fake()->paragraph(),
            'notes' => fake()->optional(0.3)->paragraph(),
            'created_by' => User::query()->first()?->id,
        ];
    }
}
