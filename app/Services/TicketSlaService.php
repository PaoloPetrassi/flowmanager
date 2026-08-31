<?php

namespace App\Services;

use App\Enums\TicketPriority;
use App\Models\Ticket;

class TicketSlaService
{
    public function apply(Ticket $ticket): void
    {
        $priority = $ticket->priority instanceof TicketPriority
            ? $ticket->priority->value
            : (string) $ticket->priority;

        $hours = (int) config("flowmanager.sla.hours.{$priority}", 24);

        $ticket->forceFill([
            'sla_due_at' => now()->addHours(max(1, $hours)),
            'sla_breached_at' => null,
            'sla_reminder_sent_at' => null,
        ])->saveQuietly();
    }
}
