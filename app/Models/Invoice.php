<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'purchase_order_id',
        'invoice_number',
        'invoice_date',
        'payment_date',
        'total',
        'total_currency',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'payment_date' => 'date',
        'total'        => 'decimal:2',
    ];

    // =========================================================================
    // Relationships
    // =========================================================================

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /**
     * File attachments via the existing `files` table doc_id convention.
     *
     * Convention: doc_id = 'INV-{invoice.id}'
     *
     * Usage:
     *   $invoice->files                         // collection of File models
     *   File::create(['doc_id' => $invoice->file_doc_id, ...])
     */
    public function files(): HasMany
    {
        return $this->hasMany(File::class, 'doc_id', 'file_doc_id');
    }

    // =========================================================================
    // Accessors
    // =========================================================================

    /**
     * Canonical doc_id string used to link File records to this invoice.
     * Stored as a virtual attribute — never persisted to the database.
     */
    public function getFileDocIdAttribute(): string
    {
        return 'INV-' . $this->id;
    }
}
