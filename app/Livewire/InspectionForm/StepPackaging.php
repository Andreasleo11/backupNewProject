<?php

namespace App\Livewire\InspectionForm;

use App\Traits\ClearsNestedSession;
use Livewire\Component;

class StepPackaging extends Component
{
    use ClearsNestedSession;

    public $second_inspection_document_number;
    public $packagings = [];

    public $quarterKey;

    protected $rules = [
        'packagings.*.second_inspection_document_number' => 'required|string',
        'packagings.*.quantity' => 'required|integer|min:1',
        'packagings.*.box_label' => 'required|string',
        'packagings.*.judgement' => 'required|in:OK,NG',
    ];

    protected $messages = [
        'packagings.*.second_inspection_document_number.required' => 'The second inspection document number is required.',
        'packagings.*.second_inspection_document_number.string' =>  'The second inspection document number must be a string.',
        'packagings.*.quantity.required' => 'The quantity is required.',
        'packagings..*.quantity.integer' => 'The quantity must be an integer.',
        'packagings.*.quantity.min' => 'The quantity must be at least 1.',
        'packagings.*.box_label.required' => 'The box label is required.',
        'packagings.*.box_label.string' => 'The box label must be a string.',
        'packagings.*.judgement.required' => 'The judgement is required.',
        'packagings.*.judgement.in' => 'The judgement must be either OK or NG.',
    ];

    public function mount($second_inspection_document_number = null)
    {
        $this->second_inspection_document_number = $second_inspection_document_number;
        $this->quarterKey = 'q' . session('stepDetailSaved.quarter');
        $this->packagings = session("stepDetailSaved.packagings.{$this->quarterKey}", []);

        if (empty($this->packagings)) {
            $this->addPackaging();
        } else {
            foreach ($this->packagings as &$item) {
                if (empty($item['second_inspection_document_number'])) {
                    $item['second_inspection_document_number'] = $this->second_inspection_document_number;
                }
            }
        }
    }

    public function addPackaging()
    {
        $this->packagings[] = [
            'second_inspection_document_number' => $this->second_inspection_document_number,
        ];
    }

    public function removePackaging($index)
    {
        unset($this->packagings[$index]);
        $this->packagings = array_values($this->packagings);
    }

    public function updated($field)
    {
        $this->validateOnly($field);
    }

    public function saveStep()
    {
        $this->validate();

        session()->put("stepDetailSaved.packagings.{$this->quarterKey}", $this->packagings);
        $this->dispatch('toast', message: "Packaging saved successfully!");
    }

    public function resetStep()
    {
        $this->packagings = [];
        $this->forgetNestedKey('stepDetailSaved.packagings', $this->quarterKey);
        $this->resetValidation();
        $this->addPackaging();
        $this->dispatch('toast', message: "Packaging reset successfully!");
    }

    public function render()
    {
        return view('livewire.inspection-form.step-packaging');
    }
}
