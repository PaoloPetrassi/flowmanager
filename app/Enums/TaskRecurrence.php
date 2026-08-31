<?php

namespace App\Enums;

enum TaskRecurrence: string
{
    case None = 'none';
    case Daily = 'daily';
    case Weekly = 'weekly';
    case Monthly = 'monthly';

    public function label(): string
    {
        return __('enums.task_recurrence.'.$this->value);
    }
}
