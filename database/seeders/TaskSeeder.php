<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        if (Task::query()->exists()) {
            return;
        }

        $projects = Project::query()->get();
        $users = User::query()->get();

        if ($projects->isEmpty() || $users->isEmpty()) {
            return;
        }

        Task::factory()
            ->count(75)
            ->recycle($projects)
            ->recycle($users)
            ->create();
    }
}
