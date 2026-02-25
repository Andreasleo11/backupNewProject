<?php

use App\Http\Controllers\ApprovalSignatureController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\SignatureController;
use App\Livewire\Signature\CaptureSignature;
use App\Livewire\Signature\ManageSignatures;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Administration Routes
|--------------------------------------------------------------------------
|
| Routes for managing approval rules, templates, signatures, and file operations.
| These routes handle administrative functions for the application.
|
| RECOMMENDED PERMISSIONS:
| - admin.manage-approvals
| - admin.manage-signatures
| - admin.manage-files
|
| RECOMMENDED ROLES: admin, super-admin
|
*/

Route::middleware('auth')->group(function () {
    // Signature Management
    Route::get('signatures/{id}', [SignatureController::class, 'show'])->name('signatures.show');
    Route::get('settings/signatures', ManageSignatures::class)->name('signatures.manage');
    Route::get('settings/signatures/capture', CaptureSignature::class)->name('signatures.capture');

    Route::get('/approval-steps/{step}/signature', [ApprovalSignatureController::class, 'show'])->name('approval-steps.signature');

    // File Operations
    Route::post('file/uploadEvaluation', [FileController::class, 'uploadEvaluation'])->name('file.upload.evaluation');
    Route::get('/get-files', [FileController::class, 'getFiles']);
});
