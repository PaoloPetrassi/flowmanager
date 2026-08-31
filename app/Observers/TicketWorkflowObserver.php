<?php

namespace App\Observers;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Services\TicketSlaService;
use App\Services\WebhookService;

class TicketWorkflowObserver
{
    public function created(Ticket $ticket): void
    {
        if (! $ticket->sla_due_at) {
            app(TicketSlaService::class)->apply($ticket);
        }
    }

    public function updated(Ticket $ticket): void
    {
        if ($ticket->wasChanged('priority')) {
            app(TicketSlaService::class)->apply($ticket);
        }

        if (
            $ticket->wasChanged('status')
            && ! $ticket->first_response_at
            && $ticket->status !== TicketStatus::Open
        ) {
            $ticket->forceFill(['first_response_at' => now()])->saveQuietly();
        }

        if (
            in_array($ticket->status, [TicketStatus::Resolved, TicketStatus::Closed], true)
            && ! $ticket->resolved_at
        ) {
            $ticket->forceFill(['resolved_at' => now()])->saveQuietly();
        }

        if ($ticket->wasChanged('status') && in_array($ticket->status, [TicketStatus::Resolved, TicketStatus::Closed], true)) {
            WebhookService::dispatch('ticket.resolved', $ticket, ['status' => $ticket->status->value]);
        }
    }
}
