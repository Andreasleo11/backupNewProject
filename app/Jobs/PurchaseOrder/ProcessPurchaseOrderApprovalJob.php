<?php

namespace App\Jobs\PurchaseOrder;

use App\Application\Approval\Contracts\Approvals;
use App\Models\PurchaseOrder;
use App\Services\PdfProcessingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessPurchaseOrderApprovalJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected PurchaseOrder $purchaseOrder,
        protected int $userId,
        protected ?string $remarks = 'Bulk approved via background process'
    ) {}

    /**
     * Execute the job.
     */
    public function handle(Approvals $approvals, PdfProcessingService $pdfService): void
    {
        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($approvals, $pdfService) {
                // 1. Process the approval workflow status
                $approvals->approve($this->purchaseOrder, $this->userId, $this->remarks);

                // 2. Process the PDF signature
                $pdfService->sign($this->purchaseOrder, $this->userId);

                // 3. Update approval date
                $this->purchaseOrder->update([
                    'approved_date' => now()
                ]);
            });

            Log::info('Successfully processed async PO approval and signing', [
                'po_id' => $this->purchaseOrder->id,
                'po_number' => $this->purchaseOrder->po_number,
                'user_id' => $this->userId
            ]);

        } catch (\Exception $e) {
            Log::error('Async PO approval failed', [
                'po_id' => $this->purchaseOrder->id,
                'user_id' => $this->userId,
                'error' => $e->getMessage()
            ]);
            
            // Re-throw to allow for job retries if configured
            throw $e;
        }
    }
}
