<?php

namespace App\Infrastructure\Listeners;

use App\Events\PurchaseRequestCreated;
use App\Infrastructure\Notifications\Services\PurchaseRequestRecipientResolver;
use App\Notifications\PurchaseRequestCreated as PurchaseRequestCreatedNotification;
use Illuminate\Support\Facades\Notification;

class SendPurchaseRequestCreatedNotification
{
    public function __construct(
        private PurchaseRequestRecipientResolver $recipientResolver
    ) {}

    public function handle(PurchaseRequestCreated $event): void
    {
        $pr = $event->purchaseRequest;

        // Use Recipient Resolver Service to determine who gets notified
        $recipients = $this->recipientResolver->resolveForCreation($pr);

        if ($recipients->isEmpty()) {
            return; // No recipients, skip notification
        }

        $details = $this->prepareNotificationDetails($pr);

        Notification::send(
            $recipients,
            new PurchaseRequestCreatedNotification($pr, $details)
        );
    }

    private function prepareNotificationDetails($pr): array
    {
        // Use Recipient Resolver for status text
        $statusText = $this->recipientResolver->getStatusUpdateMessage($pr, $pr->status);

        $body = "Here's the detail : <br>
                - Doc. Num : $pr->doc_num <br>
                - PR No. : $pr->pr_no <br>
                - Created By : {$pr->createdBy->name} <br>
                - Date PR : $pr->date_pr <br>
                - Date Required : $pr->date_required <br>
                - PIC : $pr->pic <br>
                - Remark : $pr->remark <br>
                - To Department : {$pr->to_department->value} <br>
                - Status : $statusText";

        return [
            'greeting' => 'Purchase Request Notification',
            'actionText' => 'Check Now',
            'actionURL' => route('purchase-requests.show', $pr->id),
            'body' => $body,
        ];
    }
}
