<?php

// app/Livewire/Verification/Index.php

namespace App\Livewire\Verification;

use App\Infrastructure\Persistence\Eloquent\Models\VerificationReport;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
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
            ->select('verification_reports.*')
            ->selectRaw('(SELECT SUM(verify_quantity * price) FROM verification_items WHERE verification_items.verification_report_id = verification_reports.id) as total_value')
            ->when(
                $this->status !== 'all',
                fn (Builder $query) => $query->where('status', $this->status)
            )
            ->when($this->search, function (Builder $query) {
                $s = "%{$this->search}%";
                $query->where(function ($q) use ($s) {
                    $q->where('document_number', 'like', $s)
                        ->orWhere('customer', 'like', $s)
                        ->orWhere('invoice_number', 'like', $s);
                });
            })
            ->latest();

        return view('livewire.verification.index', [
            'reports' => $q->paginate(10),
        ]);
    }
}
