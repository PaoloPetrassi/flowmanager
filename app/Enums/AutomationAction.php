<?php

namespace App\Enums;

enum AutomationAction: string
{
    case NotifyAssignee = 'notify_assignee';
    case NotifyManager = 'notify_manager';
    case NotifyUser = 'notify_user';
    case SetTicketPriority = 'set_ticket_priority';

    public function label(): string
    {
        return __('enums.automation_action.'.$this->value);
    }
}
