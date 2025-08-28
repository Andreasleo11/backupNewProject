<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderDownloadLog extends Model
{
    use HasFactory;

    const UPDATED_AT = null; // Disable 'updated_at' management
    protected $fillable = ["purchase_order_id", "user_id", "last_downloaded_at"];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }
}
