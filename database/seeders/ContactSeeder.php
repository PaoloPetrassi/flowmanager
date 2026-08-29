<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Contact;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Contact::query()->exists()) {
            return;
        }

        $companies = Company::query()->get();

        if ($companies->isEmpty()) {
            return;
        }

        Contact::factory()
            ->count(40)
            ->recycle($companies)
            ->create();

        $companies->each(function (Company $company) {
            $contact = $company->contacts()
                ->oldest('id')
                ->first();

            if ($contact !== null) {
                $contact->update([
                    'is_primary' => true,
                ]);
            }
        });
    }
}
