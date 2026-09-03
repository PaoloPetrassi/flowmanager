<?php

namespace App\Enums;

enum AutomationTrigger: string
{
    case TaskOverdue = 'task_overdue';
    case TaskDueSoon = 'task_due_soon';
    case TaskUnassigned = 'task_unassigned';
    case TicketSlaBreached = 'ticket_sla_breached';
    case TicketUnassigned = 'ticket_unassigned';
    case ProjectDueSoon = 'project_due_soon';
    case ProjectOverdue = 'project_overdue';

    public function label(): string
    {
        return __('enums.automation_trigger.'.$this->value);
    }
}
