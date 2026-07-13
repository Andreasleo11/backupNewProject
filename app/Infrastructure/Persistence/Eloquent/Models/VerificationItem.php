<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerificationItem extends Model
{
    protected $table = 'verification_items';

    protected $fillable = [
        'verification_report_id', 'part_name',
        'rec_quantity', 'verify_quantity', 'can_use', 'cant_use',
        'price', 'currency', 'do_number',
        // adjust form bridge columns
        'fg_measure', 'fg_warehouse_name', 'remark',
    ];

    protected $casts = [
        'rec_quantity' => 'decimal:4',
        'verify_quantity' => 'decimal:4',
        'can_use' => 'decimal:4',
        'cant_use' => 'decimal:4',
        'price' => 'decimal:2',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(VerificationReport::class, 'verification_report_id');
    }

    public function defects()
    {
        return $this->hasMany(VerificationItemDefect::class, 'verification_item_id');
    }

    /**
     * Adjust form raw material masters, keyed by detail_id (bridged column).
     */
    public function adjustdetail()
    {
        return $this->hasMany(\App\Models\FormAdjustMaster::class, 'detail_id', 'id');
    }
}
