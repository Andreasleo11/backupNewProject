<?php

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

// QA/QC Home — redirect to new verification dashboard
Route::middleware(['checkDepartment:QA,QC,ACCOUNTING,PPIC,STORE,LOGISTIC,BUSINESS', 'checkSessionId'])->group(function () {
    Route::get('/qaqc/home', fn() => redirect()->route('verification.dashboard'))->name('qaqc');
});

// HRD Home
// Route::middleware(['checkDepartment:HRD,PERSONNEL,DIRECTOR', 'checkSessionId'])->group(function () {
//     Route::get('/hrd/home', [HrdHomeController::class, 'index'])->name('hrd');
//     Route::get('/hrd/important-doc', [ImportantDocController::class, 'index'])->name('importantdoc.index');
//     Route::post('/hrd/important-doc', [ImportantDocController::class, 'upload'])->name('important.doc.upload');
//     Route::get('/hrd/important-doc/{filename}/download', [ImportantDocController::class, 'download'])->name('important.doc.download');

//     Route::get('/personnel/home', [HrdHomeController::class, 'index'])->name('personnel');
// });

// Director Home & QA/QC Reports — redirect to new verification system
Route::middleware(['checkDepartment:DIRECTOR,PERSONNEL', 'checkSessionId'])->group(function () {
    Route::get('/director/qaqc', fn() => redirect()->route('verification.index'))->name('director.qaqc.index');
    // Legacy show/approve/reject removed — ApprovalEngine in verification.show handles these
    Route::get('/director/qaqc/{id}', fn($id) => redirect()->route('verification.index'))->name('director.qaqc.show');
    Route::put('/director/qaqc/{id}/approve', fn($id) => redirect()->route('verification.index'))->name('director.qaqc.approve');
    Route::put('/director/qaqc/{id}/reject', fn($id) => redirect()->route('verification.index'))->name('director.qaqc.reject');
});
