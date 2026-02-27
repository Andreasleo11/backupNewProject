<?php

use App\Http\Controllers\DisciplinePageController;
use App\Http\Controllers\EvaluationDataController;
use App\Http\Controllers\EvaluationDataWeeklyController;
use App\Http\Controllers\PEHomeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Performance & Evaluation Routes
|--------------------------------------------------------------------------
|
| All routes for the Performance & Evaluation feature group, including:
|  - Discipline evaluations (regular, Yayasan, Magang)
|  - Individual yearly evaluation format pages
|  - Export routes (JPayroll, summary)
|  - Data lock / approval flows
|
| ACCESS: middleware('auth') — role/policy checks are inside each controller.
|
| RECOMMENDED ROLES: admin, super-admin, hr, hrd, pe, manager, director
|
*/

Route::middleware('auth')->group(function () {

    // ──────────────────────────────────────────────
    // PE Landing Page
    // ──────────────────────────────────────────────
    Route::get('/pe/landing', [PEHomeController::class, 'index'])->name('pe.landing');

    // ──────────────────────────────────────────────
    // Discipline — Regular Employees
    // ──────────────────────────────────────────────
    // Main listing for department heads (their own dept employees)
    Route::get('/discipline/index', [DisciplinePageController::class, 'index'])->name('discipline.index');
    Route::put('/edit/discipline/{id}', [DisciplinePageController::class, 'update'])->name('editdiscipline');

    // Import attendance data from Excel
    Route::post('/import-file', [DisciplinePageController::class, 'import'])->name('discipline.import');

    // Approve (dept head submits dept approval for regular discipline)
    Route::post('/discipline/adddepthead', [DisciplinePageController::class, 'approve_depthead'])->name('discipline.adddepthead');

    // Approve (GM submits GM approval for regular discipline)
    Route::post('/discipline/addGm', [DisciplinePageController::class, 'approve_gm'])->name('discipline.addGM');

    // ──────────────────────────────────────────────
    // Discipline — Yayasan Table
    // ──────────────────────────────────────────────
    Route::get('/discipline/yayasan/table', [DisciplinePageController::class, 'indexyayasan'])->name('yayasan.table');
    Route::post('/discipline/yayasan/update/{id}', [DisciplinePageController::class, 'updateyayasan'])->name('discipline.yayasan.update');
    Route::post('/discipline/yayasan/lock', [DisciplinePageController::class, 'lockdata'])->name('discipline.yayasan.lock');
    Route::post('/discipline/yayasan/addline', [DisciplinePageController::class, 'addlineYayasan'])->name('discipline.yayasan.addline');
    Route::post('/discipline/yayasan/approvalDepthead', [DisciplinePageController::class, 'approve_depthead_button'])->name('approve.depthead.yayasan');
    Route::post('/discipline/yayasan/rejectDepthead', [DisciplinePageController::class, 'reject_depthead_button'])->name('reject.depthead.yayasan');
    Route::post('/discipline/yayasan/rejectHRD', [DisciplinePageController::class, 'reject_hrd_button'])->name('reject.hrd.yayasan');
    Route::post('/discipline/yayasan/approvalHRD', [DisciplinePageController::class, 'approve_hrd_button'])->name('approve.hrd.yayasan');
    Route::get('/discipline/yayasan/status', [DisciplinePageController::class, 'getDepartmentStatusYayasan'])->name('department.status.yayasan');

    // ──────────────────────────────────────────────
    // Discipline — Magang (Internship) Table
    // ──────────────────────────────────────────────
    Route::get('/discipline/magang/table', [DisciplinePageController::class, 'indexmagang'])->name('magang.table');
    Route::post('/discipline/magang/update/{id}', [DisciplinePageController::class, 'updatemagang'])->name('discipline.magang.update');
    Route::post('/discipline/magang/addline', [DisciplinePageController::class, 'addlineMagang'])->name('discipline.magang.addline');
    Route::post('/discipline/magang/approval', [DisciplinePageController::class, 'approve_depthead_button'])->name('approve.data.depthead');
    Route::post('/discipline/magang/approvalGm', [DisciplinePageController::class, 'approve_gm'])->name('approve.data.gm');

    // ──────────────────────────────────────────────
    // Discipline — All Employees (HR / Super-Admin view)
    // ──────────────────────────────────────────────
    Route::get('/all/discipline', [DisciplinePageController::class, 'allindex'])->name('alldiscipline.index');

    // ──────────────────────────────────────────────
    // Data Lock & Export (Yayasan)
    // ──────────────────────────────────────────────
    Route::post('/lock-data/discipline', [DisciplinePageController::class, 'lockdata'])->name('lock.data');
    Route::get('/firstimeexport/yayasan/discipline', [DisciplinePageController::class, 'exportYayasan'])->name('export.yayasan.first.time');
    Route::get('/export/yayasan-full/discipline', [DisciplinePageController::class, 'exportYayasanFull'])->name('export.yayasan.full');

    // Date input page → show department status for Jpayroll export
    Route::get('/exportyayasantodateinput', [DisciplinePageController::class, 'dateExport'])->name('exportyayasan.dateinput');
    // Show Jpayroll export page with dept status
    Route::get('/exportyayasan', [DisciplinePageController::class, 'exportYayasanJpayroll'])->name('export.yayasan.jpayroll');
    // Get department approval status (AJAX/JSON)
    Route::get('/exportyayasan/summary', [DisciplinePageController::class, 'getDepartmentStatusYayasan'])->name('exportyayasan.summary');
    // Execute the actual Jpayroll Excel download
    Route::post('/exportyayasan/download', [DisciplinePageController::class, 'exportYayasanJpayrollFunction'])->name('exportyayasan.download');

    // ──────────────────────────────────────────────
    // Individual Yearly Evaluation Format Pages
    // (moved from legacy.php — belong to P&E domain)
    // ──────────────────────────────────────────────

    // Form pages (GET — show filter form by dept/year)
    Route::get('/format-evaluation-year-allin', [EvaluationDataController::class, 'evaluationformatrequestpageAllin'])->name('format.evaluation.year.allin');
    Route::get('/format-evaluation-year-yayasan', [EvaluationDataController::class, 'evaluationformatrequestpageYayasan'])->name('format.evaluation.year.yayasan');
    Route::get('/format-evaluation-year-magang', [EvaluationDataController::class, 'evaluationformatrequestpageMagang'])->name('format.evaluation.year.magang');
    Route::get('/format-evaluation-year-allinperpanjangan', [EvaluationDataController::class, 'evaluationformatrequestpageAllinPerpanjangan'])->name('format.evaluation.year.allinperpanjangan');

    // Result pages (POST — filtered by dept+year, renders the print/view)
    Route::post('/getformat/allin', [EvaluationDataController::class, 'getFormatYearallin'])->name('get.format.allin');
    Route::post('/getformat/yayasan', [EvaluationDataController::class, 'getFormatYearYayasan'])->name('get.format.yayasan');
    Route::post('/getformat/magang', [EvaluationDataController::class, 'getFormatYearmagang'])->name('get.format.magang');
    Route::post('/getformatallinperpanjangan', [EvaluationDataController::class, 'getFormatYearallinPerpanjangan'])->name('get.format.allinperpanjangan');

    // ──────────────────────────────────────────────
    // Evaluation Data Upload (Settings/Data Management)
    // ──────────────────────────────────────────────
    // Route::get('/evaluationindex', [DisciplinePageController::class, 'settingIndexEvaluation'])->name('indexevaluation');
    // Route::post('/updateevaluation', [DisciplinePageController::class, 'updateEvaluation'])->name('UpdateEvaluation');
    // Route::post('/deleteevaluation', [DisciplinePageController::class, 'deleteEvaluation'])->name('DeleteEvaluation');
    Route::get('/weeklyindex', [DisciplinePageController::class, 'settingIndexWeekly'])->name('weekly.index');
    Route::post('/updateweeklyeleration', [DisciplinePageController::class, 'updateWeeklyEvaluation'])->name('WeeklyUpdateEvaluation');
    Route::get('/indexdata', [DisciplinePageController::class, 'indexdata'])->name('indexdata');
    Route::post('/updatedatastep1', [DisciplinePageController::class, 'updatedatastep1'])->name('updatedata');
    Route::get('/step2disciplinedata', [DisciplinePageController::class, 'disciplineindexstep2view'])->name('step2.discipline');

    // ──────────────────────────────────────────────
    // Format Request Pages (download blank Excel templates)
    // ──────────────────────────────────────────────
    Route::get('/formatrequestmagang', [DisciplinePageController::class, 'formatrequestmagang'])->name('formatrequest.magang');
    Route::post('/getformat/magang/template', [DisciplinePageController::class, 'getFormat'])->name('get.format.template.magang');
    Route::get('/formatrequestyayasan', [DisciplinePageController::class, 'formatrequestyayasan'])->name('formatrequest.yayasan');
    Route::post('/getformat/yayasan/template', [DisciplinePageController::class, 'getFormat'])->name('get.format.template.yayasan');
    Route::get('/formatrequestallin', [DisciplinePageController::class, 'formatrequestallin'])->name('formatrequest.all');
    Route::post('/getformat/all/template', [DisciplinePageController::class, 'getFormat'])->name('get.format.template.allin');

    // ──────────────────────────────────────────────
    // Monthly Evaluation Report
    // ──────────────────────────────────────────────
    Route::get('/monthlyEvaluationReport', [EvaluationDataController::class, 'monthlyReport'])->name('monthlyEvaluationReport');
    Route::post('/monthlyEvaluationReport/view', [EvaluationDataController::class, 'showDetails'])->name('showdetail');
    Route::post('/monthlyEvaluationReport/table', [EvaluationDataController::class, 'showtable'])->name('showtable');

    Route::get('/evaluation/index', [EvaluationDataController::class, 'index']);
    Route::post('/processevaluationdata', [EvaluationDataController::class, 'update'])->name('UpdateEvaluation');
    Route::delete('/delete-evaluation', [EvaluationDataController::class, 'delete'])->name('DeleteEvaluation');
    
    Route::get('/weekly-evaluation/index', [EvaluationDataWeeklyController::class, 'weeklyIndex'])->name('weekly.evaluation.index');
    Route::post('/weeklyprocessevaluationdata', [EvaluationDataWeeklyController::class, 'updateWeekly'])->name('WeeklyUpdateEvaluation');
});
