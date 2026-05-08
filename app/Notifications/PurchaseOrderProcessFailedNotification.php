<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PurchaseOrderProcessFailedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected string $poNumber,
        protected string $action,
        protected string $errorMessage
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title' => "Purchase Order {$this->action} Failed",
            'message' => "Failed to {$this->action} PO #{$this->poNumber}: {$this->errorMessage}",
            'po_number' => $this->poNumber,
            'type' => 'error',
        ];
    }

    public function toArray($notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
