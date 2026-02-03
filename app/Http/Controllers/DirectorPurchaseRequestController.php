<?php

namespace App\Http\Controllers;

use App\DataTables\DirectorPurchaseRequestDataTable;
use App\Domain\PurchaseRequest\Services\PurchaseRequestApprovalService;
use App\Domain\PurchaseRequest\Services\PurchaseRequestSignatureService;
use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DirectorPurchaseRequestController extends Controller
{
    public function __construct(
        private readonly PurchaseRequestApprovalService $approvalService
    ) {}

    public function index(DirectorPurchaseRequestDataTable $datatable)
    {
        return $datatable->render('director.purchaseRequest.index');
    }

    public function approveSelected(Request $request)
    {
        $ids = $request->input('ids', []);
        $username = Auth::user()->name;
        $imageUrl = $username . '.png';

        $result = $this->approvalService->batchApprove($ids, $username, $imageUrl);

        return response()->json([
            'message' => $result['message'] . ' (server)',
        ]);
    }

    public function rejectSelected(Request $request)
    {
        $ids = $request->input('ids', []);
        $rejectionReason = $request->input('rejection_reason');

        $result = $this->approvalService->batchReject($ids, $rejectionReason);

        return response()->json([
            'message' => $result['message'] . ' (server)',
        ]);
    }
}
