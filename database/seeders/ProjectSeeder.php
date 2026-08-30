<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        if (Project::query()->exists()) {
            return;
        }

        $companies = Company::query()->with('contacts')->get();
        $users = User::query()->get();

        if ($companies->isEmpty() || $users->isEmpty()) {
            return;
        }

        Project::factory()
            ->count(25)
            ->recycle($companies)
            ->recycle($users)
            ->create()
            ->each(function (Project $project) {
                $contacts = $project->company?->contacts;
                $contact = $contacts?->isNotEmpty() ? $contacts->random() : null;

                if ($contact) {
                    $project->update(['contact_id' => $contact->id]);
                }
            });
    }
}
