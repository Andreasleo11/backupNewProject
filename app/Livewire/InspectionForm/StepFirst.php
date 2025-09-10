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
    public $remarks;

    public $periodKey;

    protected $rules = [
        "detail_inspection_report_document_number" => "required|string",
        "appearance" => "required|in:OK,NG",
        "weight" => "required|numeric|min:0",
        "weight_uom" => "required|string",
        "fitting_test" => "nullable|string",
        "remarks" => "required_if:appearance,NG|nullable|string",
    ];

    public function updated($property)
    {
        $this->validateOnly($property);
    }

    public function mount()
    {
        $this->periodKey = "p" . session("stepDetailSaved.period");
        $saved = session("stepDetailSaved.first_inspections.{$this->periodKey}", []);

        if ($saved) {
            foreach ($saved as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
        }
    }

    public function updatedAppearance($value): void
    {
        if ($value !== "NG") {
            $this->remarks = null; // wipe any previous text
        }
    }

    public function saveStep()
    {
        $this->validate();

        $data = [
            "detail_inspection_report_document_number" =>
                $this->detail_inspection_report_document_number,
            "appearance" => $this->appearance,
            "weight" => $this->weight,
            "weight_uom" => $this->weight_uom,
            "fitting_test" => $this->fitting_test,
            "remarks" => $this->remarks,
        ];

        session()->put("stepDetailSaved.first_inspections.{$this->periodKey}", $data);
        $this->dispatch("toast", message: "First saved successfully!");
    }

    public function resetStep()
    {
        $this->reset([
            "detail_inspection_report_document_number",
            "appearance",
            "weight",
            "weight_uom",
            "fitting_test",
            "remarks",
        ]);

        $this->forgetNestedKey("stepDetailSaved.first_inspections", $this->periodKey);
        $this->dispatch("toast", message: "First step reset successfully!");
    }

    public function render()
    {
        return view("livewire.inspection-form.step-first");
    }
}
