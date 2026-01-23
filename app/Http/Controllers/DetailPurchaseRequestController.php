<?php

namespace App\Http\Controllers;

use App\Domain\PurchaseOrder\Services\PurchaseOrderApprovalService;
use App\Domain\PurchaseOrder\Services\PurchaseOrderDetailService;
use Illuminate\Http\Request;

class DetailPurchaseRequestController extends Controller
{
    public function __construct(
        private readonly PurchaseOrderApprovalService $approvalService,
        private readonly PurchaseOrderDetailService $detailService
    ) {}

    public function approve($id, Request $request)
    {
        $this->approvalService->approveDetail($id, $request->type);

        return redirect()
            ->back()
            ->with(['success' => 'Detail approved successfully!']);
    }

    public function reject($id, Request $request)
    {
        $this->approvalService->rejectDetail($id, $request->type);

        return redirect()
            ->back()
            ->with(['success' => 'Detail rejected successfully!']);
    }

    public function update(Request $request)
    {
        if ($request->ajax()) {
            $success = $this->detailService->updateDetail(
                $request->pk,
                $request->name,
                $request->value
            );

            return response()->json([
                'success' => $success ? 'Detail updated successfully!' : 'Failed to update detail',
            ]);
        }
    }

    public function updateReceivedQuantity(Request $request, $id)
    {
        $this->detailService->updateReceivedQuantity($id, $request->received_quantity);

        return redirect()->back()->with('success', 'Update received successfully!');
    }

    public function updateAllReceivedQuantity($id)
    {
        $this->detailService->updateAllReceivedQuantity($id);

        return redirect()->back()->with('success', 'Update all received successfully!');
    }
}
