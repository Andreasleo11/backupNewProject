<?php

namespace App\Livewire\InspectionForm;

use App\Models\Upload;
use App\Traits\ClearsNestedSession;
use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StepDimension extends Component
{
    use ClearsNestedSession;

    public $dimensions = [];
    public $start_time;
    public $end_time;
    public $inspection_report_document_number;
    public $uploads;

    public $periodKey;

    protected function rules(): array
    {
        $fifteen = function ($attribute, $value, $fail) {
            $minutes = \Carbon\Carbon::createFromFormat("H:i", $value)->minute;
            if ($minutes % 15 !== 0) {
                $fail("The " . $attribute . " must be in 15-minute increments (00, 15, 30, 45).");
            }
        };

        $rules = [
            "start_time" => ["required", "date_format:H:i", $fifteen],
            "end_time" => ["required", "date_format:H:i", $fifteen],
        ];

        foreach ($this->dimensions as $i => $row) {
            $rules["dimensions.$i.inspection_report_document_number"] = "required|string";
            $rules["dimensions.$i.lower_limit"] = "required|numeric";
            $rules["dimensions.$i.upper_limit"] = [
                "required",
                "numeric",
                "gt:dimensions.$i.lower_limit",
            ];
            $rules["dimensions.$i.limit_uom"] = "required|string";
            $rules["dimensions.$i.judgement"] = "required|in:OK,NG";
            $rules["dimensions.$i.area"] = "required|string";

            /* ❶ remarks is required only when judgement = NG */
            $rules["dimensions.$i.remarks"] = [
                "nullable",
                "string",
                Rule::requiredIf(($row["judgement"] ?? "") === "NG"),
            ];

            // dynamic rule for actual_value
            $lower = $row["lower_limit"] ?? null;
            $upper = $row["upper_limit"] ?? null;

            $actualRules = ["required", "numeric"];

            if (($row["judgement"] ?? "") === "OK" && is_numeric($lower) && is_numeric($upper)) {
                // only when OK: must lie between lower & upper
                $actualRules[] = "between:$lower,$upper";
            }

            $rules["dimensions.$i.actual_value"] = $actualRules;
        }

        return $rules;
    }

    protected $messages = [
        "dimensions.*.inspection_report_document_number.required" =>
            "The inspection report document number is required.",
        "dimensions.*.limit_uom.required" => "The limit unit of measure is required.",
        "dimensions.*.lower_limit.required" => "The lower limit is required.",
        "dimensions.*.lower_limit.numeric" => "The lower limit must be a number.",
        "dimensions.*.upper_limit.required" => "The upper limit is required.",
        "dimensions.*.upper_limit.numeric" => "The upper limit must be a number.",
        "dimensions.*.upper_limit.gt" => "The upper limit must be greater than lower limit.",
        "dimensions.*.actual_value.between" =>
            "The actual value must be between the lower and upper limits.",
        "dimensions.*.limit_uom.string" => "The limit unit of measure must be a string.",
        "dimensions.*.actual_value.numeric" => "The actual value must be a number.",
        "dimensions.*.judgement.enum" => "The judgement must be either OK or NG.",
        "dimensions.*.area.required" => "The area is required.",
        "dimensions.*.area.string" => "The area must be a string.",
        "dimensions.*.remarks.string" => "The area must be a string.",
        "dimensions.*.remarks.required" => "Remarks are required when judgement is NG.",
        "start_time.required" => "The start time is required.",
        "start_time.date_format" => "The start time must be in the format HH:mm.",
        "start_time.fifteen" => "The start time must be in 15-minute",
        "end_time.required" => "The end time is required.",
        "end_time.date_format" => "The end time must be in the format HH:mm.",
        "end_time.fifteen" => "The end time must be in 15-minute",
        "dimensions.*.actual_value.required" => "The actual value is required.",
        "dimensions.*.actual_value.numeric" => "The actual value must be a number.",
    ];

    public function mount($inspection_report_document_number = null)
    {
        $this->inspection_report_document_number = $inspection_report_document_number;
        $this->periodKey = "p" . session("stepDetailSaved.period");
        $this->dimensions = session("stepDetailSaved.dimensions.{$this->periodKey}", []);

        if ($this->dimensions) {
            foreach ($this->dimensions as $key => $dimensions) {
                if (property_exists($this, "start_time") && property_exists($this, "end_time")) {
                    $this->start_time = Carbon::parse($dimensions["start_datetime"])->format("H:i");
                    $this->end_time = Carbon::parse($dimensions["end_datetime"])->format("H:i");
                }
            }
            // dd($this->start_time, $this->end_time);
        }
        $part_code = session("stepHeaderSaved.part_number");
        $this->uploads = Upload::whereHas("tags", function ($q) use ($part_code) {
            $q->where("name", $part_code);
        })->get();
        // if (empty($this->dimensions)) $this->addDimension();
    }

    public function addDimension()
    {
        $period = session("stepDetailSaved.period");
        // dd(session('stepDetailSaved'));
        $this->start_time = \Carbon\Carbon::parse(
            session("stepDetailSaved.details." . "p" . $period . ".start_datetime"),
        )->format("H:i");
        $this->end_time = \Carbon\Carbon::parse(
            session("stepDetailSaved.details." . "p" . $period . ".end_datetime"),
        )->format("H:i");

        $this->dimensions[] = [
            "inspection_report_document_number" => $this->inspection_report_document_number,
            "lower_limit" => "",
            "upper_limit" => "",
            "limit_uom" => "",
            "actual_value" => "",
            "judgement" => "",
            "area" => "",
            "remarks" => "",
        ];
    }

    public function removeDimension($index)
    {
        unset($this->dimensions[$index]);
        $this->dimensions = array_values($this->dimensions);
    }

    public function updated($property, $value)
    {
        $this->validateOnly($property);

        /* if the property that changed ends with ".judgement" … */
        if (Str::endsWith($property, ".judgement")) {
            // extract the row index: "dimensions.3.judgement" → 3
            $index = (int) Str::between($property, "dimensions.", ".judgement");

            // when the new value is NOT "NG", blank out the remarks
            if ($value !== "NG") {
                $this->dimensions[$index]["remarks"] = "";
            }
            $this->validate();
        }
    }

    public function saveStep()
    {
        $this->validate();

        foreach ($this->dimensions as $index => $dimensions) {
            $this->dimensions[$index]["start_datetime"] = Carbon::parse($this->start_time)->format(
                "Y-m-d H:i:s",
            );
            $this->dimensions[$index]["end_datetime"] = Carbon::parse($this->end_time)->format(
                "Y-m-d H:i:s",
            );
        }

        session()->put("stepDetailSaved.dimensions.{$this->periodKey}", $this->dimensions);
        $this->dispatch("toast", message: "dimensions saved successfully!");
    }

    public function resetStep()
    {
        $this->dimensions = [];
        $this->start_time = "";
        $this->end_time = "";
        $this->resetValidation();
        $this->forgetNestedKey("stepDetailSaved.dimensions", $this->periodKey);
        $this->dispatch("toast", message: "dimensions reset successfully!");
    }

    public function render()
    {
        return view("livewire.inspection-form.step-dimensions");
    }
}
