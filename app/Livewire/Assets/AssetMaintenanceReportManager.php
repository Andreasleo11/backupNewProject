<?php

namespace App\Livewire\Assets;

use App\Models\Asset;
use App\Models\AssetMaintenanceReport;
use App\Models\MaintenanceChecklistGroup;
use App\Models\MaintenanceChecklistItem;
use App\Models\MaintenanceReportDetail;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class AssetMaintenanceReportManager extends Component
{
    use WithPagination;

    // List Filtering State
    public $search = '';
    public $selectedPeriod = '';
    public $selectedYear = '';

    // UI state
    public $showForm = false;
    public $showDetail = false;
    public $editingReportId = null;
    public $showingReportId = null;

    // Form data
    public $assetId;
    public $revisionDate;
    public $checklist = []; // item_id => ['checked' => bool, 'condition' => 'good'|'bad', 'remark' => string, 'checked_by' => string]
    
    // Custom/New checklist items added dynamically
    public $newItems = []; // Array of ['group_id' => int, 'name' => string, 'condition' => 'good'|'bad', 'remark' => string, 'checked_by' => string]

    // Loaded report details for showing detail view
    public $activeReport = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'selectedPeriod' => ['except' => ''],
        'selectedYear' => ['except' => ''],
    ];

    public function render()
    {
        $reports = AssetMaintenanceReport::query()
            ->with(['asset', 'details', 'details.checklistItem'])
            ->when($this->search, function ($query) {
                $query->whereHas('asset', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('asset_tag', 'like', '%' . $this->search . '%')
                      ->orWhere('ip_address', 'like', '%' . $this->search . '%')
                      ->orWhere('username', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->selectedPeriod, function ($query) {
                $query->where('period', $this->selectedPeriod);
            })
            ->when($this->selectedYear, function ($query) {
                $query->where('year', $this->selectedYear);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.assets.asset-maintenance-report-manager', [
            'reports' => $reports,
            'assets' => Asset::all(),
            'users' => User::all(),
            'checklistGroups' => MaintenanceChecklistGroup::with('items')->get(),
        ]);
    }

    public function resetFields()
    {
        $this->assetId = null;
        $this->revisionDate = null;
        $this->checklist = [];
        $this->newItems = [];
        $this->editingReportId = null;
        $this->showingReportId = null;
        $this->activeReport = null;
        $this->showForm = false;
        $this->showDetail = false;
    }

    public function showAddForm($prefilledAssetId = null)
    {
        $this->resetFields();
        $this->assetId = $prefilledAssetId;
        
        // Initialize checklist state for all existing items
        $items = MaintenanceChecklistItem::all();
        foreach ($items as $item) {
            $this->checklist[$item->id] = [
                'checked' => false,
                'condition' => '',
                'remark' => '',
                'checked_by' => '',
            ];
        }

        $this->showForm = true;
    }

    public function store()
    {
        $this->validate([
            'assetId' => 'required|exists:assets,id',
            'revisionDate' => 'nullable|date',
            'checklist' => 'array',
            'checklist.*.checked' => 'boolean',
            'checklist.*.condition' => 'required_if:checklist.*.checked,true|nullable|in:good,bad',
            'checklist.*.remark' => 'nullable|string|max:1000',
            'checklist.*.checked_by' => 'required_if:checklist.*.checked,true|nullable|string',
            'newItems' => 'array',
            'newItems.*.name' => 'required|string|max:255',
            'newItems.*.condition' => 'required|in:good,bad',
            'newItems.*.remark' => 'nullable|string|max:1000',
            'newItems.*.checked_by' => 'required|string',
        ], [
            'checklist.*.condition.required_if' => 'Condition is required when checked.',
            'checklist.*.checked_by.required_if' => 'Checker is required when checked.',
        ]);

        // Duplicate check (1 report per asset per caturwulan period)
        $month = now()->month;
        $year = now()->year;
        $period = ($month >= 1 && $month <= 4) ? 1 : (($month >= 5 && $month <= 8) ? 2 : 3);

        $exists = AssetMaintenanceReport::where('asset_id', $this->assetId)
            ->where('period', $period)
            ->where('year', $year)
            ->where('id', '!=', $this->editingReportId)
            ->exists();

        if ($exists) {
            $this->addError('assetId', 'A maintenance report already exists for this asset in the current caturwulan period.');
            return;
        }

        \DB::transaction(function () use ($period, $year) {
            $report = AssetMaintenanceReport::updateOrCreate(
                ['id' => $this->editingReportId],
                [
                    'document_number' => $this->editingReportId ? AssetMaintenanceReport::find($this->editingReportId)->document_number : AssetMaintenanceReport::generateNoDokumen(),
                    'asset_id' => $this->assetId,
                    'revision_date' => $this->revisionDate ?: null,
                ]
            );

            // Handle Predefined Items
            // If editing, clear existing details first
            if ($this->editingReportId) {
                MaintenanceReportDetail::where('report_id', $report->id)->delete();
            }

            foreach ($this->checklist as $itemId => $data) {
                if (isset($data['checked']) && $data['checked']) {
                    MaintenanceReportDetail::create([
                        'report_id' => $report->id,
                        'checklist_item_id' => $itemId,
                        'condition' => $data['condition'],
                        'remark' => $data['remark'] ?: null,
                        'checked_by' => $data['checked_by'],
                    ]);
                }
            }

            // Handle Custom/New items (persisted as generic details without templates)
            foreach ($this->newItems as $newItem) {
                MaintenanceReportDetail::create([
                    'report_id' => $report->id,
                    'checklist_item_id' => null,
                    'custom_item_name' => $newItem['name'],
                    'condition' => $newItem['condition'],
                    'remark' => $newItem['remark'] ?: null,
                    'checked_by' => $newItem['checked_by'],
                ]);
            }
        });

        session()->flash('message', $this->editingReportId ? 'Maintenance report updated successfully.' : 'Maintenance report created successfully.');
        $this->resetFields();
    }

    public function edit($id)
    {
        $this->resetFields();
        $report = AssetMaintenanceReport::with('details')->findOrFail($id);
        $this->editingReportId = $id;
        $this->assetId = $report->asset_id;
        $this->revisionDate = $report->revision_date;

        // Initialize checklist state for all existing items
        $allItems = MaintenanceChecklistItem::all();
        foreach ($allItems as $item) {
            $detail = $report->details->firstWhere('checklist_item_id', $item->id);
            $this->checklist[$item->id] = [
                'checked' => (bool)$detail,
                'condition' => $detail?->condition ?? '',
                'remark' => $detail?->remark ?? '',
                'checked_by' => $detail?->checked_by ?? '',
            ];
        }

        // Initialize custom items
        $customDetails = $report->details->whereNull('checklist_item_id');
        foreach ($customDetails as $custom) {
            $this->newItems[] = [
                'group_id' => 1, // Fallback since it's now untied to a group in DB
                'name' => $custom->custom_item_name,
                'condition' => $custom->condition,
                'remark' => $custom->remark ?? '',
                'checked_by' => $custom->checked_by,
            ];
        }

        $this->showForm = true;
    }

    public function show($id)
    {
        $this->resetFields();
        $this->showingReportId = $id;
        $this->activeReport = AssetMaintenanceReport::with(['asset', 'details', 'details.checklistItem', 'details.checklistItem.group'])->findOrFail($id);
        $this->showDetail = true;
    }

    public function delete($id)
    {
        AssetMaintenanceReport::findOrFail($id)->delete();
        session()->flash('message', 'Maintenance report deleted successfully.');
    }

    // Helper Action: Check All Predefined Items
    public function checkAll()
    {
        foreach ($this->checklist as $itemId => $data) {
            $this->checklist[$itemId]['checked'] = true;
        }
    }

    // Helper Action: Set All Predefined Checked Items' Condition to Good
    public function setAllGood()
    {
        foreach ($this->checklist as $itemId => $data) {
            if ($data['checked']) {
                $this->checklist[$itemId]['condition'] = 'good';
            }
        }
        foreach ($this->newItems as $index => $item) {
            $this->newItems[$index]['condition'] = 'good';
        }
    }

    // Helper Action: Set Checked By of Checked Predefined Items to Current User Name
    public function setCheckedByMe()
    {
        $userName = auth()->user()->name;
        foreach ($this->checklist as $itemId => $data) {
            if ($data['checked']) {
                $this->checklist[$itemId]['checked_by'] = $userName;
            }
        }
        foreach ($this->newItems as $index => $item) {
            $this->newItems[$index]['checked_by'] = $userName;
        }
    }

    // Helper Action: Add a New Checklist Item (Client-Side State)
    public function addNewChecklistItem($groupId)
    {
        $this->newItems[] = [
            'group_id' => $groupId,
            'name' => '',
            'condition' => 'good',
            'remark' => '',
            'checked_by' => '',
        ];
    }

    // Helper Action: Remove a custom item from the array before saving
    public function removeNewChecklistItem($index)
    {
        unset($this->newItems[$index]);
        $this->newItems = array_values($this->newItems);
    }
}
