<?php

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
    Route::get('assets/dashboard', \App\Livewire\Assets\AssetDashboard::class)->name('assets.dashboard');
    Route::get('assets/manage', \App\Livewire\Assets\AssetManager::class)->name('assets.manage');
    Route::get('assets/categories', \App\Livewire\Assets\AssetCategoryManager::class)->name('assets.categories');
    Route::get('assets/component-types', \App\Livewire\Assets\ComponentTypeManager::class)->name('assets.component-types');
    Route::get('assets/maintenance-reports', \App\Livewire\Assets\AssetMaintenanceReportManager::class)->name('assets.maintenance-reports');
    Route::get('assets/{id}', \App\Livewire\Assets\AssetShow::class)->name('assets.show');
    Route::get('consumables/manage', \App\Livewire\Consumables\ConsumableManager::class)->name('consumables.manage');
    Route::get('locations/manage', \App\Livewire\Locations\LocationManager::class)->name('locations.manage');
    Route::get('consumables/categories', \App\Livewire\Consumables\ConsumableCategoryManager::class)->name('consumables.categories');
});
