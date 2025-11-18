<?php

use App\Http\Controllers\admin\DepartmentController;
use App\Http\Controllers\admin\UserController;
use App\Http\Controllers\admin\PermissionController;
use App\Http\Controllers\admin\SpecificationController;
use App\Http\Controllers\SuperAdminHomeController;
use App\Http\Controllers\admin\UserPermissionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['checkUserRole:1', 'checkSessionId'])->group(function () {
    Route::get('/change-email/page', [SuperAdminHomeController::class, 'updateEmailpage'])->name('changeemail.page');
    Route::post('/change-email', [SuperAdminHomeController::class, 'updateEmail'])->name('email.update');
    Route::get('/get-email-settings/{feature}', [SuperAdminHomeController::class, 'getEmailSettings']);

    Route::get('/superadmin/home', [SuperAdminHomeController::class, 'index'])->name('superadmin');

    Route::prefix('superadmin')->group(function () {
        Route::name('superadmin.')->group(function () {
            Route::get('/users', [UserController::class, 'index'])->name('users')->middleware('permission:get-users');
            Route::post('/users/store', [UserController::class, 'store'])->name('users.store')->middleware('permission:store-users');
            Route::put('/users/update/{id}', [UserController::class, 'update'])->name('users.update')->middleware('permission:update-users');
            Route::delete('/users/delete/{id}', [UserController::class, 'destroy'])->name('users.delete')->middleware('permission:delete-users');
            Route::get('/users/reset/{id}', [UserController::class, 'resetPassword'])->name('users.reset.password')->middleware('permission:reset-password-users');
            Route::delete('/users/delete-selected', [UserController::class, 'deleteSelected'])->name('users.deleteSelected')->middleware('permission:delete-selected-users');

            Route::get('/departments', [DepartmentController::class, 'index'])->name('departments')->middleware('permission:get-departments');
            Route::post('/departments/store', [DepartmentController::class, 'store'])->name('departments.store')->middleware('permission:store-departments');
            Route::put('/departments/update/{id}', [DepartmentController::class, 'update'])->name('departments.update')->middleware('permission:update-departments');
            Route::delete('/departments/delete/{id}', [DepartmentController::class, 'destroy'])->name('departments.delete')->middleware('permission:delete-departments');

            Route::get('/specifications', [SpecificationController::class, 'index'])->name('specifications')->middleware('permission:get-specifications');
            Route::post('/specifications/store', [SpecificationController::class, 'store'])->name('specifications.store')->middleware('permission:store-specifications');
            Route::put('/specifications/{id}/update', [SpecificationController::class, 'update'])->name('specifications.update')->middleware('permission:update-specifications');
            Route::delete('/specifications/{id}/delete', [SpecificationController::class, 'destroy'])->name('specifications.delete')->middleware('permission:delete-specifications');

            Route::get('/users-permissions', [UserPermissionController::class, 'index'])->name('users.permissions.index')->middleware('permission:get-users-permissions');
            Route::put('/users-permissions/{id}/update', [UserPermissionController::class, 'update'])->name('users.permissions.update')->middleware('permission:update-users-permissions');

            Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index')->middleware('permission:get-permissions');
            Route::post('/permissions/store', [PermissionController::class, 'store'])->name('permissions.store')->middleware('permission:store-permissions');
            Route::put('/permissions/{permission}', [PermissionController::class, 'update'])->name('permissions.update')->middleware('permission:update-permissions');
            Route::delete('/permissions/{permission}', [PermissionController::class, 'destroy'])->name('permissions.destroy')->middleware('permission:delete-permissions');
        });
    });
});
