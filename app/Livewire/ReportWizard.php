<?php

namespace App\Livewire;

use App\Models\Report;
use Livewire\Component;

class ReportWizard extends Component
{
    public $step = 1;

    public $reportId = null;

    protected $listeners = [
        'stepCompleted' => 'nextStep',
        'goBack' => 'prevStep',
        'resetWizard' => 'resetWizard',
    ];

    public function mount($reportId = null)
    {
        $this->reportId = $reportId;
        $this->step = session('report_wizard_step', 1);

        if ($reportId && ! session()->has('report')) {
            $this->loadReportIntoSession($reportId);
        }
    }

    protected function loadReportIntoSession($reportId)
    {
        $report = Report::with('details.defects')->findOrFail($reportId);

        session()->put('report', [
            'rec_date' => $report->rec_date,
            'verify_date' => $report->verify_date,
            'customer' => $report->customer,
            'invoice_no' => $report->invoice_no,
            'details' => $report->details
                ->map(function ($d) {
                    return [
                        'part_name' => $d->part_name,
                        'rec_quantity' => $d->rec_quantity,
                        'verify_quantity' => $d->verify_quantity,
                        'can_use' => $d->can_use,
                        'cant_use' => $d->cant_use,
                        'price' => $d->price,
                        'defects' => $d->defects
                            ->map(function ($defect) {
                                return [
                                    'category_id' => $defect->category_id,
                                    'quantity' => $defect->quantity,
                                    'remarks' => $defect->remarks,
                                    'is_customer' => $defect->is_customer,
                                    'is_daijo' => $defect->is_daijo,
                                    'is_supplier' => $defect->is_supplier,
                                ];
                            })
                            ->toArray(),
                    ];
                })
                ->toArray(),
            'activeDetailId' => null,
        ]);
    }

    public function nextStep()
    {
        $this->step++;
        session(['report_wizard_step' => $this->step]);
    }

    public function prevStep()
    {
        $this->step--;
        session(['report_wizard_step' => $this->step]);
    }

    public function resetWizard()
    {
        $this->step = 1;
        session()->forget('report_wizard_step');
        session()->forget('report');
    }

    public function render()
    {
        return view('livewire.report-wizard', [
            'step' => $this->step,
        ]);
    }
}
