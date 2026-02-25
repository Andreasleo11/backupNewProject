<?php

use App\Http\Controllers\ApprovalSignatureController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\SignatureController;
use App\Livewire\Signature\CaptureSignature;
use App\Livewire\Signature\ManageSignatures;
use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\Approvals\RuleManager;

Route::middleware('auth')->group(function () {
    Route::prefix('admin')->group(function () {
        Route::name('admin.')->group(function () {
            // Dashboard can be viewable by anyone who has access to role feature
            Route::get('/access-overview', \App\Livewire\Admin\AccessOverviewDashboard::class)->name('access-overview.index');
            
            Route::view('/users', 'admin.users.index')
                ->name('users.index')
                ->middleware('can:user.view-any');
                
            Route::view('/roles', 'admin.roles.index')
                ->name('roles.index')
                ->middleware('can:role.view-any');

            Route::get('/approval-rules', RuleManager::class)
                ->name('approval-rules.index')
                ->middleware('can:approval.manage-rules');
                
            Route::view('/departments', 'admin.departments.index')
                ->name('departments.index')
                ->middleware('can:department.view-any');
                
            Route::view('/employees', 'admin.employees.index')
                ->name('employees.index')
                ->middleware('can:employee.view-any');
        });
    });

    // Signature Management
    Route::get('signatures/{id}', [SignatureController::class, 'show'])->name('signatures.show');
    Route::get('settings/signatures', ManageSignatures::class)->name('signatures.manage');
    Route::get('settings/signatures/capture', CaptureSignature::class)->name('signatures.capture');

    Route::get('/approval-steps/{step}/signature', [ApprovalSignatureController::class, 'show'])->name('approval-steps.signature');

    // File Operations
    Route::post('file/uploadEvaluation', [FileController::class, 'uploadEvaluation'])->name('file.upload.evaluation');
    Route::get('/get-files', [FileController::class, 'getFiles']);
});
