<?php

use App\Http\Controllers\DisciplinePageController;
use App\Http\Controllers\EvaluationDataController;
use App\Http\Controllers\PEHomeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Performance & Evaluation Routes
|--------------------------------------------------------------------------
|
| Routes for managing discipline evaluations (yayasan, magang), performance
| landing page, and employee evaluation data.
|
| RECOMMENDED PERMISSIONS:
| - performance.view-evaluations
| - performance.manage-discipline
| - performance.approve-evaluations
| - performance.access-pe
|
| RECOMMENDED ROLES: admin, super-admin, hr, hrd, pe, manager, director
|
*/

Route::middleware('auth')->group(function () {
    // PE Landing
    Route::get('/pe/landing', [PEHomeController::class, 'index'])->name('pe.landing');

    // Discipline Evaluation
    Route::get('/discipline/index', [DisciplinePageController::class, 'index'])->name('discipline.index');
    Route::post('/discipline/adddepthead', [DisciplinePageController::class, 'adddepthead'])->name('discipline.adddepthead');
    Route::post('/discipline/addGm', [DisciplinePageController::class, 'addGM'])->name('discipline.addGM');
    Route::post('/import-file', [DisciplinePageController::class, 'import'])->name('discipline.import');

    // Yayasan Table
    Route::get('/discipline/yayasan/table', [DisciplinePageController::class, 'indexYayasan'])->name('yayasan.table');
    Route::post('/discipline/yayasan/update', [DisciplinePageController::class, 'updateYayasan'])->name('discipline.yayasan.update');
    Route::post('/discipline/yayasan/lock', [DisciplinePageController::class, 'lockYayasan'])->name('discipline.yayasan.lock');
    Route::post('/discipline/yayasan/addline', [DisciplinePageController::class, 'addlineYayasan'])->name('discipline.yayasan.addline');
    Route::post('/discipline/yayasan/approvalDepthead', [DisciplinePageController::class, 'approveDepthead'])->name('approve.depthead.yayasan');
    Route::post('/discipline/yayasan/rejectDepthead', [DisciplinePageController::class, 'rejectDepthead'])->name('reject.depthead.yayasan');
    Route::post('/discipline/yayasan/rejectHRD', [DisciplinePageController::class, 'rejectHRD'])->name('reject.hrd.yayasan');
    Route::post('/discipline/yayasan/approvalHRD', [DisciplinePageController::class, 'approveHRD'])->name('approve.hrd.yayasan');
    Route::get('/discipline/yayasan/status', [DisciplinePageController::class, 'getStatusDeptYayasan'])->name('department.status.yayasan');

    // Magang Table
    Route::get('/discipline/magang/table', [DisciplinePageController::class, 'indexMagang'])->name('magang.table');
    Route::post('/discipline/magang/update', [DisciplinePageController::class, 'updateMagang'])->name('discipline.magang.update');
    Route::post('/discipline/magang/addline', [DisciplinePageController::class, 'addlineMagang'])->name('discipline.magang.addline');
    Route::post('/discipline/magang/approval', [DisciplinePageController::class, 'approveDepthead'])->name('approve.data.depthead');
    Route::post('/discipline/magang/approvalGm', [DisciplinePageController::class, 'approveGM'])->name('approve.data.gm');

    // All Discipline
    Route::get('/all/discipline', [DisciplinePageController::class, 'allDisciplineIndex'])->name('alldiscipline.index');

    // Evaluation Data
    Route::get('/monthlyEvaluationReport', [EvaluationDataController::class, 'monthlyReport'])->name('monthlyEvaluationReport');
    Route::post('/monthlyEvaluationReport/view', [EvaluationDataController::class, 'showDetails'])->name('showdetail');
    Route::post('/monthlyEvaluationReport/table', [EvaluationDataController::class, 'showtable'])->name('showtable');

    // Setting Pages for Data Upload
    Route::get('/evaluationindex', [DisciplinePageController::class, 'settingIndexEvaluation'])->name('indexevaluation');
    Route::post('/updateevaluation', [DisciplinePageController::class, 'updateEvaluation'])->name('UpdateEvaluation');
    Route::post('/deleteevaluation', [DisciplinePageController::class, 'deleteEvaluation'])->name('DeleteEvaluation');
    Route::get('/weeklyindex', [DisciplinePageController::class, 'settingIndexWeekly'])->name('weekly.index');
    Route::post('/updateweeklyeleration', [DisciplinePageController::class, 'updateWeeklyEvaluation'])->name('WeeklyUpdateEvaluation');
    Route::get('/indexdata', [DisciplinePageController::class, 'indexdata'])->name('indexdata');
    Route::post('/updatedatastep1', [DisciplinePageController::class, 'updatedatastep1'])->name('updatedata');
    Route::get('/step2disciplinedata', [DisciplinePageController::class, 'disciplineindexstep2view'])->name('step2.discipline');

    // Format Request Pages
    Route::get('/formatrequestmagang', [DisciplinePageController::class, 'formatrequestmagang'])->name('formatrequest.magang');
    Route::post('/getformat/magang', [DisciplinePageController::class, 'getFormat'])->name('get.format.magang');
    Route::get('/formatrequestyayasan', [DisciplinePageController::class, 'formatrequestyayasan'])->name('formatrequest.yayasan');
    Route::post('/getformat/yayasan', [DisciplinePageController::class, 'getFormat'])->name('get.format');
    Route::get('/formatrequestallin', [DisciplinePageController::class, 'formatrequestallin'])->name('formatrequest.all');
    Route::post('/getformat/all', [DisciplinePageController::class, 'getFormat'])->name('get.format.allin');

    // Export Pages
    Route::get('/exportyayasantodateinput', [DisciplinePageController::class, 'exportyayasantodateinput'])->name('exportyayasan.dateinput');
    Route::get('/exportyayasan', [DisciplinePageController::class, 'exportyayasanjpayroll'])->name('export.yayasan.jpayroll');
    Route::get('/exportyayasan/summary', [DisciplinePageController::class, 'exportYayasanSummary'])->name('exportyayasan.summary');
});
