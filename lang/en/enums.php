<?php

return [
    'asset_status' => [
        'available' => 'Available',
        'assigned' => 'Assigned',
        'maintenance' => 'Maintenance',
        'retired' => 'Retired',
        'lost' => 'Lost',
    ],
    'company_status' => [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'prospect' => 'Prospect',
        'suspended' => 'Suspended',
    ],
    'company_type' => [
        'customer' => 'Customer',
        'supplier' => 'Supplier',
        'partner' => 'Partner',
        'prospect' => 'Prospect',
        'other' => 'Other',
    ],
    'project_priority' => [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
        'urgent' => 'Urgent',
    ],
    'project_status' => [
        'planned' => 'Planned',
        'active' => 'Active',
        'on_hold' => 'On hold',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ],
    'task_priority' => [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
        'urgent' => 'Urgent',
    ],
    'task_status' => [
        'todo' => 'To do',
        'in_progress' => 'In progress',
        'blocked' => 'Blocked',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ],
    'task_recurrence' => [
        'none' => 'Does not repeat',
        'daily' => 'Daily',
        'weekly' => 'Weekly',
        'monthly' => 'Monthly',
    ],
    'automation_trigger' => [
        'task_overdue' => 'Task overdue',
        'task_due_soon' => 'Task due soon',
        'ticket_sla_breached' => 'Ticket SLA breached',
        'project_due_soon' => 'Project due soon',
    ],
    'automation_action' => [
        'notify_assignee' => 'Notify assignee',
        'notify_manager' => 'Notify project manager',
        'notify_user' => 'Notify selected user',
        'set_ticket_priority' => 'Set ticket priority',
    ],
    'ticket_category' => [
        'general' => 'General',
        'technical' => 'Technical',
        'access' => 'Access',
        'billing' => 'Billing',
        'request' => 'Request',
        'other' => 'Other',
    ],
    'ticket_priority' => [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
        'urgent' => 'Urgent',
    ],
    'ticket_status' => [
        'open' => 'Open',
        'in_progress' => 'In progress',
        'waiting' => 'Waiting',
        'resolved' => 'Resolved',
        'closed' => 'Closed',
    ],
];
