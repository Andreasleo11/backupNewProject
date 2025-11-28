<?php

// app/Livewire/Verification/Index.php

namespace App\Livewire\Verification;

use App\Infrastructure\Persistence\Eloquent\Models\VerificationReport;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $status = 'all';

    public string $search = '';

    protected $queryString = ['status', 'search'];

    public function render()
    {
        $q = VerificationReport::query()
            ->when($this->status !== 'all', fn ($qq) => $qq->where('status', $this->status))
            ->when($this->search, fn ($qq) => $qq->where(function ($w) {
                $w->where('title', 'like', "%{$this->search}%")
                    ->orWhere('document_number', 'like', "%{$this->search}%");
            }))
            ->latest();

        return view('livewire.verification.index', [
            'reports' => $q->paginate(10),
        ]);
    }
}
