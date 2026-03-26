<?php

namespace App\Enums;

enum Branch: string
{
    case JAKARTA = 'JAKARTA';
    case KARAWANG = 'KARAWANG';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
