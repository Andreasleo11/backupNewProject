<?php

namespace App\Domain\Approval\Contracts;

interface Approvable {
    /** 
     * Must return a morphone relationship to ApprovalRequest.
     * 
     * 
     */
    public function approvalRequest();
}