<?php

use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeMasterController;
use App\Http\Controllers\ImportJobController;
use App\Http\Controllers\UpdateDailyController;
use App\Livewire\MasterDataPart\ImportParts;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Master Data Routes
|--------------------------------------------------------------------------
|
| Routes for importing and managing master data including parts, employees,
| and daily updates.
|
| RECOMMENDED PERMISSIONS:
| - masterdata.import-parts
| - masterdata.manage-employees
| - masterdata.manage-imports
|
| RECOMMENDED ROLES: admin, super-admin, operations
|
*/

Route::middleware('auth')->group(function () {
    // Parts Import
    Route::get('/import-parts', ImportParts::class)->name('import.parts');

    // Employee Master Data
    Route::get('/employee-master', [EmployeeMasterController::class, 'index'])->name('employeemaster');
    Route::post('/employee-master', [EmployeeMasterController::class, 'downloadTemplate'])->name('employee.master.downloadtemplate');
    Route::post('/employee-master/upload', [EmployeeMasterController::class, 'uploadExcel'])->name('employee.master.upload');

    Route::get('/employees/import', [EmployeeController::class, 'import'])->name('employees.import');
    Route::post('/employees/import', [EmployeeController::class, 'importExcel'])->name('employees.import.excel');

    // Import Jobs
    Route::get('/import-jobs', [ImportJobController::class, 'index'])->name('import-jobs.index');

    // Daily Updates
    Route::get('/DailyUpdate', [UpdateDailyController::class, 'index'])->name('update.daily.index');
    Route::post('/DailyUpdate/process', [UpdateDailyController::class, 'process'])->name('update.daily.process');
});
