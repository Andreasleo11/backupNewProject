<?php

namespace App\Livewire\Overtime;

use App\Application\Overtime\Queries\OvertimeQueryBuilder;
use App\Domain\Overtime\Models\OvertimeForm;
use App\Domain\Overtime\Models\OvertimeFormDetail;
use App\Domain\Overtime\Services\OvertimeApprovalService;
use App\Infrastructure\Approval\Contracts\Approvals;
use App\Infrastructure\Persistence\Eloquent\Models\Department;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('new.layouts.app')]
class ConsolidatedDetail extends Component
{
    public string $date;
    public ?int $dept = null;
    public ?string $branch = null;
    public ?string $startDate = null;
    public ?string $endDate = null;
    public ?string $infoStatus = null;
    public ?string $search = null;
    public ?int $perPage = null;
    public ?string $sortField = null;
    public ?string $sortDirection = null;
    public ?string $range = null;
    public bool $groupByDate = false;
    public bool $hideSigned = true;
    public string $viewMode = 'flattened'; // 'flattened' or 'grouped'

    // Reject modal state
    public bool $showRejectModal = false;
    public string $rejectReason = '';
    public ?int $rejectFormId = null;
    public ?int $rejectApprovalId = null;

    // Bulk action state
    public array $selectedIds = [];
    public array $departments = [];

    protected OvertimeApprovalService $approvalService;

    public function boot(OvertimeApprovalService $approvalService): void
    {
        $this->approvalService = $approvalService;
    }

    public function mount(string $date)
    {
        $this->date = $date;
        $this->dept = request('dept');
        $this->branch = request('branch');
        $this->startDate = request('startDate');
        $this->endDate = request('endDate');
        $this->infoStatus = request('infoStatus');
        $this->search = request('search');
        $this->perPage = request('per_page');
        $this->sortField = request('sort');
        $this->sortDirection = request('dir');
        $this->range = request('range');
        $this->groupByDate = request('group_date') == '1';
        $this->hideSigned = request('hide_signed') != '0';
    }

    private function getFilterParams(): array
    {
        return [
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'dept' => $this->dept,
            'infoStatus' => $this->infoStatus,
            'search' => $this->search,
            'perPage' => $this->perPage,
            'sortField' => $this->sortField,
            'sortDirection' => $this->sortDirection,
            'range' => $this->range,
        ];
    }

