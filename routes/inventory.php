<?php

use App\Http\Controllers\MaintenanceInventoryController;
use App\Http\Controllers\MasterInventoryController;
use App\Http\Controllers\MasterTintaController;
use App\Http\Controllers\StockTintaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Inventory & Assets Routes
|--------------------------------------------------------------------------
|
| Routes for managing inventory, stock, and asset tracking.
| Includes master tinta (ink), master inventory, and maintenance inventory.
|
| RECOMMENDED PERMISSIONS:
| - inventory.view
| - inventory.create
| - inventory.update
| - inventory.delete
| - inventory.manage-types
|
| RECOMMENDED ROLES: admin, super-admin, inventory, operations
|
*/

Route::middleware('auth')->group(function () {
    // Master Tinta (Computer Group / Ink Management)
    Route::get('mastertinta/index', [MasterTintaController::class, 'index'])->name('mastertinta.index');
    Route::get('request/index', [MasterTintaController::class, 'requestpageindex'])->name('testing.request');
    Route::get('mastertinta/transaction/list', [MasterTintaController::class, 'listtransaction'])->name('transaction.list');
    Route::post('/mastertinta/request/process', [MasterTintaController::class, 'requeststore'])->name('stockrequest.store');
    Route::get('mastertinta/transaction/index', [MasterTintaController::class, 'transactiontintaview'])->name('mastertinta.transaction.index');
    Route::post('mastertinta/transaction/process', [MasterTintaController::class, 'storetransaction'])->name('mastertinta.process');
    Route::get('/masterstock/get-items/{masterStockId}', [MasterTintaController::class, 'getItems']);
    Route::get('/stock/get-available-quantity/{stock_id}/{department_id}', [MasterTintaController::class, 'getAvailableQuantity']);

    // Master Inventory
    Route::get('masterinventory/index', [MasterInventoryController::class, 'index'])->name('masterinventory.index');
    Route::get('masterinventory/create', [MasterInventoryController::class, 'createpage'])->name('masterinventory.createpage');
    Route::post('masterinventory/store', [MasterInventoryController::class, 'store'])->name('masterinventory.store');
    Route::get('masterinventory/detail/{id}', [MasterInventoryController::class, 'detail'])->name('masterinventory.detail');
    Route::get('masterinventory/type', [MasterInventoryController::class, 'typeAdder'])->name('masterinventory.typeindex');
    Route::delete('/masterinventory/{id}', [MasterInventoryController::class, 'destroy'])->name('masterinventory.delete');
    Route::post('masterinventory/generate/qr/{id}', [MasterInventoryController::class, 'generateQr'])->name('generate.hardware.qrcode');
    Route::post('/add/hardware/type', [MasterInventoryController::class, 'addHardwareType'])->name('add.hardware.type');
    Route::post('/add/software/type', [MasterInventoryController::class, 'addSoftwareType'])->name('add.software.type');
    Route::delete('/delete/type', [MasterInventoryController::class, 'deleteType'])->name('delete.type');
    Route::get('/export-inventory', [MasterInventoryController::class, 'export'])->name('export.inventory');
    Route::get('masterinventory/{id}/edit', [MasterInventoryController::class, 'editpage'])->name('masterinventory.editpage');
    Route::put('masterinventory/{id}', [MasterInventoryController::class, 'update'])->name('masterinventory.update');
    Route::put('masterinventory/update/repairhistory/{id}', [MasterInventoryController::class, 'updateHistory'])->name('inventory.update');
    Route::post('masterinventory/repairs', [MasterInventoryController::class, 'CreateRepair'])->name('repair.store');
    Route::get('/items/types/{type}', [MasterInventoryController::class, 'getItems'])->name('items.get');
    Route::get('/items/available', [MasterInventoryController::class, 'getAvailableItems']);

    // Maintenance Inventory Reports
    Route::get('maintenanceInventoryReports', [MaintenanceInventoryController::class, 'index'])->name('maintenance.inventory.index');
    Route::get('maintenanceInventoryReports/create/{id?}', [MaintenanceInventoryController::class, 'create'])->name('maintenance.inventory.create');
    Route::get('maintenanceInventoryReports/edit/{id}', [MaintenanceInventoryController::class, 'edit'])->name('maintenance.inventory.edit');
    Route::put('maintenanceInventoryReports/{id}', [MaintenanceInventoryController::class, 'update'])->name('maintenance.inventory.update');
    Route::post('maintenanceInventoryReports', [MaintenanceInventoryController::class, 'store'])->name('maintenance.inventory.store');
    Route::get('maintenanceInventoryReports/{id}', [MaintenanceInventoryController::class, 'show'])->name('maintenance.inventory.show');

    // Stock Tinta
    Route::get('/stock-tinta-index', [StockTintaController::class, 'index'])->name('stocktinta');
});
