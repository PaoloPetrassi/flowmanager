<?php

namespace App\Enums;

enum CompanyStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Prospect = 'prospect';
    case Suspended = 'suspended';

    /**
     * Get the human-readable label.
     */
    public function label(): string
    {
        return __('enums.company_status.'.$this->value);
    }
}
