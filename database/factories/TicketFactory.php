<?php

namespace Database\Factories;

use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Company;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(TicketStatus::cases());
        $isResolved = in_array($status, [TicketStatus::Resolved, TicketStatus::Closed], true);

        return [
            'company_id' => fake()->boolean(85) ? Company::factory() : null,
            'contact_id' => null,
            'assigned_to' => fake()->boolean(80) ? User::factory() : null,
            'reference' => 'TKT-'.now()->format('Y').'-'.fake()->unique()->numerify('#####'),
            'subject' => fake()->sentence(6),
            'category' => fake()->randomElement(TicketCategory::cases()),
            'status' => $status,
            'priority' => fake()->randomElement(TicketPriority::cases()),
            'description' => fake()->paragraphs(2, true),
            'resolution' => $isResolved ? fake()->paragraph() : null,
            'resolved_at' => $isResolved ? now()->subDays(fake()->numberBetween(0, 30)) : null,
            'created_by' => User::query()->first()?->id,
        ];
    }
}
