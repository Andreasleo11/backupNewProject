<?php

use App\Http\Controllers\MonthlyBudgetReportController;
use App\Http\Controllers\MonthlyBudgetReportDetailController;
use App\Http\Controllers\MonthlyBudgetSummaryReportController;
use App\Livewire\DepartmentExpenses;
use App\Livewire\MonthlyBudgetSummary\Index as MonthlyBudgetSummaryIndex;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Finance & Accounting Routes
|--------------------------------------------------------------------------
|
| Routes for managing monthly budget reports, budget summaries, and
| department expenses tracking.
|
| RECOMMENDED PERMISSIONS:
| - finance.view-budgets
| - finance.create-budgets
| - finance.approve-budgets
| - finance.view-expenses
|
| RECOMMENDED ROLES: admin, super-admin, finance, accounting, manager
|
*/

Route::middleware('auth')->group(function () {
    // Monthly Budget Reports
    Route::get('monthly-budget-reports', \App\Livewire\MonthlyBudget\Index::class)->name('monthly-budget-reports.index');
    Route::get('monthly-budget-reports/create', [MonthlyBudgetReportController::class, 'create'])->name('monthly-budget-reports.create');
    Route::post('monthly-budget-reports', [MonthlyBudgetReportController::class, 'store'])->name('monthly-budget-reports.store');
    Route::get('monthly-budget-reports/{id}/edit', [MonthlyBudgetReportController::class, 'edit'])->name('monthly-budget-reports.edit');
    Route::put('monthly-budget-reports/{id}', [MonthlyBudgetReportController::class, 'update'])->name('monthly-budget-reports.update');
    Route::get('monthly-budget-reports/{id}', [MonthlyBudgetReportController::class, 'show'])->name('monthly-budget-reports.show');
    Route::delete('monthly-budget-reports/{id}', [MonthlyBudgetReportController::class, 'destroy'])->name('monthly-budget-reports.delete');
    Route::put('monthly-budget-reports/{id}/reject', [MonthlyBudgetReportController::class, 'reject'])->name('monthly-budget-reports.reject');
    Route::post('monthly-budget-reports/{id}/submit', [MonthlyBudgetReportController::class, 'submit'])->name('monthly-budget-reports.submit');
    Route::post('monthly-budget-reports/{id}/approve', [MonthlyBudgetReportController::class, 'approve'])->name('monthly-budget-reports.approve');
    Route::put('monthly-budget-reports/{id}/cancel', [MonthlyBudgetReportController::class, 'cancel'])->name('monthly-budget-reports.cancel');

    Route::put('monthly-budget-reports/save-autograph/{id}', [MonthlyBudgetReportController::class, 'saveAutograph'])->name('monthly.budget.save.autograph');
    Route::post('monthly-budget-reports/download-monthly-excel-template', [MonthlyBudgetReportController::class, 'downloadExcelTemplate'])->name('monthly.budget.download.excel.template');

    // Monthly Budget Report Details
    Route::post('monthly-budget-report-detail', [MonthlyBudgetReportDetailController::class, 'store'])->name('monthly.budget.report.detail.store');
    Route::put('monthly-budget-report-detail/{id}', [MonthlyBudgetReportDetailController::class, 'update'])->name('monthly.budget.report.detail.update');
    Route::delete('monthly-budget-report-detail/{id}', [MonthlyBudgetReportDetailController::class, 'destroy'])->name('monthly.budget.report.detail.delete');

    // Monthly Budget Summaries
    Route::prefix('monthly-budget-summaries')->group(function () {
        Route::get('/', MonthlyBudgetSummaryIndex::class)->name('monthly-budget-summary-report.index');
        Route::get('/{id}', [MonthlyBudgetSummaryReportController::class, 'show'])->name('monthly.budget.summary.report.show');
        Route::post('/', [MonthlyBudgetSummaryReportController::class, 'store'])->name('monthly.budget.summary.report.store');
        Route::delete('/{id}', [MonthlyBudgetSummaryReportController::class, 'destroy'])->name('monthly.budget.summary.report.delete');
        Route::put('/save-autograph/{id}', [MonthlyBudgetSummaryReportController::class, 'saveAutograph'])->name('monthly.budget.summary.save.autograph');
        Route::put('/{id}/reject', [MonthlyBudgetSummaryReportController::class, 'reject'])->name('monthly.budget.summary.report.reject');
        Route::put('/{id}/cancel', [MonthlyBudgetSummaryReportController::class, 'cancel'])->name('monthly.budget.summary.report.cancel');
        Route::post('/{id}/refresh', [MonthlyBudgetSummaryReportController::class, 'refresh'])->name('monthly-budget-summary.refresh');
        Route::get('/{id}/export-pdf', [MonthlyBudgetSummaryReportController::class, 'exportToPdf'])->name('monthly.budget.summary.report.export-pdf');
    });

    // Department Expenses
    Route::get('/reports/department-expenses', DepartmentExpenses::class)
        ->middleware(['auth'])
        ->name('department-expenses.index');
});
