<?php

use App\Http\Controllers\SuperAdminHomeController;
use App\Livewire\Admin\Approvals\RuleManager;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:super-admin'])->group(function () {
    Route::get('/change-email/page', [SuperAdminHomeController::class, 'updateEmailpage'])->name('changeemail.page');
    Route::post('/change-email', [SuperAdminHomeController::class, 'updateEmail'])->name('email.update');
    Route::get('/get-email-settings/{feature}', [SuperAdminHomeController::class, 'getEmailSettings']);

    Route::prefix('admin')->group(function () {
        Route::name('admin.')->group(function () {
            Route::get('/access-overview', \App\Livewire\Admin\AccessOverviewDashboard::class)->name('access-overview.index');
            Route::view('/users', 'admin.users.index')->name('users.index');
            Route::view('/roles', 'admin.roles.index')->name('roles.index');
            Route::view('/departments', 'admin.departments.index')->name('departments.index');
            Route::view('/employees', 'admin.employees.index')->name('employees.index');

            Route::get('/approval-rules', RuleManager::class)->name('approval-rules.index');
        });
    });
});
