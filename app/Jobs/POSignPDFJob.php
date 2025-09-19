<?php

namespace App\Jobs;

use App\Models\PurchaseOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use setasign\Fpdi\Fpdi;

class POSignPDFJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $purchaseOrder;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(PurchaseOrder $purchaseOrder)
    {
        $this->purchaseOrder = $purchaseOrder;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $pdfPath = public_path("storage/pdfs/{$this->purchaseOrder->filename}");
            $signedPdfPath = str_replace(".pdf", "_signed.pdf", $pdfPath);

            $signaturePath = public_path("autographs/Djoni.png");

            $pdf = new Fpdi();
            $pageCount = $pdf->setSourceFile($pdfPath);

            for ($pageIndex = 1; $pageIndex <= $pageCount; $pageIndex++) {
                $pdf->AddPage();
                $templateId = $pdf->importPage($pageIndex);
                $pdf->useTemplate($templateId, 0, 0, 210);

                if ($pageIndex === $pageCount) {
                    $pdf->SetFont("Arial", "", 12);
                    $pdf->Image($signaturePath, 40, 250, 40, 20);
                }
            }

            $pdf->Output($signedPdfPath, "F");

            // Update the filename and status after signing
            $this->purchaseOrder->update([
                "filename" => basename($signedPdfPath),
                "status" => 2, // Approved after signing
                "approved_date" => now(),
            ]);
        } catch (\Exception $e) {
            // Log the error
            Log::error(
                "Error signing PDF for PurchaseOrder ID {$this->purchaseOrder->id}: {$e->getMessage()}",
            );

            // Optionally update the status to indicate failure
            $this->purchaseOrder->update(["status" => 5]); // Example: 3 = Failed
        }
    }
}
