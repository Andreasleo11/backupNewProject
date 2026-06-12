<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\ForecastPostProcessingJob;
use App\Services\Sap\SapSyncService;
use Illuminate\Console\Command;

final class SyncSapData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sap:sync 
                            {--endpoint=all : Specific endpoint to sync, or "all" to sync everything} 
                            {--date= : Custom start date in YYYY-MM-DD format}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync data from SAP API endpoints into local database tables';

    /**
     * Execute the console command.
     */
    public function handle(SapSyncService $service): int
    {
        $endpointOpt = $this->option('endpoint');
        $dateOpt = $this->option('date');

        $startDate = $dateOpt ?: $service->getStartDate();

        $this->info("SAP Sync started using startDate: {$startDate}");

        if ($endpointOpt === 'all') {
            $endpoints = array_keys($service->getEndpoints());
            $this->info("Syncing all " . count($endpoints) . " endpoints...");
            
            $errors = 0;
            foreach ($endpoints as $key) {
                $this->info("Syncing: {$key}...");
                $res = $service->syncEndpoint($key, $startDate);
                if ($res['success']) {
                    $this->info("  -> " . $res['message']);
                } else {
                    $this->error("  -> Failed: " . $res['message']);
                    $errors++;
                }
            }

            // Post-processing for BOM WIP union
            $this->info("Performing BOM WIP post-processing...");
            $res = $service->processBomWipUnion();
            if ($res['success']) {
                $this->info("  -> " . $res['message']);
            } else {
                $this->error("  -> Failed: " . $res['message']);
                $errors++;
            }

            if ($errors > 0) {
                $this->error("SAP Sync finished with {$errors} errors.");
                return 1;
            }

            $this->info("SAP Sync finished successfully!");

            // Dispatch forecast post-processing job di background
            ForecastPostProcessingJob::dispatch();
            $this->info('ForecastPostProcessingJob dispatched to queue.');

            return 0;
        } else {
            // Sync specific endpoint
            $this->info("Syncing specific endpoint: {$endpointOpt}...");
            $res = $service->syncEndpoint($endpointOpt, $startDate);

            if (!$res['success']) {
                $this->error("Failed: " . $res['message']);
                return 1;
            }

            $this->info($res['message']);

            // If it is one of the BOM WIP endpoints, run the post-processing union steps
            if (str_contains($endpointOpt, 'sap_fct_bom_wip')) {
                $this->info("Performing BOM WIP post-processing union...");
                $unionRes = $service->processBomWipUnion();
                if ($unionRes['success']) {
                    $this->info("  -> " . $unionRes['message']);
                } else {
                    $this->error("  -> Failed: " . $unionRes['message']);
                    return 1;
                }
            }

            $this->info("Endpoint sync completed successfully!");
            return 0;
        }
    }
}
