<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id_create',
        'date_pr',
        'date_required',
        'remark',
        'to_department',
        'autograph_1',
        'autograph_2',
        'autograph_3',
        'autograph_4',
        'autograph_user_1',
        'autograph_user_2',
        'autograph_user_3',
        'autograph_user_4',
        'attachment_pr',
        'status',
        'pr_no',
        'supplier',
        // Add other fields as needed
    ];


    public function itemDetail()
    {
        return $this->hasMany(DetailPurchaseRequest::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'user_id_create');
    }
}
