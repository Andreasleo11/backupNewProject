<?php

namespace App\Livewire;

use App\Models\InspectionForm\InspectionReport;
use Livewire\Component;

class InspectionShow extends Component
{
    public InspectionReport $report;

    public function mount(InspectionReport $inspectionReport): void
    {
        // Eager-load the same relations you had in the controller
        $inspectionReport->load([
            "detailInspectionReports",
            "detailInspectionReports.firstInspections",
            "dimensionData",
            "detailInspectionReports.secondInspections",
            "detailInspectionReports.secondInspections.samplingData",
            "detailInspectionReports.secondInspections.packagingData",
            "detailInspectionReports.judgementData",
            "quantityData",
            "problemData",
        ]);

        $this->report = $inspectionReport;
    }

    public function render()
    {
        // Use your existing layout
        return view("livewire.inspection-form.show", [
            "inspectionReport" => $this->report,
        ])->layout("layouts.guest");
    }
}
