<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{

    use HasFactory, LogsActivity;

    protected $fillable = [
        'doc_id',
        'name',
        'mime_type',
        'size'
    ];

    public function qaqcReport()
    {
        $this->belongsTo(Report::class);
    }

    public function purchaseRequest()
    {
        $this->belongsTo(PurchaseRequest::class, 'doc_id', 'doc_num');
    }
}
