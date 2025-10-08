<?php

namespace App\Enums;

enum VehicleStatus: string
{
    case ACTIVE = 'active';
    case MAINTENANCE = 'maintenance';
    case RETIRED = 'retired';
    case SOLD = 'sold';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::MAINTENANCE => 'Maintenance',
            self::RETIRED => 'Retired',
            self::SOLD => 'Sold',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::ACTIVE => 'check2-circle',
            self::MAINTENANCE => 'tools',
            self::RETIRED => 'archive',
            self::SOLD => 'cart-check',
        };
    }

    public function variant(): string  // Bootstrap color
    {
        return match ($this) {
            self::ACTIVE => 'success',
            self::MAINTENANCE => 'warning',
            self::RETIRED => 'secondary',
            self::SOLD => 'dark',
        };
    }
}
