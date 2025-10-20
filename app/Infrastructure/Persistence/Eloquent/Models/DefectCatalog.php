<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use App\Domain\Verification\Enums\DefectSource;
use App\Domain\Verification\Enums\Severity;
use Illuminate\Database\Eloquent\Model;

class DefectCatalog extends Model
{
    protected $fillable = [
        'code', 'name', 'default_severity', 'default_source', 'default_quantity', 'notes', 'active',
    ];

    protected $casts = [
        'default_quantity' => 'decimal:4',
        'default_severity' => Severity::class,     // PHP enum cast
        'default_source' => DefectSource::class, // PHP enum cast
        'active' => 'boolean',
    ];
}
