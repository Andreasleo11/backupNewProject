<?php

namespace App\Livewire\Invoice;

use App\Models\Invoice;
use Livewire\Component;
use Livewire\WithPagination;

class InvoiceIndex extends Component
{
    use WithPagination;

    public $search = '';

    public $perPage = 10;

    public $sortBy = 'created_at';

    public $sortDirection = 'desc';

    protected $queryString = [
        'search' => ['except' => ''],
        'sortBy' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function sortByColumn($column)
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->sortBy = 'created_at';
        $this->sortDirection = 'desc';
        $this->resetPage();
    }

    public function getInvoicesQuery()
    {
        $query = Invoice::query()
            ->with(['purchaseOrder', 'files']);

        if ($this->search) {
            $searchTerm = trim($this->search);
            if (strlen($searchTerm) > 0) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('invoice_number', 'like', '%' . $searchTerm . '%')
                        ->orWhereHas('purchaseOrder', function ($poQuery) use ($searchTerm) {
                            $poQuery->where('po_number', 'like', '%' . $searchTerm . '%')
                                ->orWhere('vendor_name', 'like', '%' . $searchTerm . '%');
                        });
                });
            }
        }

        // Optimized sorting
        $sortableColumns = [
            'invoice_number', 'invoice_date', 'payment_date', 'total', 'created_at',
        ];

        if (in_array($this->sortBy, $sortableColumns)) {
            $query->orderBy($this->sortBy, $this->sortDirection);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query;
    }

    public function getInvoicesProperty()
    {
        return $this->getInvoicesQuery()->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.invoice.invoice-index', [
            'invoices' => $this->invoices,
        ]);
    }
}
