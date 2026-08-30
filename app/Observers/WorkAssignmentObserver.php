<?php

namespace App\Observers;

use App\Models\Asset;
use App\Models\Project;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\FlowNotification;
use App\Support\FlowResourceRegistry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class WorkAssignmentObserver
{
    public function created(Model $model): void
    {
        $this->notifyAssignment($model, true);
    }

    public function updated(Model $model): void
    {
        $field = $this->assignmentField($model);

        if ($field && $model->wasChanged($field)) {
            $this->notifyAssignment($model, false);
        }
    }

    private function notifyAssignment(Model $model, bool $created): void
    {
        $field = $this->assignmentField($model);

        if (! $field) {
            return;
        }

        $userId = $model->getAttribute($field);

        if (! $userId || $userId === Auth::id()) {
            return;
        }

        $recipient = User::query()->find($userId);

        if (! $recipient) {
            return;
        }

        [$title, $message, $icon] = match (true) {
            $model instanceof Project => [
                'Project assigned to you',
                'You are now responsible for the project :item.',
                'bi-kanban',
            ],
            $model instanceof Task => [
                'Task assigned to you',
                'The task :item has been assigned to you.',
                'bi-check2-square',
            ],
            $model instanceof Asset => [
                'Asset assigned to you',
                'The asset :item has been assigned to you.',
                'bi-laptop',
            ],
            $model instanceof Ticket => [
                'Ticket assigned to you',
                'The ticket :item has been assigned to you.',
                'bi-ticket-perforated',
            ],
            default => [
                'New assignment',
                ':item has been assigned to you.',
                'bi-bell',
            ],
        };

        $route = FlowResourceRegistry::routeFor($model);

        if (! $route) {
            return;
        }

        $recipient->notify(new FlowNotification(
            kind: $created ? 'assignment' : 'reassignment',
            titleKey: $title,
            messageKey: $message,
            parameters: [
                'item' => FlowResourceRegistry::labelForModel($model),
            ],
            routeName: $route,
            routeParameters: [$model->getKey()],
            icon: $icon,
        ));
    }

    private function assignmentField(Model $model): ?string
    {
        return match (true) {
            $model instanceof Project => 'manager_id',
            $model instanceof Task,
            $model instanceof Asset,
            $model instanceof Ticket => 'assigned_to',
            default => null,
        };
    }
}
