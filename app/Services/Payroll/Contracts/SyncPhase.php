<?php

declare(strict_types=1);

namespace App\Services\Payroll\Contracts;

use App\Services\Payroll\Sync\SyncContext;

interface SyncPhase
{
    /**
     * Execute this phase using the shared context.
     * Implementations should report progress via $ctx->progress.
     */
    public function execute(SyncContext $ctx): void;
}
