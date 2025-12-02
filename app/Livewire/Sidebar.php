<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Sidebar extends Component
{
    // UI state you may want to control later (kept for future use)
    public bool $expanded = false;

    public array $groupOpen = []; // groupId => bool

    // User context
    public $user;

    public string $department = '';

    public string $specification = '';

    public bool $isSuper = false;

    public bool $deptHead = false;

    // Route pattern maps
    public array $adminPatterns = [
        'admin.access-overview',
        'admin.departments*',
        // 'admin.specifications*',
        'changeemail.page',
        'pt.*',
        'md.parts.import',
    ];

    public array $computerPatterns = [
        'mastertinta.*',
        'masterinventory.*',
        'index.employeesmaster',
        'maintenance.inventory.*',
        'masterinventory.typeindex',
    ];

    public array $qualityPatterns = ['qaqc.report.*', 'listformadjust', 'qaqc.defectcategory'];

    public array $productionPatterns = ['indexpps', 'capacityforecastindex', 'pe.formlist'];

    public array $businessPatterns = ['indexds'];

    public array $maintenancePatterns = ['moulddown.*', 'linedown.*'];

    public array $hrdPatterns = ['hrd.importantDocs.*'];

    public array $purchasingPatterns = [
        'purchasing_home',
        'purchasing.evaluationsupplier.*',
        'reminderindex',
        'purchasingrequirement.*',
        'indexds',
        'fc.*',
    ];

    public array $accountingPatterns = ['accounting.purchase-request'];

    public array $inventoryPatterns = [
        'delsched.averagemonth',
        'inventoryfg',
        'inventorymtr',
        'invlinelist',
    ];

    public array $storePatterns = [
        'barcodeindex',
        'barcode.base.*',
        'inandout.*',
        'missingbarcode.*',
        'list.barcode',
        'barcode.historytable',
        'stockallbarcode',
        'updated.barcode.item.position',
        'delivery-notes.*',
        'destination.*',
        'vehicles.*',
    ];

    public array $stockMgmtPatterns = ['mastertinta.*'];

    public array $otherPatterns = [
        'director.pr.index',
        'purchaserequest',
        'daily-reports.*',
        'overtime.*',
        'actual.import.form',
        'formcuti.*',
        'formkeluar.*',
        'purchaserequest.monthlyprlist',
        'spk.*',
        'formkerusakan.*',
        'po.dashboard',
        'waiting_purchase_orders.*',
        'employee_trainings.*',
        'daily-report.form',
        'files.*',
        'department-expenses.*',
        'indexds',
        'vehicles.*',
    ];

    public array $employeeEvalSubPatterns = [
        'discipline.*',
        'yayasan.table',
        'magang.table',
        'format.evaluation.year.*',
        'exportyayasan.dateinput',
    ];

    public array $monthlyBudgetSubPatterns = [
        'monthly.budget.report.*',
        'monthly-budget-summary-report.*',
    ];

    public array $fileCompliancePatterns = [
        'requirements.*',
        'admin.requirement-uploads',
        'departments.overview',
        'compliance.dashboard',
    ];

    public function mount()
    {
        $this->user = Auth::user();
        $this->department = optional($this->user->department)->name ?? '';
        $this->specification = optional($this->user->specification)->name ?? '';
        $this->isSuper = true;
        $this->deptHead = (bool) $this->user->is_head;

        // Default open state per group based on current route
        $this->groupOpen = [
            'adminGroup' => request()->routeIs($this->adminPatterns),
            'computerGroup' => request()->routeIs($this->computerPatterns),
            'qualityGroup' => request()->routeIs($this->qualityPatterns),
            'productionGroup' => request()->routeIs($this->productionPatterns),
            'businessGroup' => request()->routeIs($this->businessPatterns),
            'maintenanceGroup' => request()->routeIs($this->maintenancePatterns),
            'humanResourceGroup' => request()->routeIs($this->hrdPatterns),
            'purchasingGroup' => request()->routeIs($this->purchasingPatterns),
            'accountingGroup' => request()->routeIs($this->accountingPatterns),
            'inventoryGroup' => request()->routeIs($this->inventoryPatterns),
            'storeGroup' => request()->routeIs($this->storePatterns),
            'stockManagementGroup' => request()->routeIs($this->stockMgmtPatterns),
            'otherGroup' => request()->routeIs($this->otherPatterns),
            'employeeEvaluationGroup' => request()->routeIs($this->employeeEvalSubPatterns),
            'monthlyBudgetGroup' => request()->routeIs($this->monthlyBudgetSubPatterns),
            'fileComplianceGroup' => request()->routeIs($this->fileCompliancePatterns),
        ];
        // dd($this->groupOpen);
    }

    // Optional handlers if you later want Livewire to control pinning / expand-all
    public function toggleExpanded()
    {
        $this->expanded = ! $this->expanded;
    }

    public function expandAllGroups()
    {
        foreach ($this->groupOpen as $k => $v) {
            $this->groupOpen[$k] = true;
        }
    }

    public function collapseAllGroups()
    {
        foreach ($this->groupOpen as $k => $v) {
            $this->groupOpen[$k] = false;
        }
    }

    public function render()
    {
        return view('livewire.sidebar');
    }
}
