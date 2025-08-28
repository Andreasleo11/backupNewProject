<?php

namespace App\Livewire\InspectionForm;

use App\Traits\ClearsNestedSession;
use Illuminate\Validation\Rule;
use Livewire\Component;

class StepProblem extends Component
{
    use ClearsNestedSession;

    public $inspection_report_document_number;
    public $problems = [];
    public $types = [];

    public $shift;
    public $operator;
    public $part_name;
    public $part_number;

    protected function rules(): array
    {
        return [
            "problems.*.inspection_report_document_number" => "required|string",
            "problems.*.type" => [
                "required",
                "string",
                Rule::in($this->types), // ← dynamic
            ],
            "problems.*.time" => "required|date_format:H:i",
            "problems.*.cycle_time" => "required|integer|min:1",
            "problems.*.remarks" => "nullable|string",
        ];
    }

    protected $messages = [
        "problems.*.inspection_report_document_number.required" =>
            "Inspection report document number is required.",
        "problems.*.inspection_report_document_number.string" =>
            "Inspection report document number must be a string.",
        "problems.*.type.required" => "Type is required.",
        "problems.*.type.string" => "Type must be a string.",
        "problems.*.type.in" =>
            "Type must be one of the following: NO PROBLEM, QUALITY PROBLEM, MOLD PROBLEM, MACHINE PROBLEM, 4M PROBLEM.",
        "problems.*.time.required" => "Time is required.",
        "problems.*.time.date_format" => "Time must be in H:i format.",
        "problems.*.cycle_time.required" => "Cycle time is required.",
        "problems.*.cycle_time.integer" => "Cycle time must be an integer.",
        "problems.*.cycle_time.min" => "Cycle time must be as least 1 minute.",
        "problems.*.remarks.string" => "Remarks must be a string.",
    ];

    public function mount($inspection_report_document_number = null)
    {
        $this->types = [
            "NO PROBLEM",
            "PART PROBLEM",
            "MOLD PROBLEM",
            "MACHINE PROBLEM",
            "TOOLS PROBLEM",
            "MATERIAL PROBLEM",
            "METHOD PROBLEM",
            "MAN PROBLEM",
        ];

        $this->inspection_report_document_number = $inspection_report_document_number;
        // dd(session('stepHeaderSaved'));
        $this->shift = session("stepHeaderSaved.shift", null);
        $this->operator = session("stepHeaderSaved.operator", null);
        $this->part_name = session("stepHeaderSaved.part_name", null);
        $this->part_number = session("stepHeaderSaved.part_number", null);
        $this->problems = session("stepProblemSaved", []);

        if (empty($this->problems)) {
            $this->addProblem();
        } else {
            foreach ($this->problems as &$problem) {
                if (empty($problem["inspection_report_document_number"])) {
                    $problem["inspection_report_document_number"] =
                        $this->inspection_report_document_number;
                }
            }
        }
    }

    public function addProblem()
    {
        $this->problems[] = [
            "inspection_report_document_number" => $this->inspection_report_document_number,
            "time" => "",
            "cycle_time" => "",
            "type" => "",
            "remarks" => "",
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

        session()->put("stepProblemSaved", $this->problems);
        $this->dispatch("toast", message: "Problems entries saved succesfully!");
    }

    public function resetStep()
    {
        $this->problems = [];
        session()->forget("stepProblemSaved");
        $this->addProblem();
        $this->resetValidation();
        $this->dispatch("toast", message: "Problems entries reset succesfully!");
    }

    public function render()
    {
        return view("livewire.inspection-form.step-problem");
    }
}
