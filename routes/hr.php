<?php

use App\Http\Controllers\DisciplinePageController;
use App\Http\Controllers\EmployeeTrainingController;
use App\Http\Controllers\EvaluationDataController;
use App\Http\Controllers\FormCutiController;
use App\Http\Controllers\FormKeluarController;
use App\Http\Controllers\FormOvertimeController;
use App\Livewire\Overtime\Create as FormOvertimeCreate;
use App\Livewire\Overtime\Detail as OvertimeDetail;
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
    // Form Overtime (Livewire)
    Route::get('/form-overtime', FormOvertimeIndex::class)->name('formovertime.index');
    Route::get('/formovertime/create', FormOvertimeCreate::class)->name('formovertime.create');
    Route::get('/formovertime/{id}', [FormOvertimeController::class, 'detail'])->name('overtime.detail');

    // Form Overtime (Controller)
    Route::get('formovertime/{id}/detail-old', [FormOvertimeController::class, 'detail'])->name('formovertime.detail');
    Route::post('add-new-data/{employee}', [FormOvertimeController::class, 'addnewdataovertime'])->name('add.employee.overtime');
    Route::post('formovertime/save-autograph', [FormOvertimeController::class, 'saveAutograph'])->name('formovertime.autograph');
    Route::post('formovertime/reject', [FormOvertimeController::class, 'rejectovertime'])->name('formovertime.reject');
    Route::get('formovertime/index/edit/{id}', [FormOvertimeController::class, 'editIndex'])->name('formovertime.editIndex');
    Route::post('formovertime/update/editindex/{id}', [FormOvertimeController::class, 'updateIndex'])->name('formovertime.updateIndex');
    Route::delete('formovertime/delete/{id}', [FormOvertimeController::class, 'deleteheader'])->name('formovertime.delete');
    Route::put('formovertime/cancel/{id}', [FormOvertimeController::class, 'cancel'])->name('formovertime.cancel');

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

    // Evaluation Data
    Route::get('/monthlyEvaluationReport', [EvaluationDataController::class, 'monthlyReport'])->name('monthlyEvaluationReport');
    Route::post('/monthlyEvaluationReport/view', [EvaluationDataController::class, 'showDetails'])->name('showdetail');
    Route::post('/monthlyEvaluationReport/table', [EvaluationDataController::class, 'showtable'])->name('showtable');
});
