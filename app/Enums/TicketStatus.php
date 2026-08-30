<?php

namespace App\Enums;

enum TicketStatus: string
{
    case Open = 'open';
    case InProgress = 'in_progress';
    case Waiting = 'waiting';
    case Resolved = 'resolved';
    case Closed = 'closed';

    /**
     * Get the human-readable label.
     */
    public function label(): string
    {
        return __('enums.ticket_status.'.$this->value);
    }
}
