<?php

namespace App\Livewire\InspectionForm;

use App\Traits\ClearsNestedSession;
use Livewire\Component;
use Illuminate\Support\Str;

class StepSampling extends Component
{
    use ClearsNestedSession;

    public $samples = [];
    public $second_inspection_document_number;
    public $periodKey;

    public array $sessionSaved = [];
    public array $baselineKey = [];
    public ?string $savedAt = null;
    public bool $isSaved = false;

    protected $rules = [
        "samples.*.second_inspection_document_number" => "required|string",
        "samples.*.quantity" => "required|integer|min:1",
        "samples.*.box_label" => "required|string",
        "samples.*.appearance" => "required|string|in:OK,NG",
        "samples.*.ng_quantity" =>
            "required_if:samples.*.appearance,NG|nullable|integer|min:1|lt:samples.*.quantity",
        "samples.*.remarks" => "required_if:samples.*.appearance,NG|nullable|string",
    ];

    protected function rules(): array
    {
        $rules = [
            "samples.*.second_inspection_document_number" => "required|string",
            "samples.*.quantity" => "required|integer|min:1",
            "samples.*.box_label" => "required|string",
            "samples.*.appearance" => "required|string|in:OK,NG",
        ];

        foreach ($this->samples as $i => $row) {
            $isNG = ($row["appearance"] ?? "") === "NG";

            $rules["samples.$i.ng_quantity"] = [
                $isNG ? "required" : "nullable",
                "integer",
                "min:1",
                "lte:samples.$i.quantity",
            ];

            $rules["samples.$i.remarks"] = [$isNG ? "required" : "nullable", "string"];
        }

        return $rules;
    }

    protected $messages = [
        "samples.*.second_inspection_document_number.required" =>
            "The second inspection document number is required.",
        "samples.*.second_inspection_document_number.string" =>
            "The second inspection document number must be a string.",
        "samples.*.quantity.required" => "The quantity is required.",
        "samples.*.quantity.integer" => "The quantity must be an integer.",
        "samples.*.quantity.min" => "The quantity must be at least 1.",
        "samples.*.box_label.required" => "The box label is required.",
        "samples.*.box_label.string" => "The box label must be a string.",
        "samples.*.appearance.required" => "The appearance is required.",
        "samples.*.appearance.string" => "The appearance must be a string.",
        "samples.*.appearance.in" => "The appearance must be either OK or NG.",
        "samples.*.ng_quantity.required" => "The NG quantity is required when appearance is NG.",
        "samples.*.ng_quantity.integer" => "The NG quantity must be an integer.",
        "samples.*.ng_quantity.min" => "The NG quantity must be at least 1.",
        "samples.*.ng_quantity.lte" =>
            "The NG quantity must be less than or equal to the quantity.",
        "samples.*.remarks.required" => "The remarks are required when appearance is NG.",
        "samples.*.remarks.string" => "The remarks must be a string.",
    ];

    protected function norm($v)
    {
        return $v === "" ? null : $v;
    }

    protected function ensureRowKeys(): void
    {
        foreach ($this->samples as $i => $row) {
            if (empty($row["row_key"])) {
                $this->samples[$i]["row_key"] = (string) Str::uuid();
            }
        }
    }

    protected function buildBaselineMap(): void
    {
        $this->baselineKey = [];
        foreach ($this->sessionSaved as $row) {
            if (!empty($row["row_key"])) {
                $this->baselineKey[$row["row_key"]] = $row;
            }
        }
    }

    public function isRowFieldSaved(string $rowKey, string $field): bool
    {
        $cur = $this->norm(
            data_get(collect($this->samples)->firstWhere("row_key", $rowKey), $field),
        );
        $base = $this->norm(data_get($this->baselineKey[$rowKey] ?? [], $field));
        return $cur !== null && $cur === $base;
    }

    public function mount($second_inspection_document_number = null)
    {
        $this->second_inspection_document_number = $second_inspection_document_number;
        $this->periodKey = "p" . session("stepDetailSaved.period");

        $this->samples = session("stepDetailSaved.samples.{$this->periodKey}", []);
        $this->sessionSaved = session("stepDetailSaved.samples.{$this->periodKey}", []);
        $this->savedAt = session("stepDetailSaved.samples_meta.{$this->periodKey}.savedAt");
        $this->isSaved = !empty($this->savedAt);

        if (empty($this->samples)) {
            $this->addSample();
        } else {
            foreach ($this->samples as &$sample) {
                $sample["second_inspection_document_number"] =
                    $this->second_inspection_document_number;
            }
        }
        $this->ensureRowKeys();

        if (!empty($this->sessionSaved)) {
            foreach ($this->sessionSaved as $i => $row) {
                if (empty($row["row_key"])) {
                    $this->sessionSaved[$i]["row_key"] = (string) Str::uuid();
                }
            }
        }
        $this->buildBaselineMap();
    }

    public function addSample()
    {
        $this->samples[] = [
            "row_key" => (string) Str::uuid(),
            "second_inspection_document_number" => $this->second_inspection_document_number,
        ];
    }

    public function removeSample($index)
    {
        unset($this->samples[$index]);
        $this->samples = array_values($this->samples);
    }

    public function updated($property, $value)
    {
        $this->validateOnly($property);

        if (Str::endsWith($property, ".appearance")) {
            $index = (int) Str::between($property, "samples.", ".appearance");

            if ($value === "OK") {
                unset($this->samples[$index]["ng_quantity"], $this->samples[$index]["remarks"]);
            }
        }
    }

    public function saveStep()
    {
        $this->validate();

        session()->put("stepDetailSaved.samples.{$this->periodKey}", $this->samples);
        $this->savedAt = now()->toIso8601String();
        session()->put("stepDetailSaved.samples_meta.{$this->periodKey}.savedAt", $this->savedAt);

        $this->sessionSaved = $this->samples;
        $this->buildBaselineMap();
        $this->isSaved = !empty($this->samples);

        $this->dispatch("samplingSaved", savedAt: $this->savedAt);
        $this->dispatch("toast", message: "Sampling data successfully!");
    }

    public function resetStep()
    {
        $this->samples = [];
        $this->resetValidation();
        $this->addSample();

        $this->forgetNestedKey("stepDetailSaved.samples", $this->periodKey);
        $this->forgetNestedKey("stepDetailSaved.samples_meta", $this->periodKey);

        $this->sessionSaved = [];
        $this->baselineKey = [];
        $this->savedAt = null;
        $this->isSaved = false;

        $this->dispatch("samplingReset");
        $this->dispatch("toast", message: "Sampling data reset successfully!");
    }

    public function render()
    {
        return view("livewire.inspection-form.step-sampling");
    }
}
