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

    protected function rules(): array
    {
        $fifteen = function ($attribute, $value, $fail) {
            $minutes = \Carbon\Carbon::createFromFormat('H:i', $value)->minute;
            if ($minutes % 15 !== 0) {
                $fail('The ' . $attribute . ' must be in 15-minute increments (00, 15, 30, 45).');
            }
        };

        $rules = [
            'start_time' => ['required', 'date_format:H:i', $fifteen],
            'end_time'   => ['required', 'date_format:H:i', $fifteen],
        ];

        foreach ($this->measurements as $i => $row) {
            $rules["measurements.$i.inspection_report_document_number"] = 'required|string';
            $rules["measurements.$i.lower_limit"] = 'required|numeric';
            $rules["measurements.$i.upper_limit"] = [
                'required',
                'numeric',
                "gt:measurements.$i.lower_limit",
            ];
            $rules["measurements.$i.limit_uom"]   = 'required|string';
            $rules["measurements.$i.judgement"]   = 'required|in:OK,NG';
            $rules["measurements.$i.area"]        = 'required|string';
            $rules["measurements.$i.remarks"]     = 'nullable|string';

            // dynamic rule for actual_value
            $lower = $row['lower_limit'] ?? null;
            $upper = $row['upper_limit'] ?? null;

            $rules["measurements.$i.actual_value"] = is_numeric($lower) && is_numeric($upper)
                ? "required|numeric|between:$lower,$upper"
                : 'required|numeric';
        }

        return $rules;
    }


    protected $messages = [
        'measurements.*.inspection_report_document_number.required' => 'The inspection report document number is required.',
        'measurements.*.lower_limit.numeric' => 'The lower limit must be a number.',
        'measurements.*.upper_limit.numeric' => 'The upper limit must be a number.',
        'measurements.*.upper_limit.gt' => 'The upper limit must be greater than lower limit.',
        'measurements.*.actual_value.between' => 'The actual value must be between the lower and upper limits.',
        'measurements.*.limit_uom.string' => 'The limit unit of measure must be a string.',
        'measurements.*.actual_value.numeric' => 'The actual value must be a number.',
        'measurements.*.judgement.enum' => 'The judgement must be either OK or NG.',
        'measurements.*.area.string' => 'The area must be a string.',
        'measurements.*.remarks.string' => 'The area must be a string.',
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
            'actual_value' => '',
            'judgement' => '',
            'area' => '',
            'remarks' => '',
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
