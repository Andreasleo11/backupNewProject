<?php

namespace App\Policies;

use App\Infrastructure\Persistence\Eloquent\Models\User;
use App\Models\PurchaseOrder;
use App\Enums\PurchaseOrderStatus;
use Illuminate\Auth\Access\HandlesAuthorization;

class PurchaseOrderPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['po.view-any', 'po.manage', 'system.admin']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PurchaseOrder $purchaseOrder): bool
    {
        // Users can view their own POs, or if they have view-any permission
        if ($user->id === $purchaseOrder->user_id) {
            return true;
        }

        return $user->hasAnyPermission(['po.view-any', 'po.manage', 'system.admin']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Legacy logic: MANAGEMENT dept cannot create POs unless super-admin
        if ($user->department?->name === 'MANAGEMENT' && !$user->hasRole('super-admin')) {
            return false;
        }

        return $user->hasAnyPermission(['po.manage', 'system.admin']) || $user->hasRole(['staff', 'purchaser', 'purchasing-manager']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PurchaseOrder $purchaseOrder): bool
    {
        // 1. Check if the status allows editing
        if (!$purchaseOrder->getStatusEnum()->canEdit()) {
            return false;
        }

        // 2. Check if the user is the owner or has management rights
        if ($user->id === $purchaseOrder->user_id) {
            return true;
        }

        return $user->hasAnyPermission(['po.manage', 'system.admin']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PurchaseOrder $purchaseOrder): bool
    {
        // Only owners can delete their own POs if they are in Draft/Rejected state
        if ($user->id === $purchaseOrder->user_id && $purchaseOrder->getStatusEnum() === PurchaseOrderStatus::REJECTED) {
            return true;
        }

        return $user->hasAnyPermission(['po.manage', 'system.admin']);
    }

    /**
     * Determine whether the user can approve the model.
     */
    public function approve(User $user, PurchaseOrder $purchaseOrder): bool
    {
        if (!$purchaseOrder->getStatusEnum()->canApprove()) {
            return false;
        }

        return $user->hasAnyPermission(['pr.approve', 'system.admin']) || $user->hasRole(['director', 'general-manager']);
    }

    /**
     * Determine whether the user can reject the model.
     */
    public function reject(User $user, PurchaseOrder $purchaseOrder): bool
    {
        if (!$purchaseOrder->getStatusEnum()->canReject()) {
            return false;
        }

        return $user->hasAnyPermission(['pr.reject', 'system.admin']) || $user->hasRole(['director', 'general-manager']);
    }

    /**
     * Determine whether the user can manage invoices for this PO.
     */
    public function manageInvoices(User $user, PurchaseOrder $purchaseOrder): bool
    {
        // Users can manage invoices if they own the PO, or if they are purchasers/admins
        if ($user->id === $purchaseOrder->user_id) {
            return true;
        }

        return $user->hasAnyPermission(['po.manage', 'system.admin']) || $user->hasRole('purchaser');
    }

    /**
     * Determine whether the user can manage attachments for this PO.
     */
    public function manageAttachments(User $user, PurchaseOrder $purchaseOrder): bool
    {
        if ($user->id === $purchaseOrder->user_id) {
            return true;
        }

        return $user->hasAnyPermission(['po.manage', 'system.admin']) || $user->hasRole('purchaser');
    }

    /**
     * Determine whether the user can view activity logs for this PO.
     */
    public function viewActivityLog(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->hasRole('super-admin') || $user->hasAnyPermission(['system.admin']);
    }

    /**
     * Determine whether the user can bulk approve POs.
     */
    public function bulkApprove(User $user): bool
    {
        return $user->hasAnyPermission(['pr.approve', 'system.admin']) || $user->hasRole(['director', 'general-manager']);
    }
}
