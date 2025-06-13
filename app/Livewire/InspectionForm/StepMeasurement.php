<?php

namespace App\Livewire\InspectionForm;

use App\Traits\ClearsNestedSession;
use Carbon\Carbon;
use Livewire\Component;

class StepMeasurement extends Component
{
    use ClearsNestedSession;

    public $measurements = [];
    public $start_time;
    public $end_time;
    public $inspection_report_document_number;

    public $periodKey;

    protected function rules()
    {
        return  [
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'measurements.*.inspection_report_document_number' => 'required|string',
            'measurements.*.lower_limit' => 'required|numeric|min:0',
            'measurements.*.upper_limit' => 'required|numeric|min:0',
            'measurements.*.limit_uom' => 'required|string',
            'measurements.*.judgement' => 'required|in:OK,NG',
            'measurements.*.part' => 'required|string',
        ];
    }

    protected $messages = [
        'measurements.*.inspection_report_document_number.required' => 'The inspection report document number is required.',
        'measurements.*.lower_limit.numeric' => 'The lower limit must be a number.',
        'measurements.*.upper_limit.numeric' => 'The upper limit must be a number.',
        'measurements.*.limit_uom.string' => 'The limit unit of measure must be a string.',
        'measurements.*.judgement.enum' => 'The judgement must be either OK or NG.',
        'measurements.*.part.string' => 'The part must be a string.',
    ];

    public function mount($inspection_report_document_number = null)
    {
        $this->inspection_report_document_number = $inspection_report_document_number;
        $this->periodKey = 'p' . session('stepDetailSaved.period');
        $this->measurements = session("stepDetailSaved.measurements.{$this->periodKey}", []);

        if ($this->measurements) {
            foreach ($this->measurements as $key => $measurements) {
                if (property_exists($this, 'start_time') && property_exists($this, 'end_time')) {
                    $this->start_time = Carbon::parse($measurements['start_datetime'])->format('H:i');
                    $this->end_time = Carbon::parse($measurements['end_datetime'])->format('H:i');
                }
            }
        }

        // if (empty($this->measurements)) $this->addMeasurement();
    }

    public function addMeasurement()
    {
        $period = session('stepDetailSaved.period');
        // dd(session('stepDetailSaved'));
        $this->start_time = \Carbon\Carbon::parse(session('stepDetailSaved.details.' . 'p' . $period . '.start_datetime'))->format('H:i');
        $this->end_time = \Carbon\Carbon::parse(session('stepDetailSaved.details.' . 'p' . $period . '.end_datetime'))->format('H:i');

        $this->measurements[] = [
            'inspection_report_document_number' => $this->inspection_report_document_number,
            'lower_limit' => '',
            'upper_limit' => '',
            'limit_uom' => '',
            'judgement' => '',
            'part' => '',
        ];
    }

    public function removeMeasurement($index)
    {
        unset($this->measurements[$index]);
        $this->measurements = array_values($this->measurements);
    }

    public function updated($property)
    {
        $this->validateOnly($property);
    }

    public function saveStep()
    {
        $this->validate();

        foreach ($this->measurements as $index => $measurements) {
            $this->measurements[$index]['start_datetime'] = Carbon::parse($this->start_time)->format('Y-m-d H:i:s');
            $this->measurements[$index]['end_datetime'] = Carbon::parse($this->end_time)->format('Y-m-d H:i:s');
        }

        session()->put("stepDetailSaved.measurements.{$this->periodKey}", $this->measurements);
        $this->dispatch('toast', message: 'Measurements saved successfully!');
    }

    public function resetStep()
    {
        $this->measurements = [];
        $this->start_time = '';
        $this->end_time = '';
        $this->resetValidation();
        $this->forgetNestedKey('stepDetailSaved.measurements', $this->periodKey);
        $this->dispatch('toast', message: 'Measurements reset successfully!');
    }

    public function render()
    {
        return view('livewire.inspection-form.step-measurement');
    }
}
