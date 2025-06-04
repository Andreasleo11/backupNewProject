<?php

namespace App\Livewire\InspectionForm;

use App\Traits\ClearsNestedSession;
use Livewire\Component;
use Illuminate\Support\Str;

class StepFirst extends Component
{
    use ClearsNestedSession;

    public $detail_inspection_report_document_number;
    public $appearance;
    public $weight;
    public $weight_uom;
    public $fitting_test;

    public $quarterKey;

    protected $rules = [
        'detail_inspection_report_document_number' => 'required|string',
        'appearance' => 'required|in:OK,NG',
        'weight' => 'required|numeric|min:0',
        'weight_uom' => 'required|string',
        'fitting_test' => 'nullable|string',
    ];

    public function updated($property)
    {
        $this->validateOnly($property);
    }

    public function mount()
    {
        $this->quarterKey = 'q' . session('stepDetailSaved.quarter');
        $saved = session("stepDetailSaved.first_inspections.{$this->quarterKey}", []);

        if ($saved) {
            foreach ($saved as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
        }
    }

    public function saveStep()
    {
        $this->validate();

        $data = [
            'detail_inspection_report_document_number' => $this->detail_inspection_report_document_number,
            'appearance' => $this->appearance,
            'weight' => $this->weight,
            'weight_uom' => $this->weight_uom,
            'fitting_test' => $this->fitting_test,
        ];

        session()->put("stepDetailSaved.first_inspections.{$this->quarterKey}", $data);
        $this->dispatch('toast', message: 'First saved successfully!');
    }

    public function resetStep()
    {
        $this->reset([
            'detail_inspection_report_document_number',
            'appearance',
            'weight',
            'weight_uom',
            'fitting_test',
        ]);

        $this->forgetNestedKey('stepDetailSaved.first_inspections', $this->quarterKey);
        $this->dispatch('toast', message: 'First step reset successfully!');
    }

    public function render()
    {
        return view('livewire.inspection-form.step-first');
    }
}
