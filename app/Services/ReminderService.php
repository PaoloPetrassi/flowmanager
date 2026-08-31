<?php

namespace App\Services;

use App\Enums\TicketStatus;
use App\Models\Task;
use App\Models\Ticket;
use App\Notifications\FlowNotification;

class ReminderService
{
    public function run(): array
    {
        $taskCount = $this->taskReminders();
        $ticketCount = $this->ticketReminders();

        return ['tasks' => $taskCount, 'tickets' => $ticketCount];
    }

    private function taskReminders(): int
    {
        $count = 0;

        Task::query()
            ->operational()
            ->open()
            ->whereNull('due_reminder_sent_at')
            ->whereNotNull('assigned_to')
            ->whereBetween('due_date', [today(), today()->addDay()])
            ->with('assignee:id,name,email')
            ->chunkById(100, function ($tasks) use (&$count) {
                foreach ($tasks as $task) {
                    if (! $task->assignee) {
                        continue;
                    }

                    $task->assignee->notify(new FlowNotification(
                        kind: 'task_reminder',
                        titleKey: 'Task due soon',
                        messageKey: 'The task :item is due on :date.',
                        parameters: [
                            'item' => $task->title,
                            'date' => $task->due_date?->format('d/m/Y'),
                        ],
                        routeName: 'tasks.show',
                        routeParameters: [$task->id],
                        icon: 'bi-alarm',
                    ));

                    $task->forceFill(['due_reminder_sent_at' => now()])->saveQuietly();
                    $count++;
                }
            });

        return $count;
    }

    private function ticketReminders(): int
    {
        $count = 0;

        Ticket::query()
            ->whereNotIn('status', [TicketStatus::Resolved->value, TicketStatus::Closed->value])
            ->whereNotNull('assigned_to')
            ->whereNotNull('sla_due_at')
            ->where('sla_due_at', '<=', now()->addHour())
            ->with('assignee:id,name,email')
            ->chunkById(100, function ($tickets) use (&$count) {
                foreach ($tickets as $ticket) {
                    if ($ticket->sla_due_at?->isPast() && ! $ticket->sla_breached_at) {
                        $ticket->forceFill(['sla_breached_at' => now()])->saveQuietly();
                    }

                    if ($ticket->sla_reminder_sent_at || ! $ticket->assignee) {
                        continue;
                    }

                    $ticket->assignee->notify(new FlowNotification(
                        kind: 'sla_reminder',
                        titleKey: $ticket->isSlaBreached() ? 'Ticket SLA breached' : 'Ticket SLA due soon',
                        messageKey: $ticket->isSlaBreached()
                            ? 'The SLA for :item has been breached.'
                            : 'The SLA for :item expires at :date.',
                        parameters: [
                            'item' => $ticket->reference,
                            'date' => $ticket->sla_due_at?->format('d/m/Y H:i'),
                        ],
                        routeName: 'tickets.show',
                        routeParameters: [$ticket->id],
                        icon: 'bi-stopwatch',
                    ));

                    $ticket->forceFill(['sla_reminder_sent_at' => now()])->saveQuietly();
                    $count++;
                }
            });

        return $count;
    }
}
