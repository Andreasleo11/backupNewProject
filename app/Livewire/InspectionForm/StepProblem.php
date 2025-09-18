<?php

namespace App\Livewire\InspectionForm;

use App\Traits\ClearsNestedSession;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Illuminate\Support\Str;

class StepProblem extends Component
{
    use ClearsNestedSession;

    public $inspection_report_document_number;
    public $problems = [];
    public $types = [];

    public $shift;
    public $operator;
    public $inspector;
    public $part_name;
    public $part_number;

    public array $sessionSaved = [];
    public array $baselineByKey = [];
    public ?string $savedAt = null;
    public bool $isSaved = false;

    protected function rules(): array
    {
        return [
            "problems.*.inspection_report_document_number" => "required|string",
            "problems.*.type" => ["required", "string", Rule::in($this->types)],
            "problems.*.time" => "required|date_format:H:i",
            "problems.*.cycle_time" => "required|integer|min:1",
            "problems.*.remarks" => "required|string|max:255",
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
        "problems.*.remarks.required" => "Remarks is required.",
    ];

    protected function norm($v)
    {
        return $v === "" ? null : $v;
    }

    protected function ensureRowKeys(): void
    {
        foreach ($this->problems as $i => $row) {
            if (!isset($row["row_key"]) || !$row["row_key"]) {
                $this->problems[$i]["row_key"] = (string) Str::uuid();
            }
        }
    }

    protected function buildBaselineMap(): void
    {
        $this->baselineByKey = [];
        foreach ($this->sessionSaved as $row) {
            if (isset($row["row_key"])) {
                $this->baselineByKey[$row["row_key"]] = $row;
            }
        }
    }

    public function isRowFieldSaved(string $rowKey, string $field): bool
    {
        $cur = $this->norm(
            data_get(collect($this->problems)->firstWhere("row_key", $rowKey), $field),
        );
        $base = $this->norm(data_get($this->baselineByKey[$rowKey] ?? [], $field));
        return $cur !== null && $cur === $base;
    }

    public function mount($inspection_report_document_number = null)
    {
        $this->types = [
            "PART PROBLEM",
            "MOLD PROBLEM",
            "MACHINE PROBLEM",
            "TOOLS PROBLEM",
            "MATERIAL PROBLEM",
            "METHOD PROBLEM",
            "MAN PROBLEM",
        ];

        $this->inspection_report_document_number = $inspection_report_document_number;

        $this->shift = session("stepHeaderSaved.shift", null);
        $this->operator = session("stepHeaderSaved.operator", null);
        $this->inspector = session("stepHeaderSaved.inspector", null);
        $this->part_name = session("stepHeaderSaved.part_name", null);
        $this->part_number = session("stepHeaderSaved.part_number", null);

        $this->problems = session("stepProblemSaved", []);
        $this->sessionSaved = session("stepProblemSaved", []);
        $this->savedAt = session("stepProblemSaved_meta.savedAt");
        $this->isSaved = !empty($this->problems);

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

        $this->ensureRowKeys();
        if (!empty($this->sessionSaved)) {
            foreach ($this->sessionSaved as $i => $row) {
                if (!isset($row["row_key"]) || !$row["row_key"]) {
                    $this->sessionSaved[$i]["row_key"] = (string) Str::uuid();
                }
            }
        }
        $this->buildBaselineMap();
    }

    public function addProblem()
    {
        $this->problems[] = [
            "row_key" => (string) Str::uuid(),
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
        $this->savedAt = now()->toIso8601String();
        session()->put("stepProblemSaved_meta.savedAt", $this->savedAt);

        $this->sessionSaved = $this->problems;
        $this->buildBaselineMap();
        $this->isSaved = true;

        $this->dispatch("problemsSaved", savedAt: $this->savedAt);
        $this->dispatch("toast", message: "Problems entries saved succesfully!");
    }

    public function resetStep()
    {
        $this->problems = [];
        session()->forget("stepProblemSaved");
        session()->forget("stepProblemSaved_meta");

        $this->sessionSaved = [];
        $this->baselineByKey = [];
        $this->savedAt = null;
        $this->isSaved = false;

        $this->addProblem();
        $this->resetValidation();

        $this->dispatch("problemsReset");
        $this->dispatch("toast", message: "Problems entries reset succesfully!");
    }

    public function render()
    {
        return view("livewire.inspection-form.step-problem");
    }
}
