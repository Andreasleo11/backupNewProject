<?php

namespace App\Livewire\PurchaseRequest;

use App\Application\PurchaseRequest\DTOs\ApprovalActionDTO;
use App\Application\PurchaseRequest\Queries\GetPurchaseRequestDetail;
use App\Application\PurchaseRequest\UseCases\ApprovePurchaseRequest as ApprovePR;
use App\Application\PurchaseRequest\UseCases\RejectPurchaseRequest as RejectPR;
use Livewire\Attributes\On;
use Livewire\Component;

class QuickView extends Component
{
    public ?int $prId = null;

    public string $rejectReason = '';

    public bool $showRejectInput = false;

    public bool $isLoading = false;

    public function mount(?int $prId = null)
    {
        $this->prId = $prId;
    }

    #[On('open-quick-view-modal')]
    public function loadPR($id)
    {
        // Handle both object and primitive passing from Alpine
        $this->prId = is_array($id) ? ($id['id'] ?? null) : $id;
        $this->showRejectInput = false;
        $this->rejectReason = '';
        $this->isLoading = true;

        // Dispatch browser event to open the drawer/modal if needed
        $this->dispatch('open-quick-view-drawer');
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
            $this->dispatch('refresh-index'); // refresh the main index table
        } catch (\Exception $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function toggleRejectInput()
    {
        $this->showRejectInput = ! $this->showRejectInput;
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
            $this->dispatch('refresh-index'); // refresh the main index table
        } catch (\Exception $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function render(GetPurchaseRequestDetail $query)
    {
        $viewData = [
            'pr' => null,
            'filteredItemDetail' => collect(),
            'totals' => [],
            'flags' => [],
            'isLoading' => $this->isLoading,
        ];

        if ($this->prId) {
            try {
                $vm = $query->handle($this->prId, auth()->user());
                $viewData = [
                    'pr' => $vm->purchaseRequest,
                    'filteredItemDetail' => $vm->filteredItemDetail,
                    'totals' => $vm->totals,
                    'flags' => $vm->flags,
                ];
            } catch (\Exception $e) {
                // Silently fail or log, template handles null pr
            }
        }

        $this->isLoading = false;

        return view('livewire.purchase-request.quick-view', $viewData);
    }
}
