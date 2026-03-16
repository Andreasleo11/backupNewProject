<?php

namespace App\Livewire\MonthlyBudget;

use App\Domain\MonthlyBudget\Actions\SubmitBudgetReportAction;
use App\Domain\MonthlyBudget\Services\BudgetReportService;
use App\Models\MonthlyBudgetReport as Report;
use App\Infrastructure\Persistence\Eloquent\Models\Department;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

class Form extends Component
{
    use WithFileUploads;

    // Report Header
    public ?int $reportId = null;
    public $dept_no;
    public $report_date;
    public bool $useExcel = false;
    public $excel_file;
    public ?string $excelName = null;

    // Report Items
    public array $items = [];

    protected function rules()
    {
        $rules = [
            'dept_no' => 'required',
            'report_date' => 'required|date',
            'useExcel' => 'boolean',
        ];

        if ($this->useExcel) {
            $rules['excel_file'] = 'required|file|mimes:xlsx,xls|max:10240';
        } else {
            $rules['items'] = 'required|array|min:1';
            $rules['items.*.name'] = 'required|string|max:255';
            $rules['items.*.uom'] = 'required|string|max:255';
            $rules['items.*.quantity'] = 'required|numeric|min:0';
            $rules['items.*.remark'] = 'nullable|string|max:255';
            
            if ($this->isDept363) {
                $rules['items.*.spec'] = 'required|string|max:255';
                $rules['items.*.last_recorded_stock'] = 'nullable|numeric';
                $rules['items.*.usage_per_month'] = 'nullable|string';
            }
        }

        return $rules;
    }

    public function mount(?int $reportId = null): void
    {
        $user = auth()->user();
        if ($reportId) {
            $this->reportId = $reportId;
            $report = Report::with('details')->findOrFail($reportId);

            if (!$report->isDraft()) {
                session()->flash('error', 'Only reports in Draft state can be edited.');
                $this->redirectRoute('monthly-budget-reports.show', $reportId);
                return;
            }

            $this->dept_no = $report->dept_no;
            $this->report_date = $report->report_date;
            $this->items = $report->details->toArray();
        } else {
            $this->dept_no = $user->department?->dept_no;
            $this->report_date = now()->format('Y-m-d');
            $this->addItem();
        }
    }

    public function getIsDept363Property(): bool
    {
        return (string)$this->dept_no === '363';
    }

    public function addItem(): void
    {
        $this->items[] = [
            'name' => '',
            'spec' => '',
            'uom' => 'PCS',
            'last_recorded_stock' => '',
            'usage_per_month' => '',
            'quantity' => '',
            'remark' => '',
        ];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function updatedUseExcel(): void
    {
        $this->resetErrorBag('excel_file');
    }

    public function totalQuantity(): float
    {
        return array_reduce($this->items, fn($sum, $item) => $sum + (float)($item['quantity'] ?? 0), 0.0);
    }

    public function saveDraft(BudgetReportService $service): void
    {
        $this->save('draft', $service);
    }

    public function signAndSubmit(BudgetReportService $service, SubmitBudgetReportAction $submitAction): void
    {
        $this->save('submit', $service, $submitAction);
    }

    private function save(string $action, BudgetReportService $service, ?SubmitBudgetReportAction $submitAction = null): void
    {
        $this->validate();

        $data = [
            'dept_no' => $this->dept_no,
            'creator_id' => auth()->id(),
            'report_date' => $this->report_date,
        ];

        DB::beginTransaction();
        try {
            if ($this->useExcel) {
                $result = $service->createFromExcel($data, $this->excel_file->getRealPath());
            } else {
                if ($this->reportId) {
                    $result = $service->updateReport($this->reportId, $data);
                    // Sync items (Delete and Re-create for simplicity in this TALL implementation)
                    \App\Models\MonthlyBudgetReportDetail::where('header_id', $this->reportId)->delete();
                    foreach ($this->items as $item) {
                        \App\Models\MonthlyBudgetReportDetail::create(array_merge($item, ['header_id' => $this->reportId]));
                    }
                } else {
                    $result = $service->createReport($data, $this->items);
                }
            }

            if (!$result['success']) {
                throw new \Exception($result['message']);
            }

            $report = $result['report'];

            if ($action === 'submit' && $submitAction) {
                $submitResult = $submitAction->execute($report, (int)auth()->id());
                if (!$submitResult['success']) {
                    throw new \Exception($submitResult['message']);
                }
            }

            DB::commit();
            session()->flash('success', $result['message']);
            $this->redirectRoute('monthly-budget-reports.index');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.monthly-budget.form', [
            'departments' => Department::all()
        ]);
    }
}
