<?php

namespace App\Enums;

enum AssetStatus: string
{
    case Available = 'available';
    case Assigned = 'assigned';
    case Maintenance = 'maintenance';
    case Retired = 'retired';
    case Lost = 'lost';

    /**
     * Get the human-readable label.
     */
    public function label(): string
    {
        return __('enums.asset_status.'.$this->value);
    }
}
