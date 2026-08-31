<?php

namespace App\Observers;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Services\RecurringTaskService;
use Illuminate\Validation\ValidationException;

class TaskWorkflowObserver
{
    public function updating(Task $task): void
    {
        if (! $task->isDirty('status')) {
            return;
        }

        $newStatus = $task->status instanceof TaskStatus
            ? $task->status
            : TaskStatus::tryFrom((string) $task->status);

        if ($newStatus !== TaskStatus::Completed) {
            return;
        }

        if ($task->hasBlockingDependencies()) {
            throw ValidationException::withMessages([
                'status' => __('This task cannot be completed until its dependencies are completed.'),
            ]);
        }
    }

    public function updated(Task $task): void
    {
        if (! $task->wasChanged('status') || $task->status !== TaskStatus::Completed) {
            return;
        }

        app(RecurringTaskService::class)->createNext($task->fresh('dependencies'));
    }
}
