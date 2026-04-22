<?php

use App\Http\Controllers\MonthlyBudgetReportController;
use App\Http\Controllers\MonthlyBudgetSummaryController;
use App\Http\Controllers\MonthlyBudgetSummaryDetailController;
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
| */

Route::middleware('auth')->group(function () {
    // Monthly Budget Reports
    Route::get('monthly-budget-reports', \App\Livewire\MonthlyBudget\Index::class)->name('monthly-budget-reports.index');
    Route::get('monthly-budget-reports/create', \App\Livewire\MonthlyBudget\Form::class)->name('monthly-budget-reports.create');
    Route::get('monthly-budget-reports/{reportId}/edit', \App\Livewire\MonthlyBudget\Form::class)->name('monthly-budget-reports.edit');
    Route::get('monthly-budget-reports/{id}', [MonthlyBudgetReportController::class, 'show'])->name('monthly-budget-reports.show');
    Route::delete('monthly-budget-reports/{id}', [MonthlyBudgetReportController::class, 'destroy'])->name('monthly-budget-reports.delete');
    Route::put('monthly-budget-reports/{id}/reject', [MonthlyBudgetReportController::class, 'reject'])->name('monthly-budget-reports.reject');
    Route::post('monthly-budget-reports/{id}/return', [MonthlyBudgetReportController::class, 'returnForRevision'])->name('monthly-budget-reports.return');
    Route::post('monthly-budget-reports/{id}/submit', [MonthlyBudgetReportController::class, 'submit'])->name('monthly-budget-reports.submit');
    Route::post('monthly-budget-reports/{id}/approve', [MonthlyBudgetReportController::class, 'approve'])->name('monthly-budget-reports.approve');
    Route::put('monthly-budget-reports/{id}/cancel', [MonthlyBudgetReportController::class, 'cancel'])->name('monthly-budget-reports.cancel');

    Route::put('monthly-budget-reports/save-autograph/{id}', [MonthlyBudgetReportController::class, 'saveAutograph'])->name('monthly.budget.save.autograph');
    Route::post('monthly-budget-reports/download-monthly-excel-template', [MonthlyBudgetReportController::class, 'downloadExcelTemplate'])->name('monthly.budget.download.excel.template');

    // Monthly Budget Summaries
    Route::prefix('monthly-budget-summaries')->group(function () {
        Route::get('/', MonthlyBudgetSummaryIndex::class)->name('monthly-budget-summary.index');
        Route::get('/{id}', [MonthlyBudgetSummaryController::class, 'show'])->name('monthly-budget-summary.show');
        Route::post('/', [MonthlyBudgetSummaryController::class, 'store'])->name('monthly-budget-summary.store');
        Route::delete('/{id}', [MonthlyBudgetSummaryController::class, 'destroy'])->name('monthly-budget-summary.delete');
        Route::put('/save-autograph/{id}', [MonthlyBudgetSummaryController::class, 'saveAutograph'])->name('monthly-budget-summary.save-autograph');
        Route::put('/{id}/reject', [MonthlyBudgetSummaryController::class, 'reject'])->name('monthly-budget-summary.reject');
        Route::post('/{id}/return', [MonthlyBudgetSummaryController::class, 'returnForRevision'])->name('monthly-budget-summary.return');
        Route::post('/{id}/submit', [MonthlyBudgetSummaryController::class, 'submit'])->name('monthly-budget-summary.submit');
        Route::put('/{id}/cancel', [MonthlyBudgetSummaryController::class, 'cancel'])->name('monthly-budget-summary.cancel');
        Route::post('/{id}/refresh', [MonthlyBudgetSummaryController::class, 'refresh'])->name('monthly-budget-summary.refresh');
        Route::get('/{id}/export-pdf', [MonthlyBudgetSummaryController::class, 'exportToPdf'])->name('monthly-budget-summary.export-pdf');

        // Details
        Route::put('/details/{id}', [MonthlyBudgetSummaryDetailController::class, 'update'])->name('monthly-budget-summary-detail.update');
        Route::delete('/details/{id}', [MonthlyBudgetSummaryDetailController::class, 'destroy'])->name('monthly-budget-summary-detail.destroy');
    });

    // Department Expenses
    Route::get('/reports/department-expenses', DepartmentExpenses::class)
        ->middleware(['auth'])
        ->name('department-expenses.index');
});
