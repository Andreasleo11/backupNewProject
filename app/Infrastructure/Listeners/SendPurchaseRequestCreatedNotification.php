<?php

namespace App\Infrastructure\Listeners;

use App\Enums\ToDepartment;
use App\Events\PurchaseRequestCreated;
use App\Models\User;
use App\Notifications\PurchaseRequestCreated as PurchaseRequestCreatedNotification;
use Illuminate\Support\Facades\Notification;

class SendPurchaseRequestCreatedNotification
{
    public function handle(PurchaseRequestCreated $event): void
    {
        $pr = $event->purchaseRequest;

        $details = $this->prepareNotificationDetails($pr);
        
        // Determine recipients
        $users = [$pr->createdBy]; // Assuming relation is loaded or lazy-loaded
        
        $extraUser = null;
        if ($pr->to_department->value === ToDepartment::MAINTENANCE->value) {
            if (
                $pr->from_department === 'PLASTIC INJECTION' &&
                $pr->branch === 'KARAWANG'
            ) {
                $extraUser = null;
            } else {
                $extraUser = User::where('email', 'nur@daijo.co.id')->first();
            }
        }

        if ($extraUser) {
            $users[] = $extraUser;
        }

        Notification::send(
            $users,
            new PurchaseRequestCreatedNotification($pr, $details)
        );
    }

    private function prepareNotificationDetails($pr): array
    {
        $statusText = $this->getStatusText($pr->status);

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

    private function getStatusText(int $status): string
    {
        return match ($status) {
            1 => 'WAITING FOR DEPT HEAD',
            2 => 'WAITING FOR VERIFICATOR',
            3 => 'WAITING FOR DIRECTOR',
            4 => 'APPROVED',
            5 => 'REJECTED',
            6 => 'WAITING FOR PURCHASER',
            7 => 'WAITING FOR GM',
            default => 'NOT DEFINED',
        };
    }
}
