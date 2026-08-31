<?php

namespace Database\Factories;

use App\Enums\TaskPriority;
use App\Enums\TaskRecurrence;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(TaskStatus::cases());

        return [
            'project_id' => Project::factory(),
            'assigned_to' => User::factory(),
            'title' => fake()->sentence(5),
            'status' => $status,
            'priority' => fake()->randomElement(TaskPriority::cases()),
            'recurrence' => TaskRecurrence::None,
            'recurrence_interval' => 1,
            'recurrence_ends_at' => null,
            'due_date' => fake()->optional(0.85)->dateTimeBetween('-2 months', '+5 months'),
            'completed_at' => $status === TaskStatus::Completed ? now() : null,
            'description' => fake()->optional(0.8)->paragraph(),
            'created_by' => User::query()->first()?->id,
        ];
    }
}
