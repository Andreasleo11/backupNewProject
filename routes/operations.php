<?php

use App\Http\Controllers\EmployeeDailyReportController;
use App\Http\Controllers\DownloadUploadController;
use App\Http\Controllers\PreviewUploadController;
use App\Livewire\DailyReportIndex;
use App\Livewire\DestinationForm;
use App\Livewire\DestinationIndex;
use App\Livewire\FileLibrary;
use App\Livewire\Services\Form as ServiceForm;
use App\Livewire\Vehicles\Form as VehiclesForm;
use App\Livewire\Vehicles\Index as VehiclesIndex;
use App\Livewire\Vehicles\Show as VehiclesShow;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Operations Routes
|--------------------------------------------------------------------------
|
| Routes for managing daily reports, vehicles, services, destinations,
| and file library operations.
|
| RECOMMENDED PERMISSIONS:
| - operations.view-reports
| - operations.manage-vehicles
| - operations.manage-services
| - operations.manage-destinations
| - operations.access-files
|
| RECOMMENDED ROLES: admin, super-admin, operations, logistic, manager
|
*/

Route::middleware('auth')->group(function () {
    // Daily Reports
    Route::get('/daily-reports', DailyReportIndex::class)->name('daily-reports.index');

    // Employee Daily Reports
    Route::get('/store-data', [\App\Http\Controllers\PurchasingMaterialController::class, 'storeDataInNewTable'])->name('construct_data');
    Route::get('/insert-material_prediction', [\App\Http\Controllers\materialPredictionController::class, 'processForemindFinalData'])->name('material_prediction');

    // Destinations
    Route::get('/destinations', DestinationIndex::class)->name('destination.index');
    Route::get('/destinations/create', DestinationForm::class)->name('destination.create');
    Route::get('/destinations/{id}/edit', DestinationForm::class)->name('destination.edit');

    // Vehicles
    Route::get('/vehicles', VehiclesIndex::class)->name('vehicles.index');
    Route::get('/vehicles/{vehicle}', VehiclesShow::class)->name('vehicles.show');
    Route::get('/vehicle/create', VehiclesForm::class)->name('vehicles.create');
    Route::get('/vehicles/{vehicle}/edit', VehiclesForm::class)->name('vehicles.edit');

    // Vehicle Services
    Route::get('/services/create/{vehicle}', ServiceForm::class)->name('services.create');
    Route::get('/services/{record}/edit', ServiceForm::class)->name('services.edit');

    // File Library
    Route::get('/files', FileLibrary::class)->name('files.index');
    Route::get('/files/{upload}/download', DownloadUploadController::class)->name('files.download');
    Route::get('/files/{upload}/preview', PreviewUploadController::class)->name('files.preview');
});
