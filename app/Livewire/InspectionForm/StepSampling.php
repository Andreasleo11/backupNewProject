<?php

namespace App\Livewire\InspectionForm;

use App\Traits\ClearsNestedSession;
use Livewire\Component;

class StepSampling extends Component
{
    use ClearsNestedSession;

    public $samples = [];
    public $second_inspection_document_number;

    public $periodKey;

    protected $rules = [
        'samples.*.second_inspection_document_number' => 'required|string',
        'samples.*.quantity' => 'required|integer|min:1',
        'samples.*.box_label' => 'required|string',
        'samples.*.appearance' => 'required|string|in:OK,NG',
        'samples.*.ng_quantity' => 'required_if:samples.*.appearance,NG|nullable|integer|min:1',
        'samples.*.remarks' => 'required_if:samples.*.appearance,NG|nullable|string',
    ];

    protected $messages = [
        'samples.*.second_inspection_document_number.required' => 'The second inspection document number is required.',
        'samples.*.second_inspection_document_number.string' => 'The second inspection document number must be a string.',
        'samples.*.second_inspection_document_number.required' => 'The second inspection document number is required.',
        'samples.*.quantity.required' => 'The quantity is required.',
        'samples.*.quantity.integer' => 'The quantity must be an integer.',
        'samples.*.quantity.min' => 'The quantity must be at least 1.',
        'samples.*.box_label.required' => 'The box label is required.',
        'samples.*.box_label.string' => 'The box label must be a string.',
        'samples.*.appearance.required' => 'The appearance is required.',
        'samples.*.appearance.string' => 'The appearance must be a string.',
        'samples.*.appearance.in' => 'The appearance must be either OK or NG.',
        'samples.*.ng_quantity.required_if' => 'The NG quantity is require when appearance is NG.',
        'samples.*.ng_quantity.integer' => 'The NG quantity must be an integer.',
        'samples.*.ng_quantity.min' => 'The NG quantity must be at least 1.',
        'samples.*.remarks.required_if' => 'The remarks are required when appearance is NG.',
        'samples.*.remarks.string' => 'The remarks must be a string.',
    ];

    public function mount($second_inspection_document_number = null)
    {
        $this->second_inspection_document_number = $second_inspection_document_number;
        $this->periodKey = 'p' . session('stepDetailSaved.period');
        $this->samples = session("stepDetailSaved.samples.{$this->periodKey}", []);

        if (empty($this->samples)) {
            $this->addSample();
        } else {
            foreach ($this->samples as &$sample) {
                $sample['second_inspection_document_number'] = $this->second_inspection_document_number;
            }
        }
    }

    public function addSample()
    {
        $this->samples[] = [
            'second_inspection_document_number' => $this->second_inspection_document_number,
        ];
    }

    public function removeSample($index)
    {
        unset($this->samples[$index]);
        $this->samples = array_values($this->samples);
    }

    public function updated($field)
    {
        $this->validateOnly($field);
    }

    public function saveStep()
    {
        $this->validate();

        session()->put("stepDetailSaved.samples.{$this->periodKey}", $this->samples);
        $this->dispatch('toast', message: "Sampling data successfully!");
    }

    public function resetStep()
    {
        $this->samples = [];
        $this->resetValidation();
        $this->addSample();
        $this->forgetNestedKey('stepDetailSaved.samples', $this->periodKey);
        $this->dispatch('toast', message: "Sampling data reset successfully!");
    }

    public function render()
    {
        return view('livewire.inspection-form.step-sampling');
    }
}
