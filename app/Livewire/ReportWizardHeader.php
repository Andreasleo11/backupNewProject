<?php

namespace App\Livewire;

use App\Models\MasterDataRogCustomerName;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class ReportWizardHeader extends Component
{
    public $reportId;

    public $rec_date;

    public $verify_date;

    public $customer;

    public $invoice_no;

    public $customerSuggestions = [];

    public function mount($reportId = null)
    {
        $this->reportId = $reportId;
        $this->rec_date = session('report.rec_date') ?? now()->toDateString();
        $this->verify_date = session('report.verify_date') ?? now()->toDateString();
        $this->customer = session('report.customer') ?? '';
        $this->invoice_no = session('report.invoice_no') ?? '';
    }

    public function updatedCustomer($value)
    {
        $this->customerSuggestions = MasterDataRogCustomerName::where('name', 'like', "%{$value}%")
            ->pluck('name')
            ->toArray();
    }

    public function selectCustomer($name)
    {
        $this->customer = $name;
        $this->customerSuggestions = [];
    }

    public function saveReport()
    {
        $this->validate([
            'rec_date' => 'required|date',
            'verify_date' => 'required|date',
            'customer' => 'required|string',
            'invoice_no' => 'required|string',
        ]);

        Session::put('report.rec_date', $this->rec_date);
        Session::put('report.verify_date', $this->verify_date);
        Session::put('report.customer', $this->customer);
        Session::put('report.invoice_no', $this->invoice_no);

        $this->dispatch('stepCompleted')->to('report-wizard');
    }

    public function confirmCancel()
    {
        $this->dispatch('resetWizard')->to('report-wizard');

        return redirect()->route('qaqc.report.index');
    }

    public function render()
    {
        return view('livewire.report-wizard-header');
    }
}
