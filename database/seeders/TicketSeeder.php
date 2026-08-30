<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    public function run(): void
    {
        if (Ticket::query()->exists()) {
            return;
        }

        $companies = Company::query()->with('contacts')->get();
        $users = User::query()->get();

        if ($companies->isEmpty() || $users->isEmpty()) {
            return;
        }

        Ticket::factory()
            ->count(50)
            ->recycle($companies)
            ->recycle($users)
            ->create()
            ->each(function (Ticket $ticket) {
                $contacts = $ticket->company?->contacts;
                $contact = $contacts?->isNotEmpty() ? $contacts->random() : null;

                if ($contact) {
                    $ticket->update(['contact_id' => $contact->id]);
                }
            });
    }
}
