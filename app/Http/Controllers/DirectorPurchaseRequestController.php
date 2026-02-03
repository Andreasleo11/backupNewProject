<?php

namespace App\Http\Controllers;

use App\Application\PurchaseRequest\UseCases\BatchApprovePurchaseRequests;
use App\Application\PurchaseRequest\UseCases\BatchRejectPurchaseRequests;
use App\DataTables\DirectorPurchaseRequestDataTable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DirectorPurchaseRequestController extends Controller
{
    public function __construct(
        private readonly BatchApprovePurchaseRequests $batchApproveUseCase,
        private readonly BatchRejectPurchaseRequests $batchRejectUseCase
    ) {}

    public function index(DirectorPurchaseRequestDataTable $datatable)
    {
        return $datatable->render('director.purchaseRequest.index');
    }

    public function approveSelected(Request $request): JsonResponse
    {
        $ids = $request->input('ids', []);
        $userId = Auth::id();

        if (! $userId) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated.',
            ], 401);
        }

        $result = $this->batchApproveUseCase->handle($ids, $userId);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'approved' => $result['approved'],
            'failed' => $result['failed'],
            'errors' => $result['errors'],
        ]);
    }

    public function rejectSelected(Request $request): JsonResponse
    {
        $ids = $request->input('ids', []);
        $rejectionReason = $request->input('rejection_reason', '');
        $userId = Auth::id();

        if (! $userId) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated.',
            ], 401);
        }

        $result = $this->batchRejectUseCase->handle($ids, $userId, $rejectionReason);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'rejected' => $result['rejected'],
            'failed' => $result['failed'],
            'errors' => $result['errors'],
        ]);
    }
}
