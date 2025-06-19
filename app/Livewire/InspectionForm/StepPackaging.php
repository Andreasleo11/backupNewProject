<?php

namespace App\Livewire\InspectionForm;

use App\Traits\ClearsNestedSession;
use Livewire\Component;
use Illuminate\Support\Str;

class StepPackaging extends Component
{
    use ClearsNestedSession;

    public $second_inspection_document_number;
    public $packagings = [];

    public $periodKey;

    protected $rules = [
        'packagings.*.second_inspection_document_number' => 'required|string',
        'packagings.*.snp' => 'required|integer|min:1',
        'packagings.*.box_label' => 'required|string',
        'packagings.*.judgement' => 'required|in:OK,NG',
        'packagings.*.remarks' => 'nullable|string|required_if:packagings.*.judgement,NG',
    ];

    protected $messages = [
        'packagings.*.second_inspection_document_number.required' => 'The second inspection document number is required.',
        'packagings.*.second_inspection_document_number.string' =>  'The second inspection document number must be a string.',
        'packagings.*.snp.required' => 'The snp is required.',
        'packagings..*.snp.integer' => 'The snp must be an integer.',
        'packagings.*.snp.min' => 'The snp must be at least 1.',
        'packagings.*.box_label.required' => 'The box label is required.',
        'packagings.*.box_label.string' => 'The box label must be a string.',
        'packagings.*.judgement.required' => 'The judgement is required.',
        'packagings.*.judgement.in' => 'The judgement must be either OK or NG.',
        'packagings.*.remarks.required' => 'Remarks are require if the judgement is NG.',
        'packagings.*.remarks.string' => 'Remarks must be a string.',
    ];

    public function mount($second_inspection_document_number = null)
    {
        $this->second_inspection_document_number = $second_inspection_document_number;
        $this->periodKey = 'p' . session('stepDetailSaved.period');
        $this->packagings = session("stepDetailSaved.packagings.{$this->periodKey}", []);

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

    public function updated($property, $value)
    {
        $this->validateOnly($property);

        /* if the property that changed ends with ".judgement" … */
        if (Str::endsWith($property, '.judgement')) {

            // extract the row index: "packagings.3.judgement" → 3
            $index = (int) Str::between($property, 'packagings.', '.judgement');

            // when the new value is NOT "NG", blank out the remarks
            if ($value !== 'NG') {
                $this->packagings[$index]['remarks'] = '';
            }
            // $this->validate();
        }
    }

    public function saveStep()
    {
        $this->validate();

        session()->put("stepDetailSaved.packagings.{$this->periodKey}", $this->packagings);
        $this->dispatch('toast', message: "Packaging saved successfully!");
    }

    public function resetStep()
    {
        $this->packagings = [];
        $this->forgetNestedKey('stepDetailSaved.packagings', $this->periodKey);
        $this->resetValidation();
        $this->addPackaging();
        $this->dispatch('toast', message: "Packaging reset successfully!");
    }

    public function render()
    {
        return view('livewire.inspection-form.step-packaging');
    }
}
