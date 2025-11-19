<?php

use App\Http\Controllers\admin\DepartmentController;
use App\Http\Controllers\admin\SpecificationController;
use App\Http\Controllers\admin\UserController;
use App\Http\Controllers\SuperAdminHomeController;
use Illuminate\Support\Facades\Route;

Route::middleware(['checkSessionId'])->group(function () {
    Route::get('/change-email/page', [SuperAdminHomeController::class, 'updateEmailpage'])->name('changeemail.page');
    Route::post('/change-email', [SuperAdminHomeController::class, 'updateEmail'])->name('email.update');
    Route::get('/get-email-settings/{feature}', [SuperAdminHomeController::class, 'getEmailSettings']);

    Route::get('/admin/home', fn() => view('admin.home'))->name('admin');

    Route::prefix('admin')->group(function () {
        Route::name('admin.')->group(function () {
            Route::get('/users', [UserController::class, 'index'])->name('users.index')->middleware('permission:users.view');
            Route::post('/users/store', [UserController::class, 'store'])->name('users.store');
            Route::put('/users/update/{id}', [UserController::class, 'update'])->name('users.update');
            Route::delete('/users/delete/{id}', [UserController::class, 'destroy'])->name('users.delete');
            Route::get('/users/reset/{id}', [UserController::class, 'resetPassword'])->name('users.reset.password');
            Route::delete('/users/delete-selected', [UserController::class, 'deleteSelected'])->name('users.deleteSelected');

            // new
            Route::get('/access-control', fn() => view('access-control.index'));

            Route::get('/departments', [DepartmentController::class, 'index'])->name('departments.index');
            Route::post('/departments/store', [DepartmentController::class, 'store'])->name('departments.store');
            Route::put('/departments/update/{id}', [DepartmentController::class, 'update'])->name('departments.update');
            Route::delete('/departments/delete/{id}', [DepartmentController::class, 'destroy'])->name('departments.delete');

            Route::get('/specifications', [SpecificationController::class, 'index'])->name('specifications.index');
            Route::post('/specifications/store', [SpecificationController::class, 'store'])->name('specifications.store');
            Route::put('/specifications/{id}/update', [SpecificationController::class, 'update'])->name('specifications.update');
            Route::delete('/specifications/{id}/delete', [SpecificationController::class, 'destroy'])->name('specifications.delete');
        });
    });
});
