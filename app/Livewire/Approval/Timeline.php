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
        // Normalize class name to morph alias if registered in relation morph map
        if (class_exists($approvableType)) {
            $approvableType = (new $approvableType)->getMorphClass();
        }

        $this->approvableType = $approvableType;
        $this->approvableId = $approvableId;

        // Support both morph alias and full class name in case of mixed database values
        $types = [$approvableType];
        $morphedClass = \Illuminate\Database\Eloquent\Relations\Relation::getMorphedModel($approvableType);
        if ($morphedClass) {
            $types[] = $morphedClass;
        }

        $this->request = ApprovalRequest::with(['steps', 'actions.causer'])
            ->whereIn('approvable_type', $types)
            ->where('approvable_id', $this->approvableId)
            ->first();
    }

    public function render()
    {
        return view('livewire.approval.timeline');
    }
}
