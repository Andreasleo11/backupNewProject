<?php

namespace App\Console\Commands;

use App\Jobs\SyncSapServiceJob;
use App\Services\FctBomWipService;
use App\Services\FctForecastService;
use App\Services\FctInventoryFgService;
use App\Services\FctInventoryMtrService;
use App\Services\FctLineProductionService;
use Illuminate\Console\Command;

class UpdateForecast extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-forecast';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update semua data dari 5 service SAP (BOM WIP, Inventory MTR/FG, Line Prod, Forecast)';

    protected $fctBomWipService;

    protected $fctInventoryMtrService;

    protected $fctInventoryFgService;

    protected $fctLineProductionService;

    protected $fctForecastService;

    public function __construct(
        FctBomWipService $fctBomWipService,
        FctInventoryMtrService $fctInventoryMtrService,
        FctInventoryFgService $fctInventoryFgService,
        FctLineProductionService $fctLineProductionService,
        FctForecastService $fctForecastService,
    ) {
        parent::__construct();

        $this->fctBomWipService = $fctBomWipService;
        $this->fctInventoryMtrService = $fctInventoryMtrService;
        $this->fctInventoryFgService = $fctInventoryFgService;
        $this->fctLineProductionService = $fctLineProductionService;
        $this->fctForecastService = $fctForecastService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $startDate = '2025-03-01';
        $this->info("📅 Mulai enqueue job dengan startDate: $startDate");

        $services = [
            ['label' => 'FCT BOM WIP', 'class' => \App\Services\FctBomWipService::class],
            [
                'label' => 'FCT INVENTORY MTR',
                'class' => \App\Services\FctInventoryMtrService::class,
            ],
            ['label' => 'FCT INVENTORY FG', 'class' => \App\Services\FctInventoryFgService::class],
            [
                'label' => 'FCT LINE PRODUCTION',
                'class' => \App\Services\FctLineProductionService::class,
            ],
            ['label' => 'SAP FORECAST', 'class' => \App\Services\FctForecastService::class],
        ];

        foreach ($services as $service) {
            $this->line("🚀 Enqueue: {$service['label']}");
            SyncSapServiceJob::dispatch($service['class'], $startDate, $service['label']);
        }

        $this->info('✅ Semua job berhasil dimasukkan ke queue.');
    }
}
