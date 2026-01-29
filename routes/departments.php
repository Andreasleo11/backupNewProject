<?php

use App\Http\Controllers\director\DirectorHomeController;
use App\Http\Controllers\director\ReportController;
use App\Http\Controllers\hrd\HrdHomeController;
use App\Http\Controllers\hrd\ImportantDocController;
use App\Http\Controllers\qaqc\QaqcHomeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Department Home Routes
|--------------------------------------------------------------------------
|
| Routes for department-specific home pages. These routes are restricted
| by department middleware and provide departmental dashboards.
|
| RECOMMENDED PERMISSIONS:
| - department.access-home
|
| RECOMMENDED ROLES: Based on department (QA, QC, HRD, Director, etc.)
|
*/

// QA/QC Home
Route::middleware(['checkDepartment:QA,QC,ACCOUNTING,PPIC,STORE,LOGISTIC,BUSINESS', 'checkSessionId'])->group(function () {
    Route::get('/qaqc/home', [QaqcHomeController::class, 'index'])->name('qaqc');
});

// HRD Home
Route::middleware(['checkDepartment:HRD,PERSONNEL,DIRECTOR', 'checkSessionId'])->group(function () {
    Route::get('/hrd/home', [HrdHomeController::class, 'index'])->name('hrd');
    Route::get('/hrd/important-doc', [ImportantDocController::class, 'index'])->name('importantdoc.index');
    Route::post('/hrd/important-doc', [ImportantDocController::class, 'upload'])->name('important.doc.upload');
    Route::get('/hrd/important-doc/{filename}/download', [ImportantDocController::class, 'download'])->name('important.doc.download');

    Route::get('/personnel/home', [HrdHomeController::class, 'index'])->name('personnel');
});

// Director Home & QA/QC Reports
Route::middleware(['checkDepartment:DIRECTOR,PERSONNEL', 'checkSessionId'])->group(function () {
    Route::get('/director/home', [DirectorHomeController::class, 'index'])->name('director');
    Route::get('/director/qaqc', [ReportController::class, 'index'])->name('director.qaqc.index');
    Route::get('/director/qaqc/{id}', [ReportController::class, 'show'])->name('director.qaqc.show');
    Route::put('/director/qaqc/{id}/approve', [ReportController::class, 'approve'])->name('director.qaqc.approve');
    Route::put('/director/qaqc/{id}/reject', [ReportController::class, 'reject'])->name('director.qaqc.reject');
   
    // Warning log
    Route::post('/director/warning-log', [DirectorHomeController::class, 'storeWarningLog'])->name('director.store.warning.log');
});
