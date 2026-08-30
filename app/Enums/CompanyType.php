<?php

namespace App\Enums;

enum CompanyType: string
{
    case Customer = 'customer';
    case Supplier = 'supplier';
    case Partner = 'partner';
    case Prospect = 'prospect';
    case Other = 'other';

    /**
     * Get the human-readable label.
     */
    public function label(): string
    {
        return __('enums.company_type.'.$this->value);
    }
}
