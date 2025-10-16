<?php

declare(strict_types=1);

namespace App\Domain\Signature\ValueObjects;

enum SignatureKind: string
{
    case DRAWN = 'drawn';
    case UPLOADED = 'uploaded';
    case TEXT = 'text';
    case SVG = 'svg';
}
