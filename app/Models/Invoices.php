<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Invoices extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';


    protected $fillable = [
        'id',
        'doc_id',
        'invoice_date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (!$invoice->id) { // Only generate if not manually assigned
                $invoice->id = self::generateInvoiceId();
            }
        });
    }

    private static function generateInvoiceId()
    {
        $date = now()->format('Ymd');
        return 'INV-' . $date . '-' . Str::random(6);
    }
}