    public function render()
    {
        $user = Auth::user();
        $builder = new OvertimeQueryBuilder;

        // Determine default filter based on user permissions and pending approvals
        $defaultFilter = $this->getFilterParams();

        $query = $builder->build($user, $defaultFilter);

        // Filter by the specific date - we need forms where the earliest start_date matches
        $query->whereRaw('
            (SELECT MIN(start_date) FROM detail_form_overtime WHERE header_id = header_form_overtime.id) = ?
        ', [$this->date]);

        // Apply department filter if provided
        if ($this->dept) {
            $query->where('dept_id', $this->dept);
        }

        // Apply branch filter if provided (handle multiple branches separated by comma)
        if ($this->branch) {
            $branches = explode(',', $this->branch);
            $branches = array_map('trim', $branches);
            $query->whereIn('branch', $branches);
        }

        $headers = $query->with([
            'user:id,name',
            'department:id,name',
            'failedDetails',
            'details' => function ($q) {
                if (!empty($this->search)) {
                    $s = trim($this->search);
                    $q->where(function ($qd) use ($s) {
                        $qd->where('name', 'like', '%' . $s . '%')
                          ->orWhere('NIK', 'like', $s . '%')
                          ->orWhere('job_desc', 'like', '%' . $s . '%');
                    });
                }
            },
            'approvalRequest',
            'approvalRequest.steps',
        ])->get();

        // Calculate approval permissions for each form
        $user = Auth::user();
        foreach ($headers as $form) {
            $form->can_approve = $user->can('approve', $form);
        }

        if(!$this->dept){
            $this->departments = $headers->pluck('department')->unique('id')->values()->all();
        }

        $totalForms = $headers->count();
        // Calculate stats based on the explicitly filtered details relation
        $totalDetails = $headers->sum(fn($h) => $h->details->count());
        $approvedDetails = $headers->sum(fn($h) => $h->details->where('status', 'Approved')->count());
        $rejectedDetails = $headers->sum(fn($h) => $h->details->where('status', 'Rejected')->count());
        $pendingDetails = $headers->sum(fn($h) => $h->details->whereNull('status')->count());

        $backFilters = array_filter([
            'dept' => $this->dept,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'info_status' => $this->infoStatus,
            'q' => $this->search,
            'per_page' => $this->perPage,
            'sort' => $this->sortField,
            'dir' => $this->sortDirection,
            'range' => $this->range,
            'group_date' => $this->groupByDate ? 1 : 0,
            'hide_signed' => $this->hideSigned ? 1 : 0,
        ]);

        return view('livewire.overtime.consolidated-detail', [
            'headers' => $headers,
            'date' => $this->date,
            'totalForms' => $totalForms,
            'totalDetails' => $totalDetails,
            'approvedDetails' => $approvedDetails,
            'rejectedDetails' => $rejectedDetails,
            'pendingDetails' => $pendingDetails,
            'user' => Auth::user(),
            'canApprove' => Auth::user()->can('overtime.approve'),
            'backFilters' => $backFilters,
            'viewMode' => $this->viewMode,
        ]);
    }

    // Approval Actions
    public function sign(int $formId, int $stepId): void
    {
        $form = OvertimeForm::findOrFail($formId);
        $this->authorize('approve', $form);

        $result = $this->approvalService->sign($formId, $stepId);

        if ($result['success']) {
            $this->dispatch('flash', type: 'success', message: $result['message']);
        } else {
            $this->dispatch('flash', type: 'error', message: $result['message']);
        }

        // Refresh the component data
        $this->render();
    }

    public function signSelected(): void
    {
        if (empty($this->selectedIds)) {
            $this->dispatch('flash', type: 'warning', message: 'No forms selected for approval.');
            return;
        }

        $successCount = 0;
        $failCount = 0;
        $selectedForms = OvertimeForm::with('approvalRequest.steps')->whereIn('id', $this->selectedIds)->get();

        foreach ($selectedForms as $form) {
            if (!$form) {
                $failCount++;
                continue;
            }

            try {
                $this->authorize('approve', $form);

                // Find the current step
                $currentStep = $form->approvalRequest?->steps->where('sequence', $form->approvalRequest->current_step)->first();
                if (!$currentStep) {
                    $failCount++;
                    continue;
                }

                $result = $this->approvalService->sign($form->id, $currentStep->id);
                if ($result['success']) {
                    $successCount++;
                } else {
                    $failCount++;
                }
            } catch (\Exception $e) {
                $failCount++;
            }
        }

        $this->selectedIds = [];

        if ($successCount > 0) {
            $this->dispatch('flash', type: 'success', message: "Successfully approved $successCount forms.");
        }
        if ($failCount > 0) {
            $this->dispatch('flash', type: 'error', message: "Failed to approve $failCount forms.");
        }

        $this->render();
    }

    public function openRejectModal(int $formId, int $approvalId): void
    {
        $this->rejectFormId = $formId;
        $this->rejectApprovalId = $approvalId;
        $this->rejectReason = '';
        $this->showRejectModal = true;
    }

    public function submitReject(): void
    {
        $form = OvertimeForm::findOrFail($this->rejectFormId);
        $this->authorize('reject', $form);

        $this->validate(['rejectReason' => 'required|string|min:5']);

        $result = $this->approvalService->reject(
            $this->rejectFormId,
            $this->rejectApprovalId,
            $this->rejectReason,
        );

        $this->showRejectModal = false;
        $this->rejectFormId = null;
        $this->rejectApprovalId = null;

        if ($result['success']) {
            $this->dispatch('flash', type: 'success', message: $result['message']);
        } else {
            $this->dispatch('flash', type: 'error', message: $result['message']);
        }

        // Refresh the component data
        $this->render();
    }

    // Payroll Push Actions
    public function pushDetail(int $formId, int $detailId): void
    {
        $form = OvertimeForm::findOrFail($formId);
        $this->authorize('pushToPayroll', $form);

        $detail = OvertimeFormDetail::findOrFail($detailId);
        $service = app(\App\Domain\Overtime\Services\OvertimeJPayrollService::class);
        $result = $service->pushSingleDetail($detail);

        $this->dispatch(
            'flash',
            type: $result['success'] ? 'success' : 'error',
            message: $result['message']
        );

        // Refresh the component data
        $this->render();
    }

    public function rejectDetail(int $formId, int $detailId): void
    {
        $form = OvertimeForm::findOrFail($formId);
        $this->authorize('pushToPayroll', $form);

        $detail = OvertimeFormDetail::findOrFail($detailId);
        $detail->status = 'Rejected';
        $detail->reason = 'Manual rejection by HR/Verificator';
        $detail->save();

        $service = app(\App\Domain\Overtime\Services\OvertimeJPayrollService::class);
        $service->checkAndUpdateHeaderPushStatus($formId);

        $this->dispatch('flash', type: 'success', message: 'Detail berhasil direject secara manual.');

        // Refresh the component data
        $this->render();
    }

    public function pushAll(int $formId): void
    {
        $form = OvertimeForm::findOrFail($formId);
        $this->authorize('pushToPayroll', $form);

        $service = app(\App\Domain\Overtime\Services\OvertimeJPayrollService::class);
        $result = $service->pushAllDetails($formId);

        $this->dispatch(
            'flash',
            type: $result['success'] ? 'success' : 'error',
            message: $result['message'] . " ({$result['total_success']} ok, {$result['total_failed']} failed)"
        );
        // Refresh the component data
        $this->render();
    }

    public function toggleViewMode(): void
    {
        $this->viewMode = $this->viewMode === 'flattened' ? 'grouped' : 'flattened';
    }
}