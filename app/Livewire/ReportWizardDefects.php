<?php

namespace App\Livewire;

use App\Models\DefectCategory;
use Livewire\Component;
use Illuminate\Support\Facades\Session;

class ReportWizardDefects extends Component
{
    public $defect;
    public $activeDetailId = null;
    public $existingDetails = [];
    public $categories = [];
    public $defectSource = '';

    public function mount()
    {
        $this->refreshExistingDetails();
        $this->categories = DefectCategory::pluck('name', 'id')->toArray();
        $this->defect = [
            'category_id' => '',
            'quantity' => '',
            'remarks' => '',
        ];

        $this->activeDetailId = Session::get('report.activeDetailId', null);
    }

    protected function refreshExistingDetails()
    {
        $this->existingDetails = collect(Session::get('report.details', []))
            ->map(function ($item, $i) {
                $item['id'] = $i;
                $item['defects'] = $item['defects'] ?? [];
                return (object) $item;
            });
    }

    public function setActiveDetail($id)
    {
        $this->activeDetailId = $id;
        Session::put('report.activeDetailId', $id);
    }

    public function removeDefectFromSession($key)
    {
        $report = Session::get('report', []);
        if (isset($report['details'][$this->activeDetailId]['defects'][$key])) {
            unset($report['details'][$this->activeDetailId]['defects'][$key]);
            $report['details'][$this->activeDetailId]['defects'] = array_values($report['details'][$this->activeDetailId]['defects']); // reindex
            Session::put('report', $report);
        }
        $this->refreshExistingDetails();
    }

    public function saveDefect()
    {
        $this->validate(
            [
                'defectSource' => 'required|in:customer,daijo,supplier',
                'defect.category_id' => 'required|integer',
                'defect.quantity' => 'required|integer|min:1',
                'defect.remarks' => 'nullable|string',
            ],
            [
                'defect.category_id.required' => 'Please select a defect category.',
                'defect.category_id.integer' => 'Invalid category selected.',
                'defect.quantity.required' => 'Quantity is required.',
                'defect.quantity.integer' => 'Quantity must be a number.',
                'defect.quantity.min' => 'Quantity must be at least 1.',
                'defect.remarks.string' => 'Remarks must be text.',
            ],
        );

        $defectWithSource = array_merge($this->defect, [
            'is_customer' => $this->defectSource === 'customer' ? 1 : 0,
            'is_daijo' => $this->defectSource === 'daijo' ? 1 : 0,
            'is_supplier' => $this->defectSource === 'supplier' ? 1 : 0,
        ]);

        $report = Session::get('report', []);
        $report['details'][$this->activeDetailId]['defects'][] = $defectWithSource;
        Session::put('report', $report);

        $this->defect = ['category_id' => '', 'quantity' => '', 'remarks' => ''];
        $this->defectSource = '';
        $this->refreshExistingDetails();
        $this->dispatch('defect-added');
    }

    public function nextStep()
    {
        $this->dispatch('stepCompleted')->to('report-wizard');
    }

    public function goBack()
    {
        $this->dispatch('goBack')->to('report-wizard');
        Session::put('report.activeDetailId', null);
    }

    public function render()
    {
        return view('livewire.report-wizard-defects');
    }
}
