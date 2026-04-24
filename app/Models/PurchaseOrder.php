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
        'po_number',
        'approved_date',
        'status',
        'filename',
        'reason',
        'creator_id',
        'downloaded_at',
        'vendor_name',
        'invoice_date',
        'currency',
        'total',
        'tanggal_pembayaran',
        'invoice_number',
        'purchase_order_category_id',
        'parent_po_number',
        'revision_count',
    ];

    // Enum-based status methods
    public function getStatusEnum(): PurchaseOrderStatus
    {
        return PurchaseOrderStatus::fromLegacyValue($this->status);
    }

    public function setStatusEnum(PurchaseOrderStatus $status): void
    {
        $this->status = $status->legacyValue();
    }

    public function isStatus(PurchaseOrderStatus $status): bool
    {
        return $this->status === $status->legacyValue();
    }

    public function canTransitionTo(PurchaseOrderStatus $newStatus): bool
    {
        $currentStatus = $this->getStatusEnum();

        return match ($currentStatus) {
            PurchaseOrderStatus::DRAFT => in_array($newStatus, [PurchaseOrderStatus::WAITING]),
            PurchaseOrderStatus::WAITING => in_array($newStatus, [PurchaseOrderStatus::APPROVED, PurchaseOrderStatus::REJECTED, PurchaseOrderStatus::CANCELLED]),
            PurchaseOrderStatus::APPROVED, PurchaseOrderStatus::REJECTED, PurchaseOrderStatus::CANCELLED => false,
        };
    }

    // Legacy scope methods (keeping for backward compatibility)
    public function scopeApproved($query)
    {
        return $query->where('status', PurchaseOrderStatus::APPROVED->legacyValue());
    }

    public function scopeWaiting($query)
    {
        return $query->where('status', PurchaseOrderStatus::WAITING->legacyValue());
    }

    public function scopeRejected($query)
    {
        return $query->where('status', PurchaseOrderStatus::REJECTED->legacyValue());
    }

    public function scopeCanceled($query)
    {
        return $query->where('status', PurchaseOrderStatus::CANCELLED->legacyValue());
    }

    // New enum-based scopes
    public function scopeWithStatus($query, PurchaseOrderStatus $status)
    {
        return $query->where('status', $status->legacyValue());
    }

    public function scopeDraft($query)
    {
        return $query->where('status', PurchaseOrderStatus::DRAFT->legacyValue());
    }

    public function scopeTerminal($query)
    {
        return $query->whereIn('status', [
            PurchaseOrderStatus::APPROVED->legacyValue(),
            PurchaseOrderStatus::REJECTED->legacyValue(),
            PurchaseOrderStatus::CANCELLED->legacyValue(),
        ]);
    }

    public function scopeEditable($query)
    {
        return $query->whereIn('status', [
            PurchaseOrderStatus::DRAFT->legacyValue(),
            PurchaseOrderStatus::REJECTED->legacyValue(),
        ]);
    }

    public function scopeApprovedForCurrentMonth($query)
    {
        return $query
            ->where('status', 2)
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
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
        $vendorNames = Vendor::pluck('name');

        return response()->json([
            'vendorNames' => $vendorNames,
        ]);
    }

    public function sendNotification($event)
    {
        $details = $this->prepareNotificationDetails();
        $users = $this->getNotificationUsers($event);

        if ($users->isNotEmpty()) {
            Notification::send($users, $this->getNotificationInstance($event, $details));
        } else {
            Log::warning(
                "No valid users found to send the notification for Purchase Order {$event}.",
            );
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
                - Status : {$this->getStatusText($this->status)}",
        ];
    }

    private function getStatusText($status)
    {
        try {
            $statusEnum = PurchaseOrderStatus::fromLegacyValue($status);

            return $statusEnum->label();
        } catch (\InvalidArgumentException $e) {
            return 'UNDEFINED';
        }
    }

    private function getNotificationUsers($event)
    {
        if ($event == 'created') {
            return User::role('DIRECTOR')->get();
        } elseif ($event == 'approved') {
            $deptHeadAccounting = User::where('name', 'benny')->first();
            $accountingUser = User::where('name', 'nessa')->first();

            return collect([$deptHeadAccounting, $accountingUser, $this->user])->filter();
        } elseif ($event == 'canceled') {
            $director = User::role('DIRECTOR')->first();

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
