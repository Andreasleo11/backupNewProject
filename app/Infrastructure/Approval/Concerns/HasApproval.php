<?php

namespace App\Infrastructure\Approval\Concerns;

use App\Infrastructure\Persistence\Eloquent\Models\ApprovalRequest;

trait HasApproval
{
    public function approvalRequest()
    {
        return $this->morphOne(ApprovalRequest::class, 'approvable');
    }

    // convenience
    public function approvalStatus(): string
    {
        return optional($this->approvalRequest)->status ?? 'DRAFT';
    }

    /**
     * Default Approvable implementation (Override in Model if needed)
     */
    public function getApprovableTypeLabel(): string
    {
        return str_replace(['App\\Models\\', 'App\\Infrastructure\\Persistence\\Eloquent\\Models\\'], '', get_class($this));
    }

    public function getApprovableIdentifier(): string
    {
        return (string) $this->getKey();
    }

    public function getApprovableShowUrl(): string
    {
        return '#';
    }

    public function getApprovableDepartmentName(): ?string
    {
        return null;
    }

    public function getApprovableBranchValue(): ?string
    {
        return null;
    }
}
