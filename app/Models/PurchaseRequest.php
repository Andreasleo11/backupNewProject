<?php

namespace App\Models;

use App\Console\Commands\SendPREmailNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Bus;

class PurchaseRequest extends Model
{
    use HasFactory, SoftDeletes;

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
        'autograph_5',
        'autograph_6',
        'autograph_7',
        'autograph_user_1',
        'autograph_user_2',
        'autograph_user_3',
        'autograph_user_4',
        'autograph_user_5',
        'autograph_user_6',
        'autograph_user_7',
        'attachment_pr',
        'status',
        'pr_no',
        'supplier',
        'description',
        'approved_at',
        'updated_at',
        'pic',
        'type',
        'from_department',
        'is_import',
        'is_cancel',
        'po_number'
    ];


    public function itemDetail()
    {
        return $this->hasMany(DetailPurchaseRequest::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'user_id_create');
    }

    public function files()
    {
        return $this->hasMany(File::class, 'doc_id', 'doc_num');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 4);
    }

    public function scopeWaiting($query)
    {
        return $query->where('status', 3);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 5);
    }

    protected static function booted()
    {
        static::created(function ($purchaseRequest) {
            // Get the date portion
            $date = now()->format('Ymd'); // Assuming you want the current date

            // Build the docNum
            $docNum = "PR/{$purchaseRequest->id}/{$date}";

            $prNo = substr($purchaseRequest->to_department, 0, 4) . '-' . $purchaseRequest->id;

            $purchaseRequest->update(['pr_no' => $prNo, 'doc_num' => $docNum]);

            // Dispatch the job to send the email notification
            Bus::dispatch(new SendPREmailNotification($purchaseRequest));
        });

        static::updating(function ($purchaseRequest) {
            if ($purchaseRequest->isDirty('status')) {
                $originalStatus = $purchaseRequest->getOriginal('status');
                $newStatus = $purchaseRequest->status;

                // Check if the status has changed
                if ($originalStatus !== $newStatus) {
                    // Dispatch the job to send the email notification
                    Bus::dispatch(new SendPREmailNotification($purchaseRequest));
                }
            }
        });
    }
}
