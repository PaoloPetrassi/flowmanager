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
        return match ($this) {
            self::Active => 'Active',
            self::Inactive => 'Inactive',
            self::Prospect => 'Prospect',
            self::Suspended => 'Suspended',
        };
    }
}