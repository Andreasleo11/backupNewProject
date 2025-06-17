<?php

namespace App\Livewire\InspectionForm;

use App\Traits\ClearsNestedSession;
use Livewire\Component;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;


class StepSecond extends Component
{
    use ClearsNestedSession;

    public $detail_inspection_report_document_number;
    public $document_number;
    public $lot_size_quantity;
    public $skipLotSize = false;
    public $secondInspectionSaved = false;

    public $periodKey;

    protected function rules(): array
    {
        return [
            'detail_inspection_report_document_number' => 'required|string',
            'document_number' => 'required|string|unique:second_inspections,document_number',
            'lot_size_quantity' => [
                'nullable',
                'numeric',
                'min:1',
                Rule::requiredIf(fn() => !$this->skipLotSize),
            ],
            'skipLotSize' => 'boolean',
        ];
    }

    public function updated($field)
    {
        $this->validateOnly($field);
    }

    public function mount()
    {
        $this->periodKey = 'p' . session('stepDetailSaved.period');
        $saved = session("stepDetailSaved.second_inspections.{$this->periodKey}", []);

        if ($saved) {
            foreach ($saved as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
            $this->secondInspectionSaved = true;
        }

        if (!$this->document_number) {
            $this->document_number = 'SECOND-' . now()->format('Ymd-His') . '-' . strtoupper(Str::random(4));
        }
    }

    public function saveStep()
    {
        $this->validate();

        $data = [
            'detail_inspection_report_document_number' => $this->detail_inspection_report_document_number,
            'document_number' => $this->document_number,
            'lot_size_quantity' => $this->skipLotSize ? null : $this->lot_size_quantity,
            'skipLotSize'      => $this->skipLotSize,
        ];

        $this->secondInspectionSaved = true;

        session()->put("stepDetailSaved.second_inspections.{$this->periodKey}", $data);

        $this->dispatch('toast', message: 'Second inspection saved succesfully!');
    }

    public function resetStep()
    {
        $this->forgetNestedKey('stepDetailSaved.second_inspections', $this->periodKey);
        $this->reset(['lot_size_quantity', 'skipLotSize']);
        $this->secondInspectionSaved = false;
        $this->dispatch('toast', message: 'Second inspection reset successfully!');
    }

    public function render()
    {
        return view('livewire.inspection-form.step-second');
    }
}
