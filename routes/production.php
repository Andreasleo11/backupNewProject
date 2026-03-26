<?php

use App\Http\Controllers\ForecastCustomerController;
use App\Http\Controllers\PEController;
use App\Http\Controllers\ProjectTrackerController;
use App\Http\Controllers\SuratPerintahKerjaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Production Routes
|--------------------------------------------------------------------------
|
| Routes for managing production operations, PE trial forms, forecast
| customer data, project tracking, and SPK (Surat Perintah Kerja).
|
| RECOMMENDED PERMISSIONS:
| - production.view
| - production.create-trials
| - production.manage-spk
| - production.track-projects
|
| RECOMMENDED ROLES: admin, super-admin, production, operations, manager
|
*/

Route::middleware('auth')->group(function () {
    // PE Trial Forms
    Route::get('/pe/trialinput', [PEController::class, 'trialinput'])->name('pe.trial');
    Route::post('/pe/trialfinish', [PEController::class, 'input'])->name('pe.input');
    Route::get('/pe/listformrequest', [PEController::class, 'view'])->name('pe.formlist');
    Route::get('/pe/listformrequest/detail/{id}', [PEController::class, 'detail'])->name('trial.detail');
    Route::post('/pe/listformrequest/detai/updateTonage/{id}', [PEController::class, 'updateTonage'])->name('update.tonage');

    // Forecast Customer Master
    Route::get('/forecastcustomermaster', [ForecastCustomerController::class, 'index'])->name('fc.index');
    Route::post('/add/forecastmaster', [ForecastCustomerController::class, 'addnewmaster'])->name('addnewforecastmaster');

    // SPK Management
    Route::get('/spk', [SuratPerintahKerjaController::class, 'index'])->name('spk.index');
    Route::get('/spk/create', [SuratPerintahKerjaController::class, 'createpage'])->name('spk.create');
    Route::post('/spk/input', [SuratPerintahKerjaController::class, 'inputprocess'])->name('spk.input');
    Route::get('/spk/{id}', [SuratPerintahKerjaController::class, 'detail'])->name('spk.detail');
    Route::put('/spk/{id}', [SuratPerintahKerjaController::class, 'update'])->name('spk.update');
    Route::delete('/spk/{id}', [SuratPerintahKerjaController::class, 'destroy'])->name('spk.delete');
    Route::get('/spk/report/monthly', [SuratPerintahKerjaController::class, 'monthlyreport'])->name('spk.monthlyreport');
    Route::put('/spk/save-autograph/{id}', [SuratPerintahKerjaController::class, 'saveAutograph'])->name('spk.save.autograph');
    Route::put('/spk/ask-a-revision/{id}', [SuratPerintahKerjaController::class, 'revision'])->name('spk.revision');
    Route::put('/spk/finish/{id}', [SuratPerintahKerjaController::class, 'finish'])->name('spk.finish');
    Route::get('/spk/{id}/reject', [SuratPerintahKerjaController::class, 'reject'])->name('spk.reject');

    // Project Tracker
    Route::get('projecttracker/index', [ProjectTrackerController::class, 'index'])->name('pt.index');
    Route::get('projecttracker/create', [ProjectTrackerController::class, 'create'])->name('pt.create');
    Route::post('projecttracker/post', [ProjectTrackerController::class, 'store'])->name('pt.store');
    Route::get('projecttracker/detail/{id}', [ProjectTrackerController::class, 'detail'])->name('pt.detail');
    Route::put('projecttracker/{id}/update-ongoing', [ProjectTrackerController::class, 'updateOngoing'])->name('pt.updateongoing');
    Route::put('projecttracker/{id}/update-test', [ProjectTrackerController::class, 'updateTest'])->name('pt.updatetest');
    Route::put('projecttracker/{id}/update-revision', [ProjectTrackerController::class, 'updateRevision'])->name('pt.updaterevision');
    Route::put('projecttracker/{id}/accept', [ProjectTrackerController::class, 'updateAccept'])->name('pt.updateaccept');
});
