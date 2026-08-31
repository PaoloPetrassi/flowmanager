<?php

namespace App\Enums;

enum AutomationTrigger: string
{
    case TaskOverdue = 'task_overdue';
    case TaskDueSoon = 'task_due_soon';
    case TicketSlaBreached = 'ticket_sla_breached';
    case ProjectDueSoon = 'project_due_soon';

    public function label(): string
    {
        return __('enums.automation_trigger.'.$this->value);
    }
}
