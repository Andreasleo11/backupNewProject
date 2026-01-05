<?php

namespace App\Infrastructure\Listeners;

use App\Enums\ToDepartment;
use App\Events\PurchaseRequestStatusUpdated;
use App\Models\User;
use App\Notifications\PurchaseRequestUpdated; // The original notification class
use Illuminate\Support\Facades\Notification;

class SendPurchaseRequestStatusUpdatedNotification
{
    public function handle(PurchaseRequestStatusUpdated $event): void
    {
        $pr = $event->purchaseRequest;
        $status = $pr->status;

        // Skip if cancelled (status 8 used to be ignored in original code 'pr->status != 8', but wait...
        // Original logic: "if ($statusChanged && $pr->status != 8)"
        // Status 8 is DRAFT.
        if ($status === 8) {
            return;
        }

        $details = $this->prepareNotificationDetails($pr);
        $user = null;
        $users = [$pr->createdBy];

        switch ($status) {
            case 1: // WAITING FOR DEPT HEAD
                if (
                    $pr->from_department === 'PLASTIC INJECTION' ||
                    ($pr->from_department === 'MAINTENANCE MACHINE' && $pr->branch === 'KARAWANG')
                ) {
                    $deptHead = null;
                } elseif ($pr->from_department === 'MOULDING') {
                    if ($pr->is_import === 1) {
                        $deptHead = User::where('email', 'fang@daijo.co.id')->first();
                    } else {
                        // if is_import is false or null, notification will sent to fang and ong
                        $deptHead = User::where('is_head', 1)
                            ->whereHas('department', function ($query) use ($pr) {
                                $query->where('name', $pr->from_department);
                            })
                            ->get(); // Collection
                    }
                } elseif ($pr->from_department === 'STORE') {
                    $deptHead = User::where('is_head', 1)
                        ->whereHas('department', function ($query) {
                            $query->where('name', 'LOGISTIC');
                        })
                        ->first();
                } else {
                    $deptHead = User::where('is_head', 1)
                        ->whereHas('department', function ($query) use ($pr) {
                            $query->where('name', $pr->from_department);
                        })
                        ->first();
                }

                $user = $deptHead ?: $pr->createdBy;
                break;

            case 7: // WAITING FOR GM
                if (
                    $pr->from_department === 'PLASTIC INJECTION' ||
                    $pr->from_department === 'MAINTENANCE MACHINE'
                ) {
                    if ($pr->branch === 'KARAWANG') {
                        $gm = User::where('email', 'pawarid_pannin@daijo.co.id')->first();
                    } else {
                        $gm = User::where('email', 'albert@daijo.co.id')->first();
                    }
                } else {
                    $gm = User::whereHas('department', function ($query) {
                        $query->where('name', '!=', 'MOULDING')->where('is_gm', 1);
                    })->first();
                }
                $user = $gm ?: $pr->createdBy;
                break;

            case 6: // WAITING FOR PURCHASER
                $toDept = $pr->to_department->value;
                if ($toDept === ToDepartment::COMPUTER->value) {
                    $purchaser = User::where('email', 'vicky@daijo.co.id')->first();
                } elseif ($toDept === ToDepartment::PURCHASING->value) {
                    $purchaser = User::where('email', 'dian@daijo.co.id')->first();
                } elseif ($toDept === ToDepartment::MAINTENANCE->value) {
                    $purchaser = User::where('email', 'nur@daijo.co.id')->first();
                } elseif ($toDept === ToDepartment::PERSONALIA->value) {
                    $purchaser = User::where('email', 'ani_apriani@daijo.co.id')->first();
                } else {
                    $purchaser = $pr->createdBy;
                }

                $user = $purchaser;
                break;

            case 2: // WAITING FOR VERIFICATOR
                $verificator = User::with('specification')
                    ->whereHas('specification', function ($query) {
                        $query->where('name', 'VERIFICATOR');
                    })
                    ->where('is_head', 1)
                    ->first();

                $user = $verificator ?: $pr->createdBy;
                break;

            case 3: // WAITING FOR DIRECTOR
                $director = User::with('specification')
                    ->whereHas('specification', function ($query) {
                        $query->where('name', 'DIRECTOR');
                    })
                    ->first();

                $user = $director ?: $pr->createdBy;
                break;

            case 4: // APPROVED
            case 5: // REJECTED
                $user = $pr->createdBy;
                break;

            default:
                $user = $pr->createdBy;
                break;
        }

        // Additional CC rules
        if ($pr->to_department->value === ToDepartment::PURCHASING->value && $pr->status === 4) {
            $purchasingUsers = User::whereHas('department', function ($query) {
                $query->where('name', 'PURCHASING');
            })->get();
            $users = array_merge($users, $purchasingUsers->all());
        } elseif ($pr->to_department->value === ToDepartment::MAINTENANCE->value) {
            $ccUser = User::where('email', 'nur@daijo.co.id')->first();
            if ($ccUser) {
                $users = array_merge($users, [$ccUser]);
            }
        }

        // Merge $user into $users
        if ($user instanceof \Illuminate\Support\Collection) {
            $updatedNotificationUsers = array_merge($users, $user->all());
        } else {
            $updatedNotificationUsers = isset($user) ? array_merge($users, [$user]) : $users;
        }

        Notification::send(
            $updatedNotificationUsers,
            new PurchaseRequestUpdated($pr, $details)
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
