<?php

use App\Http\Controllers\admin\DepartmentController;
use App\Http\Controllers\admin\SpecificationController;
use App\Http\Controllers\SuperAdminHomeController;
use App\Livewire\Admin\Roles\RoleIndex;
use App\Livewire\Admin\Users\UserIndex;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/change-email/page', [SuperAdminHomeController::class, 'updateEmailpage'])->name('changeemail.page');
    Route::post('/change-email', [SuperAdminHomeController::class, 'updateEmail'])->name('email.update');
    Route::get('/get-email-settings/{feature}', [SuperAdminHomeController::class, 'getEmailSettings']);

    Route::get('/admin/home', fn() => view('admin.home'))->name('admin');

    Route::prefix('admin')->group(function () {
        Route::name('admin.')->group(function () {            
            Route::get('/access-overview', fn() => view('admin.access-overview'))->name('access-overview.index');
            Route::view('/users', 'admin.users.index')->name('users.index');
            Route::view('/roles/', 'admin.roles.index')->name('roles.index');
            Route::view('/departments', 'admin.departments.index')->name('departments.index');
            Route::view('/employees', 'admin.employees.index')->name('employees.index');
        });
    });
});
