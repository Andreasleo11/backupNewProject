<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\User;
use App\Notifications\PurchaseOrderApproved;
use App\Notifications\PurchaseOrderCanceled;
use App\Notifications\PurchaseOrderCreated;
use App\Notifications\PurchaseOrderRejected;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class NotificationService
{
    /**
     * Send notification for purchase order creation
     */
    public function sendPurchaseOrderCreated(PurchaseOrder $po): void
    {
        $this->sendNotification($po, 'created');
    }

    /**
     * Send notification for purchase order approval
     */
    public function sendPurchaseOrderApproved(PurchaseOrder $po): void
    {
        $this->sendNotification($po, 'approved');
    }

    /**
     * Send notification for purchase order rejection
     */
    public function sendPurchaseOrderRejected(PurchaseOrder $po): void
    {
        $this->sendNotification($po, 'rejected');
    }

    /**
     * Send notification for purchase order cancellation
     */
    public function sendPurchaseOrderCanceled(PurchaseOrder $po): void
    {
        $this->sendNotification($po, 'canceled');
    }

    /**
     * Send notification for a specific event
     */
    private function sendNotification(PurchaseOrder $po, string $event): void
    {
        try {
            $details = $this->prepareNotificationDetails($po);
            $users = $this->getNotificationUsers($po, $event);

            if ($users->isEmpty()) {
                Log::warning("No valid users found to send {$event} notification", [
                    'po_id' => $po->id,
                    'po_number' => $po->po_number,
                ]);

                return;
            }

            $notification = $this->getNotificationInstance($event, $po, $details);

            if ($notification) {
                Notification::send($users, $notification);

                Log::info('Notification sent successfully', [
                    'event' => $event,
                    'po_id' => $po->id,
                    'po_number' => $po->po_number,
                    'recipients_count' => $users->count(),
                    'recipients' => $users->pluck('id')->toArray(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Failed to send {$event} notification", [
                'po_id' => $po->id,
                'po_number' => $po->po_number,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Prepare notification details with formatted data
     */
    private function prepareNotificationDetails(PurchaseOrder $po): array
    {
        $total = number_format($po->total, 2, '.', ',');

        return [
            'greeting' => 'Purchase Order Notification',
            'actionText' => 'Check Now',
            'actionURL' => route('po.view', $po->id),
            'body' => "Details of the Purchase Order: <br>
                - PO Number : {$po->po_number} <br>
                - Vendor Name : {$po->vendor_name} <br>
                - Invoice Date : {$po->invoice_date} <br>
                - Invoice Number : {$po->invoice_number} <br>
                - Total : {$po->currency} {$total} <br>
                - Tanggal Pembayaran : {$po->tanggal_pembayaran} <br>
                - Status : {$this->getStatusText($po->status)}",
        ];
    }

    /**
     * Get users to notify for a specific event
     */
    private function getNotificationUsers(PurchaseOrder $po, string $event): \Illuminate\Support\Collection
    {
        return match ($event) {
            'created' => $this->getDirectors(),
            'approved' => $this->getApprovalRecipients($po),
            'rejected' => collect([$po->user])->filter(),
            'canceled' => $this->getCancellationRecipients($po),
            default => collect(),
        };
    }

    /**
     * Get director users for notifications
     */
    private function getDirectors(): \Illuminate\Support\Collection
    {
        return User::role('DIRECTOR')->get();
    }

    /**
     * Get recipients for approval notifications
     */
    private function getApprovalRecipients(PurchaseOrder $po): \Illuminate\Support\Collection
    {
        $recipients = collect([$po->user])->filter(); // Creator always gets notified

        // Add department head (Accounting) - hardcoded for now, could be configurable
        $deptHeadAccounting = User::where('name', 'benny')->first();
        if ($deptHeadAccounting) {
            $recipients->push($deptHeadAccounting);
        }

        // Add accounting user - hardcoded for now, could be configurable
        $accountingUser = User::where('name', 'nessa')->first();
        if ($accountingUser) {
            $recipients->push($accountingUser);
        }

        return $recipients->unique('id');
    }

    /**
     * Get recipients for cancellation notifications
     */
    private function getCancellationRecipients(PurchaseOrder $po): \Illuminate\Support\Collection
    {
        $director = User::role('DIRECTOR')->first();

        return collect([$po->user, $director])->filter();
    }

    /**
     * Get notification instance for a specific event
     *
     * @return mixed
     */
    private function getNotificationInstance(string $event, PurchaseOrder $po, array $details)
    {
        return match ($event) {
            'created' => new PurchaseOrderCreated($po, $details),
            'approved' => new PurchaseOrderApproved($po, $details),
            'rejected' => new PurchaseOrderRejected($po, $details),
            'canceled' => new PurchaseOrderCanceled($po, $details),
            default => null,
        };
    }

    /**
     * Convert status integer to human-readable text
     */
    private function getStatusText(int $status): string
    {
        return match ($status) {
            1 => 'WAITING',
            2 => 'APPROVED',
            3 => 'REJECTED',
            4 => 'CANCELED',
            default => 'UNDEFINED',
        };
    }

    /**
     * Send custom notification to specific users
     */
    public function sendCustomNotification(array $userIds, string $subject, string $message, ?string $actionUrl = null): void
    {
        try {
            $users = User::whereIn('id', $userIds)->get();

            if ($users->isNotEmpty()) {
                // Create a generic notification or use existing notification classes
                Notification::send($users, new \App\Notifications\CustomNotification($subject, $message, $actionUrl));

                Log::info('Custom notification sent', [
                    'recipients_count' => $users->count(),
                    'subject' => $subject,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send custom notification', [
                'user_ids' => $userIds,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Queue notification for later sending (useful for bulk operations)
     */
    public function queueNotification(PurchaseOrder $po, string $event): void
    {
        // This could dispatch a job to send notifications asynchronously
        // For now, we'll send immediately but log that it was queued
        Log::info('Notification queued for processing', [
            'po_id' => $po->id,
            'event' => $event,
            'queued_at' => now(),
        ]);

        // Send immediately for now - could be replaced with actual queuing
        $this->sendNotification($po, $event);
    }
}
