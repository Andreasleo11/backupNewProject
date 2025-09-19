<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\PurchasingMaterialController;
use App\Http\Controllers\materialPredictionController;
use App\Services\FctForecastService;

class SyncSapServiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $serviceClass;
    public $startDate;
    public $label;

    public function __construct(string $serviceClass, string $startDate, string $label)
    {
        $this->serviceClass = $serviceClass;
        $this->startDate = $startDate;
        $this->label = $label;
    }

    public function handle()
    {
        $service = app($this->serviceClass);

        Log::info("Queue start: {$this->label}");

        $service->syncData($this->startDate);

        Log::info("Queue done: {$this->label}");

        // Debug log
        Log::info("Checking serviceClass: " . $this->serviceClass);
        Log::info("Comparing with: " . FctForecastService::class);

        if ($this->serviceClass === FctForecastService::class) {
            Log::info("Condition matched! Starting post-processing...");
            $this->handleForecastPostProcessing();
        } else {
            Log::info("Condition not matched, skipping post-processing");
        }
    }

    private function handleForecastPostProcessing()
    {
        try {
            Log::info("Starting forecast post-processing...");

            // 1. Truncate tables
            DB::table("foremind_final")->truncate();
            DB::table("forecast_material_predictions")->truncate();
            Log::info("Tables truncated successfully");

            // 2. Execute first controller method
            $purchasingController = app(PurchasingMaterialController::class);
            $purchasingController->storeDataInNewTable();
            Log::info("PurchasingMaterialController::storeDataInNewTable executed");

            // 3. Execute second controller method
            $predictionController = app(materialPredictionController::class);
            $predictionController->processForemindFinalData();
            Log::info("materialPredictionController::processForemindFinalData executed");

            Log::info("Forecast post-processing completed successfully");
        } catch (\Exception $e) {
            Log::error("Error in forecast post-processing: " . $e->getMessage());
            throw $e; // Re-throw untuk trigger job failure
        }
    }
}
