<?php

namespace App\Enums;

enum TaskPriority: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    case Urgent = 'urgent';

    /**
     * Get the human-readable label.
     */
    public function label(): string
    {
        return __('enums.task_priority.'.$this->value);
    }
}
