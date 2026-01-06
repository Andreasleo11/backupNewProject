<?php

namespace App\Application\PurchaseRequest\Contracts;

use App\Models\PurchaseRequest;

interface SyncPrWorkflow
{
    public function sync(PurchaseRequest $pr): void;
}
