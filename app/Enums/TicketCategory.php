<?php

namespace App\Enums;

enum TicketCategory: string
{
    case General = 'general';
    case Technical = 'technical';
    case Access = 'access';
    case Billing = 'billing';
    case Request = 'request';
    case Other = 'other';

    /**
     * Get the human-readable label.
     */
    public function label(): string
    {
        return __('enums.ticket_category.'.$this->value);
    }
}
