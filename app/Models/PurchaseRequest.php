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

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Get the current record's position in the table
            $position = static::count() + 1;

            // Get the date portion
            $date = now()->format('Ymd'); // Assuming you want the current date

            // Build the custom ID
            $customId = "PR/{$position}/{$date}";

            // Assign the custom ID to the model
            $model->doc_num = $customId;
        });
    }
}
