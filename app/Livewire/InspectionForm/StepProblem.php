<?php

namespace App\Livewire\InspectionForm;

use App\Traits\ClearsNestedSession;
use Livewire\Component;

class StepProblem extends Component
{
    use ClearsNestedSession;

    public $inspection_report_document_number;
    public $problems = [];

    public $quarterKey;

    protected $rules = [
        'problems.*.inspection_report_document_number' => 'required|string',
        'problems.*.type' => 'required|string|in:NO PROBLEM,QUALITY PROBLEM,MOLD PROBLEM,MACHINE PROBLEM,4M PROBLEM',
        'problems.*.time' => 'required|date_format:H:i',
        'problems.*.cycle_time' => 'required|integer|min:1',
        'problems.*.remark' => 'nullable|string',
    ];

    protected $messages = [
        'problems.*.inspection_report_document_number.required' => 'Inspection report document number is required.',
        'problems.*.inspection_report_document_number.string' => 'Inspection report document number must be a string.',
        'problems.*.type.required' => 'Type is required.',
        'problems.*.type.string' => 'Type must be a string.',
        'problems.*.type.in' => 'Type must be one of the following: NO PROBLEM, QUALITY PROBLEM, MOLD PROBLEM, MACHINE PROBLEM, 4M PROBLEM.',
        'problems.*.time.required' => 'Time is required.',
        'problems.*.time.date_format' => 'Time must be in H:i format.',
        'problems.*.cycle_time.required' => 'Cycle time is required.',
        'problems.*.cycle_time.integer' => 'Cycle time must be an integer.',
        'problems.*.cycle_time.min' => 'Cycle time must be as least 1 minute.',
        'problems.*.remark.string' => 'Remark must be a string.',
    ];

    public function mount($inspection_report_document_number = null)
    {
        $this->inspection_report_document_number = $inspection_report_document_number;
        $this->quarterKey = 'q' . session('stepDetailSaved.quarter');
        $this->problems = session("stepDetailSaved.problems.{$this->quarterKey}", []);

        if (empty($this->problems)) {
            $this->addProblem();
        } else {
            foreach ($this->problems as &$problem) {
                if (empty($problem['inspection_report_document_number'])) {
                    $problem['inspection_report_document_number'] = $this->inspection_report_document_number;
                }
            }
        }
    }

    public function addProblem()
    {
        $this->problems[] = [
            'inspection_report_document_number' => $this->inspection_report_document_number,
        ];
    }

    public function removeProblem($index)
    {
        unset($this->problems[$index]);
        $this->problems = array_values($this->problems);
    }

    public function updated($field)
    {
        $this->validateOnly($field);
    }

    public function saveStep()
    {
        $this->validate();

        session()->put("stepDetailSaved.problems.{$this->quarterKey}", $this->problems);
        $this->dispatch('toast', message: "Problems entries saved succesfully!");
    }

    public function resetStep()
    {
        $this->problems = [];
        $this->forgetNestedKey('stepDetailSaved.problems', $this->quarterKey);
        $this->addProblem();
        $this->resetValidation();
        $this->dispatch('toast', message: "Problems entries reset succesfully!");
    }

    public function render()
    {
        return view('livewire.inspection-form.step-problem');
    }
}
