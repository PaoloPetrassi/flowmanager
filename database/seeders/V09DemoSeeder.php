<?php

namespace Database\Seeders;

use App\Enums\AutomationAction;
use App\Enums\AutomationTrigger;
use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use App\Enums\TaskPriority;
use App\Enums\TaskRecurrence;
use App\Enums\TaskStatus;
use App\Models\AutomationRule;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\Task;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class V09DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        $users = User::query()->orderBy('id')->get();
        $projects = Project::query()
            ->operational()
            ->with(['tasks' => fn ($query) => $query->orderBy('id')])
            ->orderBy('id')
            ->limit(8)
            ->get();

        if ($users->isEmpty() || $projects->isEmpty()) {
            return;
        }

        Model::withoutEvents(function () use ($users, $projects): void {
            foreach ($projects as $index => $project) {
                $manager = $project->manager ?: $users[$index % $users->count()];

                if (! $project->manager_id) {
                    $project->forceFill(['manager_id' => $manager->id])->save();
                }

                $project->teamMembers()->syncWithoutDetaching([
                    $manager->id => ['role' => 'Project manager'],
                    $users[($index + 1) % $users->count()]->id => ['role' => 'Contributor'],
                ]);

                $milestone = Milestone::query()->firstOrCreate(
                    [
                        'project_id' => $project->id,
                        'name' => 'Delivery milestone',
                    ],
                    [
                        'description' => 'Example milestone for planning and workload views.',
                        'due_date' => $project->due_date ?: today()->addDays(30 + $index),
                        'created_by' => $manager->id,
                    ]
                );

                foreach ($project->tasks->take(5) as $taskIndex => $task) {
                    $changes = [];

                    if (! $task->estimated_minutes) {
                        $changes['estimated_minutes'] = 60 * ($taskIndex + 1);
                    }

                    if ($taskIndex < 3 && ! $task->milestone_id) {
                        $changes['milestone_id'] = $milestone->id;
                    }

                    if ($changes !== []) {
                        $task->forceFill($changes)->save();
                    }
                }
            }

            $project = $projects->first();
            $tasks = $project->tasks->values();

            if ($tasks->count() >= 3) {
                $parent = $tasks[0];
                $child = $tasks[1];
                $dependent = $tasks[2];

                if (! $child->parent_id) {
                    $child->forceFill(['parent_id' => $parent->id])->save();
                }

                $dependent->dependencies()->syncWithoutDetaching([$parent->id]);

                if ($parent->recurrence === TaskRecurrence::None && $parent->due_date) {
                    $parent->forceFill([
                        'recurrence' => TaskRecurrence::Weekly,
                        'recurrence_interval' => 1,
                        'recurrence_ends_at' => $parent->due_date->copy()->addMonths(2),
                    ])->save();
                }

                TimeEntry::query()->firstOrCreate(
                    [
                        'task_id' => $parent->id,
                        'user_id' => $parent->assigned_to ?: $users->first()->id,
                        'note' => 'Demo time entry',
                    ],
                    [
                        'started_at' => now()->subDays(2)->startOfHour(),
                        'ended_at' => now()->subDays(2)->startOfHour()->addMinutes(75),
                        'minutes' => 75,
                    ]
                );
            }

            $this->seedTemplate($projects->first(), $users->first());
            $this->seedAutomations($users->first());
        });
    }

    private function seedTemplate(Project $source, User $creator): void
    {
        if (Project::query()->where('code', 'TPL-DEMO-001')->exists()) {
            return;
        }

        $template = Project::query()->create([
            'company_id' => $source->company_id,
            'contact_id' => null,
            'manager_id' => null,
            'code' => 'TPL-DEMO-001',
            'name' => 'Standard delivery template',
            'status' => ProjectStatus::Planned,
            'priority' => ProjectPriority::Medium,
            'is_template' => true,
            'progress_override' => null,
            'start_date' => null,
            'due_date' => null,
            'budget' => null,
            'estimated_minutes' => 720,
            'description' => 'Reusable example project structure.',
            'created_by' => $creator->id,
        ]);

        $first = Task::query()->create([
            'project_id' => $template->id,
            'title' => 'Kick-off and requirements',
            'status' => TaskStatus::Todo,
            'priority' => TaskPriority::High,
            'recurrence' => TaskRecurrence::None,
            'recurrence_interval' => 1,
            'estimated_minutes' => 180,
            'created_by' => $creator->id,
        ]);

        Task::query()->create([
            'project_id' => $template->id,
            'parent_id' => $first->id,
            'title' => 'Delivery checklist',
            'status' => TaskStatus::Todo,
            'priority' => TaskPriority::Medium,
            'recurrence' => TaskRecurrence::None,
            'recurrence_interval' => 1,
            'estimated_minutes' => 120,
            'created_by' => $creator->id,
        ]);
    }

    private function seedAutomations(User $creator): void
    {
        AutomationRule::query()->updateOrCreate(
            ['name' => 'Notify assignee when a task is overdue'],
            [
                'trigger' => AutomationTrigger::TaskOverdue,
                'action' => AutomationAction::NotifyAssignee,
                'conditions' => [],
                'action_config' => [],
                'is_active' => true,
                'created_by' => $creator->id,
            ]
        );

        AutomationRule::query()->updateOrCreate(
            ['name' => 'Notify manager about projects due soon'],
            [
                'trigger' => AutomationTrigger::ProjectDueSoon,
                'action' => AutomationAction::NotifyManager,
                'conditions' => [],
                'action_config' => [],
                'is_active' => true,
                'created_by' => $creator->id,
            ]
        );
    }
}
