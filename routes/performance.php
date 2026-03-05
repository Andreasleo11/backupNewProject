<?php

use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\EvaluationDataController;
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

    // (Routes pointing to obsolete DisciplinePageController and EvaluationDataController have been safely removed)

    // ──────────────────────────────────────────────
    // Unified Evaluation Page (new rework)
    // IMPORTANT: Specific literal routes MUST come before {month}/{year} wildcard.
    // ──────────────────────────────────────────────

    // Main page — defaults to current month
    Route::get('/evaluation', [EvaluationController::class, 'index'])->name('evaluation.index');

    // DataTable AJAX data per type tab (before {month}/{year} to avoid wildcard capture)
    Route::get('/evaluation/data/regular', [EvaluationController::class, 'dataRegular'])->name('evaluation.data.regular');
    Route::get('/evaluation/data/yayasan', [EvaluationController::class, 'dataYayasan'])->name('evaluation.data.yayasan');
    Route::get('/evaluation/data/magang',  [EvaluationController::class, 'dataMagang'])->name('evaluation.data.magang');

    // Status summary chips (AJAX — before {month}/{year})
    Route::get('/evaluation/summary', [EvaluationController::class, 'summary'])->name('evaluation.summary');

    // Export Excel
    Route::get('/evaluation/export', [EvaluationController::class, 'export'])->name('evaluation.export');

    // Bulk import Excel (Regular — grader submits filled template)
    Route::post('/evaluation/import', [EvaluationController::class, 'import'])->name('evaluation.import');

    // Batch approve (dept head — before {month}/{year})
    Route::post('/evaluation/approve-dept', [EvaluationController::class, 'approveDept'])->name('evaluation.approve-dept');

    // Final approve (HRD/GM — before {month}/{year})
    Route::post('/evaluation/approve-hrd', [EvaluationController::class, 'approveHrd'])->name('evaluation.approve-hrd');

    // Single record fetch for grade modal (Legacy ID-based)
    Route::get('/evaluation/{id}/data', [EvaluationController::class, 'show'])->name('evaluation.show');

    // Single record fetch for grade modal (New NIK-based)
    Route::get('/evaluation/data-by-nik/{nik}/{month}/{year}', [EvaluationController::class, 'showByNik'])->name('evaluation.show.nik');

    // Grade a single record (Legacy ID-based)
    Route::put('/evaluation/{id}/grade', [EvaluationController::class, 'grade'])->name('evaluation.grade');

    // Grade a single record (New NIK-based)
    Route::put('/evaluation/grade-by-nik/{nik}/{month}/{year}', [EvaluationController::class, 'gradeByNik'])->name('evaluation.grade.nik');

    // Reject a single record
    Route::post('/evaluation/{id}/reject', [EvaluationController::class, 'reject'])->name('evaluation.reject');

    // Parameterized period route (must come AFTER all literal /evaluation/... routes)
    Route::get('/evaluation/{month}/{year}', [EvaluationController::class, 'index'])->name('evaluation.period');

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

    // Export Yayasan JPayroll
    Route::get('/exportyayasantodateinput', [EvaluationDataController::class, 'dateExport'])->name('exportyayasan.dateinput');
    Route::get('/exportyayasan/summary', [EvaluationDataController::class, 'getDepartmentStatusYayasan'])->name('exportyayasan.summary');
    Route::get('/exportyayasan', [EvaluationDataController::class, 'exportYayasanJpayroll'])->name('export.yayasan.jpayroll');
    Route::post('/exportyayasan/download', [EvaluationDataController::class, 'exportYayasanJpayrollFunction'])->name('exportyayasan.download');
});

