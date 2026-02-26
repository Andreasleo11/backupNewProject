<?php

use App\Http\Controllers\EmployeeDashboardController;
use App\Http\Controllers\EvaluationDataController;
use App\Http\Controllers\NotificationFeedController;
use App\Http\Controllers\SuperAdminHomeController;
use App\Http\Controllers\SyncProgressController;
use App\Http\Controllers\UserHomeController;
use Illuminate\Support\Facades\Route;

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

    // Super Admin
    Route::get('/change-password', [SuperAdminHomeController::class, 'index'])->name('changeemail.page');
    Route::post('/change-password', [SuperAdminHomeController::class, 'changePassword'])->name('changeemail');

    // Notifications
    Route::get('/notifications', [NotificationFeedController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/mark-as-read', [NotificationFeedController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::post('/notifications/mark-all-as-read', [NotificationFeedController::class, 'markAllAsRead'])->name('notifications.mark-all-as-read');

    // Sync Progress (utility)
    Route::get('/sync-progress/{job}/{tab}', [SyncProgressController::class, 'getProgress'])->name('sync.progress');

    Route::get('/change-email/page', [SuperAdminHomeController::class, 'updateEmailpage'])->name('changeemail.page');
    Route::post('/change-email', [SuperAdminHomeController::class, 'updateEmail'])->name('email.update');
    Route::get('/get-email-settings/{feature}', [SuperAdminHomeController::class, 'getEmailSettings']);

    Route::get('/format-evaluation-year-allinperpanjangan', [
        EvaluationDataController::class,
        'evaluationformatrequestpageAllinPerpanjangan',
    ])->name('format.evaluation.year.allinperpanjangan');
    
    Route::post('/getformatallinperpanjangan', [EvaluationDataController::class, 'getFormatYearallinPerpanjangan'])->name(
        'get.format.allinperpanjangan',
    );
});
