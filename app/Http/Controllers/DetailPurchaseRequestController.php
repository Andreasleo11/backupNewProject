<?php

namespace App\Http\Controllers;

use App\Application\PurchaseRequest\DTOs\ItemApprovalActionDTO;
use App\Application\PurchaseRequest\UseCases\ApproveItem;
use App\Application\PurchaseRequest\UseCases\RejectItem;
use App\Domain\PurchaseRequest\Services\PurchaseRequestDetailService;
use App\Models\DetailPurchaseRequest;
use Illuminate\Http\Request;

class DetailPurchaseRequestController extends Controller
{
    public function __construct(
        private readonly PurchaseRequestDetailService $detailService,
        private readonly ApproveItem $approveItemUseCase,
        private readonly RejectItem $rejectItemUseCase
    ) {}

    public function approve(DetailPurchaseRequest $item)
    {
        try {
            $this->authorize('approve', $item);

            $this->approveItemUseCase->handle(new ItemApprovalActionDTO(
                itemId: $item->id,
                actorUser: auth()->user()
            ));

            return redirect()
                ->back()
                ->with(['success' => 'Item approved successfully!']);

        } catch (\DomainException $e) {
            return redirect()
                ->back()
                ->with(['error' => $e->getMessage()]);
        }
    }

    public function reject(DetailPurchaseRequest $item)
    {
        try {
            $this->authorize('reject', $item);

            $this->rejectItemUseCase->handle(new ItemApprovalActionDTO(
                itemId: $item->id,
                actorUser: auth()->user()
            ));

            return redirect()
                ->back()
                ->with(['success' => 'Item rejected successfully!']);

        } catch (\DomainException $e) {
            return redirect()
                ->back()
                ->with(['error' => $e->getMessage()]);
        }
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
