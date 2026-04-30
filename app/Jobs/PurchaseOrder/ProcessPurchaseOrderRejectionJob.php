<?php

namespace App\Jobs\PurchaseOrder;

use App\Application\Approval\Contracts\Approvals;
use App\Models\PurchaseOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessPurchaseOrderRejectionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected PurchaseOrder $purchaseOrder,
        protected int $userId,
        protected string $reason
    ) {}

    /**
     * Execute the job.
     */
    public function handle(Approvals $approvals): void
    {
        try {
            // 1. Process the rejection in the approval workflow
            $approvals->reject($this->purchaseOrder, $this->userId, $this->reason);

            Log::info('Successfully processed async PO rejection', [
                'po_id' => $this->purchaseOrder->id,
                'po_number' => $this->purchaseOrder->po_number,
                'user_id' => $this->userId,
                'reason' => $this->reason
            ]);

        } catch (\Exception $e) {
            Log::error('Async PO rejection failed', [
                'po_id' => $this->purchaseOrder->id,
                'user_id' => $this->userId,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }
}
