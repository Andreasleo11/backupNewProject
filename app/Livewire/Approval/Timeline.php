<?php

namespace App\Livewire\Approval;

use App\Infrastructure\Persistence\Eloquent\Models\ApprovalRequest;
use Livewire\Component;

class Timeline extends Component
{
    public string $approvableType;

    public int $approvableId;

    public ?ApprovalRequest $request = null;

    public function mount(string $approvableType, int $approvableId)
    {
        $this->approvableType = $approvableType;
        $this->approvableId = $approvableId;

        $this->request = ApprovalRequest::with(['steps', 'actions.causer'])
            ->where('approvable_type', $this->approvableType)
            ->where('approvable_id', $this->approvableId)
            ->first();
    }

    public function render()
    {
        return view('livewire.approval.timeline');
    }
}
