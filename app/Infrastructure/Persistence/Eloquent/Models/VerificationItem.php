<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerificationItem extends Model
{
    protected $table = 'verification_items';

    protected $fillable = ['verification_report_id', 'name', 'notes', 'amount'];

    public function report(): BelongsTo
    {
        return $this->belongsTo(VerificationReport::class, 'verification_report_id');
    }
}
