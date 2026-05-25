<?php

use App\Http\Controllers\ApprovalSignatureController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\SignatureController;
use App\Livewire\Admin\Approvals\RuleManager;
use App\Livewire\Signature\CaptureSignature;
use App\Livewire\Signature\ManageSignatures;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::prefix('admin')->group(function () {
        Route::name('admin.')->group(function () {
            // Dashboard can be viewable by anyone who has access to role feature
            Route::get('/access-overview', \App\Livewire\Admin\AccessOverviewDashboard::class)->name('access-overview.index');

            Route::get('/users', \App\Livewire\Admin\Users\UserIndex::class)
                ->name('users.index')
                ->middleware('can:user.view-any');

            Route::get('/roles', \App\Livewire\Admin\Roles\RoleIndex::class)
                ->name('roles.index')
                ->middleware('can:role.view-any');

            Route::get('/permission-sync', \App\Livewire\Admin\PermissionSyncManager::class)
                ->name('permission-sync.index')
                ->middleware('can:system.admin');

            Route::get('/approval-rules', RuleManager::class)
                ->name('approval-rules.index')
                ->middleware('can:approval.manage-rules');

            Route::view('/departments', 'admin.departments.index')
                ->name('departments.index')
                ->middleware('can:department.view-any');

            Route::get('/employees', \App\Livewire\Admin\Employees\EmployeeIndex::class)
                ->name('employees.index')
                ->middleware('can:employee.view-any');

            // Evaluation Data Management (Admin)
            Route::get('/evaluation-data', [\App\Http\Controllers\Admin\EvaluationDataManagementController::class, 'index'])->name('evaluation-data.index');
            Route::post('/evaluation-data/upload', [\App\Http\Controllers\Admin\EvaluationDataManagementController::class, 'upload'])->name('evaluation-data.upload');
            Route::post('/evaluation-data/commit', [\App\Http\Controllers\Admin\EvaluationDataManagementController::class, 'commit'])->name('evaluation-data.commit');
            Route::delete('/evaluation-data/truncate', [\App\Http\Controllers\Admin\EvaluationDataManagementController::class, 'truncate'])->name('evaluation-data.truncate');
            Route::delete('/evaluation-data/{id}', [\App\Http\Controllers\Admin\EvaluationDataManagementController::class, 'destroy'])->name('evaluation-data.destroy');

            // Weekly Evaluation Data Management (Admin)
            Route::get('/evaluation-data-weekly', [\App\Http\Controllers\Admin\EvaluationDataWeeklyManagementController::class, 'index'])->name('evaluation-data-weekly.index');
            Route::post('/evaluation-data-weekly/upload', [\App\Http\Controllers\Admin\EvaluationDataWeeklyManagementController::class, 'upload'])->name('evaluation-data-weekly.upload');
            Route::post('/evaluation-data-weekly/commit', [\App\Http\Controllers\Admin\EvaluationDataWeeklyManagementController::class, 'commit'])->name('evaluation-data-weekly.commit');
            Route::delete('/evaluation-data-weekly/truncate', [\App\Http\Controllers\Admin\EvaluationDataWeeklyManagementController::class, 'truncate'])->name('evaluation-data-weekly.truncate');
            Route::delete('/evaluation-data-weekly/{id}', [\App\Http\Controllers\Admin\EvaluationDataWeeklyManagementController::class, 'destroy'])->name('evaluation-data-weekly.destroy');
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
