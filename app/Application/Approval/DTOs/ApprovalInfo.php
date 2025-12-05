<?php

namespace App\Application\Approval\DTOs;

final class ApprovalInfo 
{
    public function __construct(
        public readonly int $id,
        public readonly string $status,
        public readonly ?int $currentStep,
    ){}
}