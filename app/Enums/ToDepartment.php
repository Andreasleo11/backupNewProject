<?php

namespace App\Enums;

enum ToDepartment: string
{
    case MAINTENANCE = 'MAINTENANCE';
    case COMPUTER    = 'COMPUTER';
    case PERSONALIA  = 'PERSONALIA';
    case PURCHASING  = 'PURCHASING';

    public function label(): string
    {
        return match($this) {
            self::MAINTENANCE => 'Maintenance',
            self::COMPUTER    => 'Computer',
            self::PERSONALIA  => 'Personalia',
            self::PURCHASING  => 'Purchasing',
        };
    }

    public static function values(): array
    {
        return array_map(fn($c) => $c->value, self::cases());
    }
}
