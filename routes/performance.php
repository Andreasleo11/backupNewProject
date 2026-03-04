<?php

use App\Http\Controllers\EvaluationController;
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

    // Batch approve (dept head — before {month}/{year})
    Route::post('/evaluation/approve-dept', [EvaluationController::class, 'approveDept'])->name('evaluation.approve-dept');

    // Final approve (HRD/GM — before {month}/{year})
    Route::post('/evaluation/approve-hrd', [EvaluationController::class, 'approveHrd'])->name('evaluation.approve-hrd');

    // Single record fetch for grade modal
    Route::get('/evaluation/{id}/data', [EvaluationController::class, 'show'])->name('evaluation.show');

    // Grade a single record (grader role)
    Route::put('/evaluation/{id}/grade', [EvaluationController::class, 'grade'])->name('evaluation.grade');

    // Reject a single record
    Route::post('/evaluation/{id}/reject', [EvaluationController::class, 'reject'])->name('evaluation.reject');

    // Parameterized period route (must come AFTER all literal /evaluation/... routes)
    Route::get('/evaluation/{month}/{year}', [EvaluationController::class, 'index'])->name('evaluation.period');

    // (Legacy import routes removed out of scope)
});

