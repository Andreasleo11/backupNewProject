<?php

declare(strict_types=1);

namespace App\Infrastructure\Notifications\Services;

use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Resolves notification recipients for Purchase Request events.
 *
 * Centralizes all the complex logic for determining who should receive
 * notifications based on PR status, department, and user roles.
 */
class PurchaseRequestRecipientResolver
{
    /**
     * Resolve recipients for PR creation notification.
     *
     * @param PurchaseRequest $pr The created purchase request
     * @return Collection<User> Collection of users to notify
     */
    public function resolveForCreation(PurchaseRequest $pr): Collection
    {
        $recipients = collect();

        // Add creator
        if ($pr->createdBy) {
            $recipients->push($pr->createdBy);
        }

        // Add department-specific recipients
        $toDepartment = strtoupper($pr->to_department?->value ?? '');

        if ($toDepartment === 'MAINTENANCE') {
            // Nur handles maintenance PRs
            $nurUser = User::where('email', 'nur@daijo.co.id')->first();
            if ($nurUser) {
                $recipients->push($nurUser);
            }
        } elseif ($toDepartment === 'PURCHASING') {
            // Fang handles purchasing PRs
            $fangUser = User::where('email', 'fang@daijo.co.id')->first();
            if ($fangUser) {
                $recipients->push($fangUser);
            }
        }

        return $recipients->unique('id');
    }

    /**
     * Resolve recipients for status update notification.
     *
     * @param PurchaseRequest $pr The purchase request
     * @param int $newStatus The new status code
     * @return Collection<User> Collection of users to notify
     */
    public function resolveForStatusUpdate(PurchaseRequest $pr, int $newStatus): Collection
    {
        $recipients = collect();

        // Always notify creator
        if ($pr->createdBy) {
            $recipients->push($pr->createdBy);
        }

        $toDepartment = strtoupper($pr->to_department?->value ?? '');

        // Status 6: Pending Purchaser
        if ($newStatus === 6) {
            // Notify all purchasers
            $purchasers = User::role('PURCHASER')->get();

            $recipients = $recipients->merge($purchasers);

            // Department-specific purchasers
            if ($toDepartment === 'MAINTENANCE') {
                $nur = User::where('email', 'nur@daijo.co.id')->first();
                if ($nur) {
                    $recipients->push($nur);
                }
            } elseif ($toDepartment === 'PURCHASING' || $toDepartment === 'COMPUTER') {
                $fang = User::where('email', 'fang@daijo.co.id')->first();
                if ($fang) {
                    $recipients->push($fang);
                }
            }
        }

        // Status 2: Pending Verificator
        if ($newStatus === 2) {
            $verificators = User::role('VERIFICATOR')->get();

            $recipients = $recipients->merge($verificators);
        }

        // Status 3: Pending Director
        if ($newStatus === 3) {
            $directors = User::role('DIRECTOR')->get();

            $recipients = $recipients->merge($directors);
        }

        // Status 7: Pending GM
        if ($newStatus === 7) {
            $gms = User::where('is_gm', 1)->get();
            $recipients = $recipients->merge($gms);
        }

        // Status 4: Approved - notify creator and department head
        if ($newStatus === 4) {
            $fromDept = strtoupper($pr->from_department ?? '');

            $deptHeads = User::whereHas('department', function ($query) use ($fromDept) {
                $query->where('name', $fromDept);
            })->where('is_head', 1)->get();

            $recipients = $recipients->merge($deptHeads);
        }

        // Status 5: Rejected - notify creator and approver
        if ($newStatus === 5) {
            // Creator already added above
        }

        return $recipients->unique('id');
    }

    /**
     * Get notification message for creation.
     *
     * @param PurchaseRequest $pr The purchase request
     * @return string Notification message
     */
    public function getCreationMessage(PurchaseRequest $pr): string
    {
        return sprintf(
            'New Purchase Request #%s created by %s',
            $pr->doc_num ?? $pr->pr_no ?? $pr->id,
            $pr->createdBy->name ?? 'Unknown'
        );
    }

    /**
     * Get notification message for status update.
     *
     * @param PurchaseRequest $pr The purchase request
     * @param int $newStatus The new status
     * @return string Notification message
     */
    public function getStatusUpdateMessage(PurchaseRequest $pr, int $newStatus): string
    {
        $statusText = match ($newStatus) {
            1 => 'Pending Department Head Approval',
            2 => 'Pending Verificator Approval',
            3 => 'Pending Director Approval',
            4 => 'Approved',
            5 => 'Rejected',
            6 => 'Pending Purchaser Approval',
            7 => 'Pending GM Approval',
            8 => 'Cancelled',
            default => 'Status Updated',
        };

        return sprintf(
            'Purchase Request #%s - %s',
            $pr->doc_num ?? $pr->pr_no ?? $pr->id,
            $statusText
        );
    }
}
