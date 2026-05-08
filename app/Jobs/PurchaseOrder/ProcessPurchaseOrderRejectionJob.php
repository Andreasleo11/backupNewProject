<?php

namespace App\Jobs\PurchaseOrder;

use App\Application\Approval\Contracts\Approvals;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Notifications\PurchaseOrderProcessFailedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
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
                'reason' => $this->reason,
            ]);

        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();

            Log::error('Async PO rejection failed', [
                'po_id' => $this->purchaseOrder->id,
                'user_id' => $this->userId,
                'error' => $errorMessage,
            ]);

            // 1. Store error in cache for real-time polling feedback
            Cache::put("po_process_error_{$this->purchaseOrder->id}", $errorMessage, now()->addMinutes(5));

            // 2. Notify the user via database notification
            $user = User::find($this->userId);
            if ($user) {
                $user->notify(new PurchaseOrderProcessFailedNotification(
                    $this->purchaseOrder->po_number,
                    'Rejection',
                    $errorMessage
                ));
            }

            throw $e;
        }
    }
}
