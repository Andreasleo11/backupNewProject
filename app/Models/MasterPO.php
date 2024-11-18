<?php

namespace App\Models;

use App\Notifications\MasterPOApproved;
use App\Notifications\MasterPOCreated;
use App\Notifications\MasterPORejected;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class MasterPO extends Model
{
    use HasFactory;

    protected $table = 'master_po';

    protected $fillable = [
        'po_number',
        'approved_date',
        'status',
        'filename',
        'reason',
        'creator_id',
        'downloaded_at',
        'vendor_name',
        'po_date',
        'currency',
        'total',
        'tanggal_pembayaran',
    ];

    // Queries
    public function scopeApproved($query)
    {
        return $query->where('status', 2);
    }

    public function scopeWaiting($query)
    {
        return $query->where('status', 1);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 3);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($report) {
            $report->sendNotification('created');
        });

        static::updated(function ($report) {
            if ($report->isDirty('status')) {
                $statusMapping = [
                    2 => 'approved',
                    3 => 'rejected'
                ];

                if (isset($statusMapping[$report->status])) {
                    $report->sendNotification($statusMapping[$report->status]);
                }
            }
        });
    }

    public function sendNotification($event)
    {
        $details = $this->prepareNotificationDetails();
        $users = $this->getNotificationUsers($event);

        if ($users->isNotEmpty()) {
            Notification::send($users, $this->getNotificationInstance($event, $details));
        } else {
            Log::warning("No valid users found to send the notification for Master PO {$event}.");
        }
    }

    private function prepareNotificationDetails()
    {
        return [
            'greeting' => 'Purchase Order Notification',
            'actionText' => 'Check Now',
            'actionURL' => route('po.view', $this->id),
            'body' => "Notification for Purchase Order Report: <br>
                - PO Number : {$this->po_number} <br>
                - Vendor Name : {$this->vendor_name} <br>
                - PO Date : {$this->po_date} <br>
                - Total : {$this->currency} {$this->total} <br>
                - Tanggal Pembayaran : {$this->tanggal_pembayaran} <br>
                - Status : {$this->getStatusText($this->status)}"
        ];
    }

    private function getStatusText($status)
    {
        return match ($status) {
            1 => 'WAITING',
            2 => 'APPROVED',
            3 => 'REJECTED',
            default => 'UNDEFINED',
        };
    }

    private function getNotificationUsers($event)
    {
        if ($event == 'created') {
            // Notify director on creation
            return User::whereHas('department', fn($query) => $query->where('name', 'DIRECTOR'))->get();
        } else {
            // Notify creator on approval or rejection
            return collect([$this->user])->filter();
        }
    }

    private function getNotificationInstance($event, $details)
    {
        return match ($event) {
            'created' => new MasterPOCreated($this, $details),
            'approved' => new MasterPOApproved($this, $details),
            'rejected' => new MasterPORejected($this, $details),
            default => null,
        };
    }
}
