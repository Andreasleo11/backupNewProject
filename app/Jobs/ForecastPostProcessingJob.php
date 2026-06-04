<?php

namespace App\Jobs;

use App\Http\Controllers\materialPredictionController;
use App\Http\Controllers\PurchasingMaterialController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ForecastPostProcessingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Maksimal waktu eksekusi job (detik).
     * Set tinggi karena proses prediksi bisa berat.
     */
    public int $timeout = 3600;

    /**
     * Jangan retry otomatis kalau gagal.
     */
    public int $tries = 1;

    public function __construct()
    {
        //
    }

    public function handle(): void
    {
        Log::info('[ForecastPostProcessing] Job started.');

        try {
            // Step 1: Truncate tabel foremind_detail
            DB::table('foremind_final')->truncate();
            Log::info('[ForecastPostProcessing] Table foremind_final truncated.');

            // Step 2: Truncate tabel forecast_material_predictions
            DB::table('forecast_material_predictions')->truncate();
            Log::info('[ForecastPostProcessing] Table forecast_material_predictions truncated.');

            // Step 3: Jalankan PurchasingMaterialController::storeDataInNewTable
            // (setara dengan GET /store-data)
            $purchasingController = app(PurchasingMaterialController::class);
            $purchasingController->storeDataInNewTable();
            Log::info('[ForecastPostProcessing] storeDataInNewTable executed successfully.');

            // Step 4: Jalankan materialPredictionController::processForemindFinalData
            // (setara dengan GET /insert-material_prediction)
            $predictionController = app(materialPredictionController::class);
            $predictionController->processForemindFinalData();
            Log::info('[ForecastPostProcessing] processForemindFinalData executed successfully.');

            Log::info('[ForecastPostProcessing] Job completed successfully.');
        } catch (\Throwable $e) {
            Log::error('[ForecastPostProcessing] Job failed: ' . $e->getMessage(), [
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Re-throw agar job ditandai FAILED di queue
            throw $e;
        }
    }
}
