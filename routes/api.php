<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\EvaluationApiController;
use App\Http\Controllers\Api\V1\PurchaseRequestController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    // Public routes
    Route::post('/auth/login', [AuthController::class, 'login']);

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        // Auth
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        // Purchase Requests
        Route::apiResource('purchase-requests', PurchaseRequestController::class);
        Route::post('/purchase-requests/{purchaseRequest}/approve', [PurchaseRequestController::class, 'approve']);
        Route::post('/purchase-requests/{purchaseRequest}/reject', [PurchaseRequestController::class, 'reject']);
        Route::get('/purchase-requests/{id}/history', [PurchaseRequestController::class, 'history']);

        // Discipline Evaluations
        Route::apiResource('evaluations', EvaluationApiController::class)->except(['destroy']);
        Route::post('/evaluations/approve/dept-head', [EvaluationApiController::class, 'approveDeptHead']);
        Route::post('/evaluations/approve/gm', [EvaluationApiController::class, 'approveGM']);
        Route::post('/evaluations/reject/dept-head', [EvaluationApiController::class, 'rejectDeptHead']);
        Route::get('/evaluations/export', [EvaluationApiController::class, 'export']);
    });
});
