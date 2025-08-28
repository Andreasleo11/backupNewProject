<?php

namespace App\Livewire\InspectionForm;

use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Illuminate\Support\Str;

class StepHeader extends Component
{
    public $document_number;
    public $customer;
    public $inspection_date;
    public $part_number;
    public $part_name;
    public $weight;
    public $weight_uom;
    public $material;
    public $color;
    public $tool_number_or_cav_number;
    public $machine_number;
    public $shift;
    public $operator;
    public $errorMessage = null;

    protected $rules = [
        "document_number" => "required|string|unique:inspection_reports,document_number",
        "customer" => "required|string",
        "inspection_date" => "required|date",
        "part_number" => "required|string",
        "part_name" => "required|string",
        "weight" => "required|numeric",
        "weight_uom" => "required|string",
        "material" => "required|string",
        "color" => "required|string",
        "tool_number_or_cav_number" => "required|string",
        "machine_number" => "required|string",
        "shift" => "required|integer|min:1|max:3",
        "operator" => "required|string",
    ];

    protected $listeners = ["dropdownSelected"];

    public function dropdownSelected($payload = null)
    {
        if (!is_array($payload)) {
            return;
        }

        // For part_number or part_name
        if (isset($payload["item_no"]) && isset($payload["description"])) {
            $this->part_number = $payload["item_no"];
            $this->part_name = $payload["description"];
        }

        // Generic fallback (e.g. customer, operator, etc.)
        if (!empty($payload["field"]) && isset($payload["value"])) {
            $this->{$payload["field"]} = $payload["value"];
        }
    }

    public function updated($field)
    {
        $this->validateOnly($field);
    }

    public function mount()
    {
        $saved = session("stepHeaderSaved");

        if ($saved) {
            foreach ($saved as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
        }

        if (!$this->document_number) {
            $this->document_number =
                "INSP-" . now()->format("Ymd-His") . "-" . strtoupper(Str::random(4));
        }

        if (!$this->inspection_date) {
            $this->inspection_date = now()->format("Y-m-d");
        }
    }

    public function saveStep()
    {
        $this->validate();

        $data = [
            "document_number" => $this->document_number,
            "customer" => $this->customer,
            "inspection_date" => $this->inspection_date,
            "part_number" => $this->part_number,
            "part_name" => $this->part_name,
            "weight" => $this->weight,
            "weight_uom" => $this->weight_uom,
            "material" => $this->material,
            "color" => $this->color,
            "tool_number_or_cav_number" => $this->tool_number_or_cav_number,
            "machine_number" => $this->machine_number,
            "shift" => $this->shift,
            "operator" => $this->operator,
        ];

        session(["stepHeaderSaved" => $data]);

        $this->dispatch("stepHeaderSaved", $data);
        $this->dispatch("nextStep");
        $this->dispatch("toast", message: "Header saved successfully.");
    }

    public function render()
    {
        Log::info("customerError" . $this->getErrorBag()->first("customer"));
        return view("livewire.inspection-form.step-header", [
            "customerError" => $this->getErrorBag()->first("customer"),
        ]);
    }
}
