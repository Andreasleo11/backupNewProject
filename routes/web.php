<?php

use App\Http\Controllers\FileController;
use App\Http\Controllers\HomeController;
use App\Livewire\Admin\RequirementUploads\Review as ReviewUploads;
use App\Livewire\Auth\ChangePasswordPage;
use App\Livewire\Departments\Overview as DepartmentsOverview;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Root redirect - authenticated users go to home, guests go to login
Route::get('/', fn () => Auth::check() ? redirect()->intended('/home') : redirect()->intended(route('login')))->name('/');

// Laravel Authentication Routes
Auth::routes();

// Core Application Routes
Route::middleware('auth')->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::get('/account/security', ChangePasswordPage::class)->name('account.security');
});

// not decide where to put this route
Route::get('/daily-reports/{employee_id}', [App\Http\Controllers\EmployeeDailyReportController::class, 'show'])->name('daily-reports.depthead.show');

Route::get('/upload-daily-report', [App\Http\Controllers\EmployeeDailyReportController::class, 'showUploadForm'])->name('daily-report.form');
Route::post('/daily-report/confirm-upload', [App\Http\Controllers\EmployeeDailyReportController::class, 'confirmUpload'])->name('daily-report.confirm-upload');
Route::post('/upload-daily-report', [App\Http\Controllers\EmployeeDailyReportController::class, 'upload'])->name('daily-report.upload');

Route::get('/format-evaluation-year-yayasan', [App\Http\Controllers\EvaluationDataController::class, 'evaluationformatrequestpageYayasan'])->name('format.evaluation.year.yayasan');
Route::get('/format-evaluation-year-allin', [App\Http\Controllers\EvaluationDataController::class, 'evaluationformatrequestpageAllin'])->name('format.evaluation.year.allin');
Route::get('/format-evaluation-year-magang', [App\Http\Controllers\EvaluationDataController::class, 'evaluationformatrequestpageMagang'])->name('format.evaluation.year.magang');
Route::get('/admin/requirement-uploads', ReviewUploads::class)->name('admin.requirement-uploads');
Route::get('/departments/overview', DepartmentsOverview::class)->name('departments.overview');

Route::put('purchase-requests/{id}/po-number', [App\Http\Controllers\PurchaseRequestController::class, 'updatePoNumber'])->name('purchase-requests.po-number.update');
Route::get('purchase-requests/{id}/export-pdf', [App\Http\Controllers\PurchaseRequestController::class, 'exportToPdf'])->name('purchase-requests.export-pdf');

// DEPRECATED: Old GET routes - kept temporarily for backward compatibility
// TODO: Remove after frontend migration complete
Route::get('purchase-requests/items/{id}/approve', function () {
    abort(405, 'Please use POST method for item approval');
})->name('purchase-requests.items.approve.deprecated');

Route::get('purchase-requests/items/{id}/reject', function () {
    abort(405, 'Please use POST method for item rejection');
})->name('purchase-requests.items.reject.deprecated');

Route::post('file/upload', [FileController::class, 'upload'])->name('file.upload');
Route::delete('files/{id}', [FileController::class, 'destroy'])->name('file.destroy');

/*
|--------------------------------------------------------------------------
| Feature-Based Modular Routes
|--------------------------------------------------------------------------
|
| Routes are organized by business feature/domain in separate files.
| Each file contains routes related to a specific functional area.
|
*/

// Administration & Management
require __DIR__ . '/administration.php';

// Inventory & Assets
require __DIR__ . '/inventory.php';

// Quality Control
require __DIR__ . '/quality.php';

// Production
require __DIR__ . '/production.php';

// Procurement
require __DIR__ . '/procurement.php';

// Finance & Accounting
require __DIR__ . '/finance.php';

// Operations
require __DIR__ . '/operations.php';

// HR & Employee Management
require __DIR__ . '/hr.php';

// Performance & Evaluation
require __DIR__ . '/performance.php';

// Compliance & Documentation
require __DIR__ . '/compliance.php';

// Department-Specific Home Pages
require __DIR__ . '/departments.php';

// Master Data Management
require __DIR__ . '/master-data.php';

// Legacy & Utility Routes
require __DIR__ . '/legacy.php';

// Admin Routes (already modularized)
require __DIR__ . '/admin.php';
