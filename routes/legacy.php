<?php

use App\Http\Controllers\DisciplinePageController;
use App\Http\Controllers\EmployeeDashboardController;
use App\Http\Controllers\NotificationFeedController;
use App\Http\Controllers\SuperAdminHomeController;
use App\Http\Controllers\SyncProgressController;
use App\Http\Controllers\UserHomeController;
use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\RequirementUploads\Review as ReviewUploads;
use App\Livewire\Departments\Overview as DepartmentsOverview;

/*
|--------------------------------------------------------------------------
| Legacy & Utility Routes
|--------------------------------------------------------------------------
|
| Routes for legacy features, auto-login, utility functions, and
| miscellaneous features that don't fit into specific feature modules.
|
| RECOMMENDED PERMISSIONS:
| - N/A (mostly utility routes)
|
| RECOMMENDED ROLES: Various depending on specific route
|
*/

Route::middleware('auth')->group(function () {
    // User Home Routes
    Route::get('/user/home', [UserHomeController::class, 'index'])->name('user.home');

    // Employee Dashboard
    Route::get('/employees-dashboard', [EmployeeDashboardController::class, 'index'])->name('employees.dashboard');

    // Super Admin (legacy)
    Route::get('/change-password', [SuperAdminHomeController::class, 'index'])->name('changeemail.page');
    Route::post('/change-password', [SuperAdminHomeController::class, 'changePassword'])->name('changeemail');
    Route::get('/change-email/page', [SuperAdminHomeController::class, 'updateEmailpage'])->name('changeemail.page');
    Route::post('/change-email', [SuperAdminHomeController::class, 'updateEmail'])->name('email.update');
    Route::get('/get-email-settings/{feature}', [SuperAdminHomeController::class, 'getEmailSettings']);

    // Notifications
    Route::get('/notifications', [NotificationFeedController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/mark-as-read', [NotificationFeedController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::post('/notifications/mark-all-as-read', [NotificationFeedController::class, 'markAllAsRead'])->name('notifications.mark-all-as-read');

    // Sync Progress (utility)
    Route::get('/sync-progress/{job}/{tab}', [SyncProgressController::class, 'getProgress'])->name('sync.progress');

    // not decide where to put this route
    Route::get('/daily-reports/{employee_id}', [App\Http\Controllers\EmployeeDailyReportController::class, 'show'])->name('daily-reports.depthead.show');

    Route::get('/upload-daily-report', [App\Http\Controllers\EmployeeDailyReportController::class, 'showUploadForm'])->name('daily-report.form');
    Route::post('/daily-report/confirm-upload', [App\Http\Controllers\EmployeeDailyReportController::class, 'confirmUpload'])->name('daily-report.confirm-upload');
    Route::post('/upload-daily-report', [App\Http\Controllers\EmployeeDailyReportController::class, 'upload'])->name('daily-report.upload');
    Route::get('/admin/requirement-uploads', ReviewUploads::class)->name('admin.requirement-uploads');
    Route::get('/departments/overview', DepartmentsOverview::class)->name('departments.overview');
    Route::put('purchase-requests/{id}/po-number', [App\Http\Controllers\PurchaseRequestController::class, 'updatePoNumber'])->name('purchase-requests.po-number.update');
    Route::get('purchase-requests/{id}/export-pdf', [App\Http\Controllers\PurchaseRequestController::class, 'exportToPdf'])->name('purchase-requests.export-pdf');   
});

