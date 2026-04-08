<?php

use App\Http\Controllers\EmployeeTrainingController;
use App\Http\Controllers\FormCutiController;
use App\Http\Controllers\FormKeluarController;
use App\Http\Controllers\FormOvertimeController;
use App\Http\Controllers\hrd\ImportantDocController;
use App\Livewire\Overtime\Detail as FormOvertimeDetail;
use App\Livewire\Overtime\Form as FormOvertime;
use App\Livewire\Overtime\Index as FormOvertimeIndex;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| HR & Employee Management Routes
|--------------------------------------------------------------------------
|
| Routes for managing employee forms (overtime, cuti, keluar), employee
| trainings, and evaluation data. Note: Employee imports are in master-data.php.
|
| RECOMMENDED PERMISSIONS:
| - hr.view-forms
| - hr.approve-overtime
| - hr.approve-leave
| - hr.manage-trainings
| - hr.manage-evaluations
|
| RECOMMENDED ROLES: admin, super-admin, hr, hrd, manager
|
*/

Route::middleware('auth')->group(function () {
    // === Form Overtime (Livewire - primary interface) ===
    Route::get('/overtime-forms', FormOvertimeIndex::class)->name('overtime.index');
    Route::get('/overtime-forms/create', FormOvertime::class)->name('overtime.create');
    Route::get('/overtime-forms/{id}', FormOvertimeDetail::class)->name('overtime.detail');
    Route::get('/overtime-forms/{id}/edit', FormOvertime::class)->name('overtime.edit');

    // === Form Overtime Actions (used by Detail Livewire via direct method calls) ===
    Route::get('/formovertime/{id}/export', [FormOvertimeController::class, 'exportOvertime'])->name('overtime.export');

    // === Form Overtime Reports & Import ===
    Route::get('/overtime-summary', [FormOvertimeController::class, 'summaryView'])->name('overtime.summary');
    Route::get('/overtime-summary/export', [FormOvertimeController::class, 'exportSummaryExcel'])->name('overtime.summary.export');
    Route::get('/overtime-import', [FormOvertimeController::class, 'showForm'])->name('actual.import.form');
    Route::post('/overtime-import', [FormOvertimeController::class, 'import'])->name('actual.import');

    // === JPayroll Integration ===
    Route::get('/overtime-forms/{id}/reapprove', [FormOvertimeController::class, 'reapprove'])->name('overtime-forms.reapprove');
    Route::get('/overtime-forms/{headerId}/push-all', [FormOvertimeController::class, 'pushAllDetailsToJPayroll'])->name('overtime.jpayroll.push-all');
    Route::get('/overtime-forms/detail/{detailId}/push', [FormOvertimeController::class, 'pushSingleDetailToJPayroll'])->name('overtime.jpayroll.push-detail');

    // === Server-side detail rejection (admin action) ===
    Route::delete('/formovertime/detail/{id}/reject', [FormOvertimeController::class, 'rejectDetailServerSide'])->name('overtime.detail.reject');

    // FormCuti
    Route::get('/formcuti/index', [FormCutiController::class, 'index'])->name('formcuti');
    Route::post('/formcuti/input', [FormCutiController::class, 'input'])->name('formcuti.input');
    Route::get('/formcuti/{id}/detail', [FormCutiController::class, 'detail'])->name('formcuti.detail');
    Route::post('/formcuti/approve/{id}', [FormCutiController::class, 'approve'])->name('formcuti.approve');
    Route::post('/formcuti/reject/{id}', [FormCutiController::class, 'reject'])->name('formcuti.reject');
    Route::delete('/formcuti/delete/{id}', [FormCutiController::class, 'delete'])->name('formcuti.delete');

    // FormKeluar
    Route::get('/formkeluar/index', [FormKeluarController::class, 'index'])->name('formkeluar');
    Route::post('/formkeluar/input', [FormKeluarController::class, 'input'])->name('formkeluar.input');
    Route::get('/formkeluar/{id}/detail', [FormKeluarController::class, 'detail'])->name('formkeluar.detail');
    Route::post('/formkeluar/approve/{id}', [FormKeluarController::class, 'approve'])->name('formkeluar.approve');
    Route::post('/formkeluar/reject/{id}', [FormKeluarController::class, 'reject'])->name('formkeluar.reject');
    Route::delete('/formkeluar/delete/{id}', [FormKeluarController::class, 'delete'])->name('formkeluar.delete');

    // Employee Trainings
    Route::resource('employee_trainings', EmployeeTrainingController::class);
    Route::patch('employee_trainings/{employee_training}/evaluate', [EmployeeTrainingController::class, 'evaluate'])->name('employee_trainings.evaluate');

    // Important Doc
    Route::get('/hrd/importantdocs/', [ImportantDocController::class, 'index'])->name('hrd.importantDocs.index');
    Route::get('/hrd/importantdocs/create', [ImportantDocController::class, 'create'])->name('hrd.importantDocs.create');
    Route::post('/hrd/importantdocs/store', [ImportantDocController::class, 'store'])->name('hrd.importantDocs.store');
    Route::get('/hrd/importantdocs/{id}', [ImportantDocController::class, 'detail'])->name('hrd.importantDocs.detail');
    Route::get('/hrd/importantdocs/{id}/edit', [ImportantDocController::class, 'edit'])->name('hrd.importantDocs.edit');
    Route::put('/hrd/importantdocs/{id}', [ImportantDocController::class, 'update'])->name('hrd.importantDocs.update');
    Route::delete('/hrd/importantdocs/{id}', [ImportantDocController::class, 'destroy'])->name('hrd.importantDocs.delete');
    Route::post('/hrd/importantdocs/{id}/restore', [ImportantDocController::class, 'restore'])->name('hrd.importantDocs.restore');
    Route::delete('/hrd/importantdocs/{id}/force', [ImportantDocController::class, 'forceDelete'])->name('hrd.importantDocs.forceDelete');
    Route::delete('/hrd/importantdocs/files/{fileId}', [ImportantDocController::class, 'destroyFile'])->name('hrd.importantDocs.file.delete');
});