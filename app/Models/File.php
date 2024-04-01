<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    protected $fillable = [
        'doc_id',
        'name',
        'mime_type',
        'data',
        'size'
    ];
    use HasFactory;

    public function qaqcReport()
    {
        $this->belongsTo(Report::class);
    }

    public function purchaseRequest(){
        $this->belongsTo(PurchaseRequest::class, 'doc_id', 'doc_num');
    }
}
