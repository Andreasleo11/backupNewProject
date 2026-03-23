<?php

use App\Http\Controllers\EmployeeTrainingController;
use App\Http\Controllers\FormCutiController;
use App\Http\Controllers\FormKeluarController;
use App\Http\Controllers\FormOvertimeController;
use App\Livewire\Overtime\Create as FormOvertimeCreate;
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
    Route::get('/overtime-forms/create', FormOvertimeCreate::class)->name('overtime.create');

    // === Form Overtime Detail & Actions (controller-backed) ===
    Route::get('/formovertime/{id}', [FormOvertimeController::class, 'detail'])->name('overtime.detail');
    Route::post('/formovertime/{id}/sign', [FormOvertimeController::class, 'sign'])->name('overtime.sign');
    Route::post('/formovertime/{id}/reject', [FormOvertimeController::class, 'reject'])->name('overtime.reject');
    Route::get('/formovertime/{id}/export', [FormOvertimeController::class, 'exportOvertime'])->name('overtime.export');

    // === Form Overtime Reports & Import ===
    Route::get('/overtime-summary', [FormOvertimeController::class, 'summaryView'])->name('overtime.summary');
    Route::get('/overtime-summary/export', [FormOvertimeController::class, 'exportSummaryExcel'])->name('overtime.summary.export');
    Route::get('/overtime-import', [FormOvertimeController::class, 'showForm'])->name('actual.import.form');
    Route::post('/overtime-import', [FormOvertimeController::class, 'import'])->name('actual.import');

    // === JPayroll Integration ===
    Route::get('/overtime-forms/{id}/reapprove', [FormOvertimeController::class, 'reapprove'])->name('overtime-forms.reapprove');

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

    // (Legacy monthlyEvaluationReport routes removed)
});
