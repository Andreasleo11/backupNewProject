<?php

namespace App\Models;

use App\Notifications\PurchaseOrderApproved;
use App\Notifications\PurchaseOrderCanceled;
use App\Notifications\PurchaseOrderCreated;
use App\Notifications\PurchaseOrderRejected;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'creator_id',
        'po_number',
        'vendor_name',
        'approved_date',
        // 'invoice_date',
        // 'invoice_number',
        'status',
        // 'currency',
        // 'purchase_order_category_id',
        'filename',
        'reason',
        'downloaded_at',
        'total',
        'tanggal_pembayaran',
        'parent_po_number',
        'revision_count',
        'remark',
        // rename total -> total_before_tax
        'total_before_tax',
        // new fields
        'vendor_code',
        'posting_date',
        'delivery_date',
        'sales_employee_name',
        'total_tax',
        'bill_to',
        'ship_to',
        'payment_terms',
        'contact_person_name',
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

    public function scopeCanceled($query)
    {
        return $query->where('status', 4);
    }

    public function scopeApprovedForCurrentMonth($query)
    {
        return $query->where('status', 2)
                    ->whereBetween('created_at', [
                        now()->startOfMonth(),
                        now()->endOfMonth()
                    ]);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function latestDownloadLog()
    {
        return $this->hasOne(PurchaseOrderDownloadLog::class)->latestOfMany();
    }

    public function category()
    {
        return $this->belongsTo(PurchaseOrderCategory::class, 'purchase_order_category_id');
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class, 'purchase_order_number', 'po_number');
    }

    protected static function boot()
    {
        parent::boot();

        // static::created(function ($report) {
        //     $report->sendNotification('created');
        // });

        static::updated(function ($report) {
            if ($report->isDirty('status')) {
                $statusMapping = [
                    2 => 'approved',
                    3 => 'rejected',
                    4 => 'canceled',
                ];

                // Send a notification based on the new status
                $report->sendNotification($statusMapping[$report->status]);
                if (isset($statusMapping[$report->status])) {
                }
            }
        });
    }

    public function getVendorNames()
    {
        $vendorNames = Vendor::pluck('name')->get();
        return response()->json([
            'vendorNames' => $vendorNames
        ]);
    }

    public function sendNotification($event)
    {
        $details = $this->prepareNotificationDetails();
        $users = $this->getNotificationUsers($event);

        if ($users->isNotEmpty()) {
            Notification::send($users, $this->getNotificationInstance($event, $details));
        } else {
            Log::warning("No valid users found to send the notification for Purchase Order {$event}.");
        }
    }

    private function prepareNotificationDetails()
    {
        $total = number_format($this->total, 2, '.', ',');
        return [
            'greeting' => 'Purchase Order Notification',
            'actionText' => 'Check Now',
            'actionURL' => route('po.view', $this->id),
            'body' => "Details of the Purchase Order: <br>
                - PO Number : {$this->po_number} <br>
                - Vendor Name : {$this->vendor_name} <br>
                - Invoice Date : {$this->invoice_date} <br>
                - Invoice Number : {$this->invoice_number} <br>
                - Total : {$this->currency} {$total} <br>
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
            4 => 'CANCELED',
            default => 'UNDEFINED',
        };
    }

    private function getNotificationUsers($event)
    {
        if ($event == 'created') {
            return User::whereHas('specification', fn($query) => $query->where('name', 'DIRECTOR'))->get();
        } elseif($event == 'approved') {
             $deptHeadAccounting = User::where('name', 'benny')->first();
             $accountingUser = User::where('name', 'nessa')->first();

             return collect([$deptHeadAccounting, $accountingUser, $this->user])->filter();
        } elseif($event == 'canceled') {
            $director = User::whereHas('specification', fn($query) => $query->where('name', 'DIRECTOR'))->first();

            return collect([$this->user, $director])->filter();
        } else {
            return collect([$this->user])->filter();
        }
    }

    private function getNotificationInstance($event, $details)
    {
        return match ($event) {
            'created' => new PurchaseOrderCreated($this, $details),
            'approved' => new PurchaseOrderApproved($this, $details),
            'rejected' => new PurchaseOrderRejected($this, $details),
            'canceled' => new PurchaseOrderCanceled($this, $details),
            default => null,
        };
    }
}
