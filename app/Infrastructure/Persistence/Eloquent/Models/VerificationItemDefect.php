<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use App\Domain\Verification\Enums\DefectSource;
use App\Domain\Verification\Enums\Severity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerificationItemDefect extends Model
{
    protected $fillable = [
        'verification_item_id',
        'code',
        'name',
        'severity',
        'source',
        'quantity',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'severity' => Severity::class,
        'source' => DefectSource::class,
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(VerificationItem::class, 'verification_item_id');
    }
}
