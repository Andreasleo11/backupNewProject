<?php

namespace App\Livewire\InspectionForm;

use App\Traits\ClearsNestedSession;
use Livewire\Component;

class StepQuantity extends Component
{
    use ClearsNestedSession;

    public $inspection_report_document_number;
    public $output_quantity;
    public $pass_quantity;
    public $reject_quantity;
    public $sampling_quantity;
    public $reject_rate = 0;

    public $quarterKey;

    protected $rules = [
        'inspection_report_document_number' => 'required|string',
        'output_quantity'  => 'required|integer|min:0',
        'pass_quantity'    => 'required|integer|min:0|lte:output_quantity',
        'reject_quantity'  => 'required|integer|min:0|lte:output_quantity',
        'sampling_quantity' => 'required|integer|min:1|lte:output_quantity',
        'reject_rate'      => 'required|numeric|between:0,100',
    ];

    public function mount()
    {
        $this->quarterKey = 'q' . session('stepDetailSaved.quarter');
        $saved = session("stepDetailSaved.quantities.{$this->quarterKey}", []);

        if ($saved) {
            foreach ($saved as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
        }
    }

    public function updated($property)
    {
        $this->validateOnly($property);

        /** 
         *  Enforce pass ≤ output and auto-adjust reject qty
         */
        if (in_array($property, ['output_quantity', 'pass_quantity'])) {

            if (
                is_numeric($this->output_quantity) &&
                is_numeric($this->pass_quantity)
            ) {
                // Clamp pass_quantity to output_quantity
                if ($this->pass_quantity > $this->output_quantity) {
                    $this->pass_quantity = $this->output_quantity;
                }

                // If everything passed, zero out rejects automatically
                if ($this->pass_quantity == $this->output_quantity) {
                    $this->reject_quantity = 0;
                }
            }
        }

        // Make sure reject_quantity never exceeds the remainder
        if ($property === 'reject_quantity' && is_numeric($this->reject_quantity)) {
            $maxReject = max(0, (int)$this->output_quantity - (int)$this->pass_quantity);
            if ($this->reject_quantity > $maxReject) {
                $this->reject_quantity = $maxReject;
            }
        }

        /**
         * Re-calculate reject rate whenever the relevant numbers change
         */
        if (
            is_numeric($this->reject_quantity) &&
            is_numeric($this->sampling_quantity) &&
            $this->sampling_quantity > 0
        ) {
            $this->reject_rate = round(
                ($this->reject_quantity / $this->sampling_quantity) * 100,
                2
            );
        }
    }

    public function saveStep()
    {
        $this->validate();

        $data = [
            'inspection_report_document_number' => $this->inspection_report_document_number,
            'output_quantity' => $this->output_quantity,
            'pass_quantity' => $this->pass_quantity,
            'reject_quantity' => $this->reject_quantity,
            'sampling_quantity' => $this->sampling_quantity,
            'reject_rate' => $this->reject_rate,
        ];

        session(["stepDetailSaved.quantities.{$this->quarterKey}" => $data]);
        $this->dispatch('toast', message: "Quantity data saved successfully!");
    }

    public function resetStep()
    {
        $this->reset([
            'inspection_report_document_number',
            'output_quantity',
            'pass_quantity',
            'reject_quantity',
            'sampling_quantity',
            'reject_rate',
        ]);
        $this->forgetNestedKey('stepDetailSaved.quantities', $this->quarterKey);
        $this->resetValidation();
        $this->dispatch('toast', message: "Quantity data reset sucessfully!");
    }

    public function render()
    {
        return view('livewire.inspection-form.step-quantity');
    }
}
