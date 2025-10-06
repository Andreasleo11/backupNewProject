<?php

namespace App\Livewire\Admin\RequirementUploads;

use App\Models\RequirementUpload;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

class Review extends Component
{
    use WithPagination;

    public string $status = 'pending'; // pending|approved|rejected|all

    public ?string $q = null;          // search by department/requirement

    public ?int $uploadId = null;      // approve/reject target

    public ?string $review_notes = null;

    public function updating($field)
    {
        if (in_array($field, ['status', 'q'])) {
            $this->resetPage();
        }
    }

    public function approve(int $id): void
    {
        Gate::authorize('approve-requirements');

        $u = RequirementUpload::findOrFail($id);
        $u->status = 'approved';
        $u->review_notes = $this->review_notes;
        $u->save();

        $this->dispatch('upload:done');
        $this->reset(['uploadId', 'review_notes']);
        $this->dispatch('toast', type: 'success', message: 'Upload approved.');
    }

    public function reject(int $id): void
    {
        Gate::authorize('approve-requirements');

        $u = RequirementUpload::findOrFail($id);
        $u->status = 'rejected';
        $u->review_notes = $this->review_notes;
        $u->save();

        $this->dispatch('upload:done');
        $this->reset(['uploadId', 'review_notes']);
        $this->dispatch('toast', type: 'warning', message: 'Upload rejected.');
    }

    public function render()
    {
        $rows = RequirementUpload::with(['requirement'])
            ->when($this->status !== 'all', fn ($q) => $q->where('status', $this->status))
            ->when($this->q, function ($q) {
                $q->where('original_name', 'like', "%{$this->q}%")
                    ->orWhereHas('requirement', fn ($qq) => $qq->where('name', 'like', "%{$this->q}%")
                        ->orWhere('code', 'like', "%{$this->q}%"));
            })
            ->latest()
            ->paginate(10);

        return view('livewire.admin.requirement-uploads.review', compact('rows'));
    }
}
