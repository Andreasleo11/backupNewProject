<?php

namespace App\Enums;

enum ToDepartment: string
{
    case MAINTENANCE = 'Maintenance';
    case COMPUTER = 'Computer';
    case PERSONALIA = 'Personnel';
    case PURCHASING = 'Purchasing';

    public function label(): string
    {
        return match ($this) {
            self::MAINTENANCE => 'Maintenance',
            self::COMPUTER => 'Computer',
            self::PERSONALIA => 'Personalia',
            self::PURCHASING => 'Purchasing',
        };
    }

    public static function values(): array
    {
        return array_map(fn ($c) => $c->value, self::cases());
    }
}
