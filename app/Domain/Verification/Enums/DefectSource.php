<?php

namespace App\Domain\Verification\Enums;

enum DefectSource: string
{
    case CUSTOMER = 'CUSTOMER';
    case DAIJO = 'DAIJO';
    case SUPPLIER = 'SUPPLIER';
}
