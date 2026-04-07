<?php

namespace App\Livewire\PurchaseRequest;

use App\Application\PurchaseRequest\DTOs\ApprovalActionDTO;
use App\Application\PurchaseRequest\Queries\GetPurchaseRequestDetail;
use App\Application\PurchaseRequest\UseCases\ApprovePurchaseRequest as ApprovePR;
use App\Application\PurchaseRequest\UseCases\RejectPurchaseRequest as RejectPR;
use Livewire\Component;

class QuickView extends Component
{
    public int $prId;
    public string $rejectReason = '';
    public bool $showRejectInput = false;

    public function mount(int $prId)
    {
        $this->prId = $prId;
    }

    public function approve(ApprovePR $useCase)
    {
        try {
            $useCase->handle(new ApprovalActionDTO(
                purchaseRequestId: $this->prId,
                actorUserId: (int) auth()->id(),
                remarks: null,
                autoApproveItems: true,
            ));

            $this->dispatch('toast', message: 'Purchase Request approved successfully!', type: 'success');
            $this->dispatch('close-quick-view-modal');
            $this->dispatch('refresh-dashboard'); // optional, to refresh counts
        } catch (\Exception $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function toggleRejectInput()
    {
        $this->showRejectInput = !$this->showRejectInput;
        $this->rejectReason = '';
    }

    public function submitReject(RejectPR $useCase)
    {
        $this->validate(['rejectReason' => 'required|min:3']);

        try {
            $useCase->handle(new ApprovalActionDTO(
                purchaseRequestId: $this->prId,
                actorUserId: (int) auth()->id(),
                remarks: $this->rejectReason,
                autoApproveItems: true,
            ));

            $this->dispatch('toast', message: 'Purchase Request rejected.', type: 'success');
            $this->dispatch('close-quick-view-modal');
            $this->dispatch('refresh-dashboard');
        } catch (\Exception $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function render(GetPurchaseRequestDetail $query)
    {
        $vm = $query->handle($this->prId, auth()->user());
        
        // Authorization is handled via Policies in the PR module, similar to standard view
        // We catch Auth exception or just let Laravel handle it
        
        return view('livewire.purchase-request.quick-view', [
            'pr' => $vm->purchaseRequest,
            'filteredItemDetail' => $vm->filteredItemDetail,
            'totals' => $vm->totals,
            'flags' => $vm->flags,
        ]);
    }
}
