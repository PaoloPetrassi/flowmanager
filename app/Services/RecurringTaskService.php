<?php

namespace App\Services;

use App\Enums\TaskRecurrence;
use App\Enums\TaskStatus;
use App\Models\Task;
use Illuminate\Support\Carbon;

class RecurringTaskService
{
    public function createNext(Task $task): ?Task
    {
        $recurrence = $task->recurrence instanceof TaskRecurrence
            ? $task->recurrence
            : TaskRecurrence::tryFrom((string) $task->recurrence);

        if (
            ! $recurrence
            || $recurrence === TaskRecurrence::None
            || ! $task->due_date
            || $task->next_recurrence_id
        ) {
            return null;
        }

        $nextDueDate = $this->nextDate(
            $task->due_date->copy(),
            $recurrence,
            max(1, (int) $task->recurrence_interval)
        );

        if ($task->recurrence_ends_at && $nextDueDate->gt($task->recurrence_ends_at)) {
            return null;
        }

        $next = Task::create([
            'project_id' => $task->project_id,
            'parent_id' => $task->parent_id,
            'milestone_id' => $task->milestone_id,
            'assigned_to' => $task->assigned_to,
            'title' => $task->title,
            'status' => TaskStatus::Todo,
            'priority' => $task->priority,
            'recurrence' => $recurrence,
            'recurrence_interval' => $task->recurrence_interval,
            'recurrence_ends_at' => $task->recurrence_ends_at,
            'recurrence_source_id' => $task->recurrence_source_id ?: $task->id,
            'due_date' => $nextDueDate,
            'estimated_minutes' => $task->estimated_minutes,
            'description' => $task->description,
            'created_by' => $task->created_by,
        ]);

        $task->forceFill(['next_recurrence_id' => $next->id])->saveQuietly();

        $next->dependencies()->sync($task->dependencies()->pluck('tasks.id'));

        return $next;
    }

    private function nextDate(Carbon $date, TaskRecurrence $recurrence, int $interval): Carbon
    {
        return match ($recurrence) {
            TaskRecurrence::Daily => $date->addDays($interval),
            TaskRecurrence::Weekly => $date->addWeeks($interval),
            TaskRecurrence::Monthly => $date->addMonthsNoOverflow($interval),
            TaskRecurrence::None => $date,
        };
    }
}
