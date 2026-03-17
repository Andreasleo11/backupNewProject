<?php

namespace App\Domain\Approval\Contracts;

interface Approvable
{
    /**
     * Must return a morphone relationship to ApprovalRequest.
     */
    public function approvalRequest();

    /**
     * Get the primary key of the model.
     */
    public function getKey();

    /**
     * The display name of the module/report type (e.g. "Purchase Request").
     */
    public function getApprovableTypeLabel(): string;

    /**
     * The document identifier (e.g. "PR/1/2026").
     */
    public function getApprovableIdentifier(): string;

    /**
     * The URL to view the document.
     */
    public function getApprovableShowUrl(): string;
}
