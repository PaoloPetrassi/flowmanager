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
        return match ($this) {
            self::Available => 'Available',
            self::Assigned => 'Assigned',
            self::Maintenance => 'Maintenance',
            self::Retired => 'Retired',
            self::Lost => 'Lost',
        };
    }
}
