<?php

namespace App\Livewire\InspectionForm;

use App\Traits\ClearsNestedSession;
use Livewire\Component;

class StepFirst extends Component
{
    use ClearsNestedSession;

    public $detail_inspection_report_document_number;
    public $appearance;
    public $weight;
    public $weight_uom;
    public $fitting_test;
    public $remarks;

    public $periodKey;

    /** the last payload saved to session (baseline for per-field compare) */
    public array $sessionSaved = [];

    /** last saved timestamp for tooltip */
    public ?string $savedAt = null;

    public bool $isSaved = false;

    protected $rules = [
        "detail_inspection_report_document_number" => "required|string",
        "appearance" => "required|in:OK,NG",
        "weight" => "required|numeric|min:0",
        "weight_uom" => "required|string",
        "fitting_test" => "nullable|string",
        "remarks" => "required_if:appearance,NG|nullable|string",
    ];

    public function updated($property)
    {
        $this->validateOnly($property);
    }

    /** normalize values for comparison (treat '' as null) */
    protected function norm($v)
    {
        return $v === "" ? null : $v;
    }

    protected function snapshot(): array
    {
        $data = [
            "detail_inspection_report_document_number" =>
                $this->detail_inspection_report_document_number,
            "appearance" => $this->appearance,
            "weight" => $this->weight,
            "weight_uom" => $this->weight_uom,
            "fitting_test" => $this->fitting_test,
            "remarks" => $this->remarks,
        ];

        if (
            strtoupper((string) $this->appearance) === "OK" &&
            $this->remarks === null &&
            $this->remarks === ""
        ) {
            unset($data["remarks"]);
        }

        return $data;
    }

    public function getHasBaselineProperty(): bool
    {
        return !empty($this->sessionSaved);
    }

    /** per-field: exactly matches the last saved value (and not null) */
    public function isFieldSaved(string $field): bool
    {
        $cur = $this->norm(data_get($this, $field));
        $base = $this->norm(data_get($this->sessionSaved, $field));
        return $cur !== null && $cur === $base;
    }

    /** per-group (e.g., weight + uom) */
    public function isGroupSaved(array $fields): bool
    {
        foreach ($fields as $f) {
            if (!$this->isFieldSaved($f)) {
                return false;
            }
        }
        return true;
    }

    public function mount()
    {
        $this->periodKey = "p" . session("stepDetailSaved.period");
        $saved = session("stepDetailSaved.first_inspections.{$this->periodKey}", []);
        $this->sessionSaved = $saved;
        $this->savedAt = session(
            "stepDetailSaved.first_inspection_meta.{$this->periodKey}.savedAt",
        );
        $this->isSaved = !empty($saved);

        if ($saved) {
            foreach ($saved as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
        }
    }

    public function updatedAppearance($value): void
    {
        if ($value !== "NG") {
            $this->remarks = null; // wipe any previous text
        }
    }

    public function saveStep()
    {
        $this->validate();

        $data = $this->snapshot();
        session()->put("stepDetailSaved.first_inspections.{$this->periodKey}", $data);

        $this->savedAt = now()->toIso8601String();
        session()->put(
            "stepDetailSaved.first_inspection_meta.{$this->periodKey}.savedAt",
            $this->savedAt,
        );

        $this->sessionSaved = $data;
        $this->isSaved = true;

        $this->dispatch("toast", message: "First saved successfully!");
        $this->dispatch("firstInspectionSaved", savedAt: $this->savedAt);
        $this->dispatch("firstInspectionSaved")->to(\App\Livewire\InspectionForm\StepDetail::class);
    }

    public function resetStep()
    {
        $this->reset(["appearance", "weight", "weight_uom", "fitting_test", "remarks"]);

        $this->forgetNestedKey("stepDetailSaved.first_inspections", $this->periodKey);

        $this->sessionSaved = [];
        $this->savedAt = null;
        $this->isSaved = false;

        $this->dispatch("toast", message: "First step reset successfully!");
        $this->dispatch("firstInspectionReset");
        $this->dispatch("firstInspectionReset")->to(\App\Livewire\InspectionForm\StepDetail::class);
    }

    public function render()
    {
        return view("livewire.inspection-form.step-first");
    }
}
