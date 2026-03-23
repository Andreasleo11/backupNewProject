<?php

namespace App\Livewire\Overtime;

use App\Domain\Overtime\Services\OvertimeApprovalService;
use App\Models\DetailFormOvertime;
use App\Models\HeaderFormOvertime;
use App\Models\OvertimeFormApproval;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Detail extends Component
{
    public HeaderFormOvertime $form;
    public int $formId;

    // Reject modal state
    public bool $showRejectModal = false;
    public string $rejectReason = '';
    public ?int $rejectApprovalId = null;

    // JPayroll push feedback
    public ?string $pushMessage = null;
    public bool $pushSuccess = false;

    protected OvertimeApprovalService $approvalService;

    public function boot(OvertimeApprovalService $approvalService): void
    {
        $this->approvalService = $approvalService;
    }

    public function mount(int $id): void
    {
        $this->formId = $id;
        $this->loadForm();
    }

    public function loadForm(): void
    {
        $this->form = HeaderFormOvertime::with([
            'user',
            'department',
            'flow.steps',
            'approvals.step',
            'approvals.approver',
            'details.actualOvertimeDetail',
            'details.employee',
        ])->findOrFail($this->formId);
    }

    // ── Approval actions ─────────────────────────────────────────────────────

    public function sign(int $stepId): void
    {
        $result = $this->approvalService->sign($this->formId, $stepId);

        if ($result['success']) {
            $this->loadForm();
            $this->dispatch('toast', message: $result['message'], type: 'success');
        } else {
            $this->dispatch('toast', message: $result['message'], type: 'error');
        }
    }

    public function openRejectModal(int $approvalId): void
    {
        $this->rejectApprovalId = $approvalId;
        $this->rejectReason = '';
        $this->showRejectModal = true;
    }

    public function submitReject(): void
    {
        $this->validate(['rejectReason' => 'required|string|min:5']);

        $result = $this->approvalService->reject(
            $this->formId,
            $this->rejectApprovalId,
            $this->rejectReason,
        );

        $this->showRejectModal = false;
        $this->rejectApprovalId = null;

        if ($result['success']) {
            $this->loadForm();
            $this->dispatch('toast', message: $result['message'], type: 'success');
        } else {
            $this->dispatch('toast', message: $result['message'], type: 'error');
        }
    }

    // ── JPayroll push actions ─────────────────────────────────────────────────

    public function pushDetail(int $detailId, string $action): void
    {
        $this->authorize('pushToPayroll', HeaderFormOvertime::class);

        $detail = DetailFormOvertime::with('employee', 'header')->findOrFail($detailId);
        $service = app(\App\Domain\Overtime\Services\OvertimeJPayrollService::class);
        $result = $service->pushSingleDetail($detail, $action);

        $this->pushSuccess = $result['success'];
        $this->pushMessage = $result['message'];
        $this->loadForm();
    }

    public function pushAll(): void
    {
        $this->authorize('pushToPayroll', HeaderFormOvertime::class);

        $service = app(\App\Domain\Overtime\Services\OvertimeJPayrollService::class);
        $result = $service->pushAllDetails($this->formId);

        $this->pushSuccess = $result['success'];
        $this->pushMessage = $result['message'] . " ({$result['total_success']} ok, {$result['total_failed']} failed)";
        $this->loadForm();
    }

    public function clearPushMessage(): void
    {
        $this->pushMessage = null;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Build approval timeline for the view.
     * Each item: step_order, role_slug, label, status, approver_name, signed_at, signature_path
     */
    public function getTimelineProperty(): array
    {
        if (! $this->form->flow) {
            return [];
        }

        return $this->form->flow->steps->map(function ($step) {
            $approval = $this->form->approvals
                ->firstWhere('flow_step_id', $step->id);

            $status = $approval?->status ?? 'pending';
            $isCurrent = $this->form->currentStep()?->id === $step->id;

            return [
                'step_order'     => $step->step_order,
                'role_slug'      => $step->role_slug,
                'label'          => ucwords(str_replace(['-', '_'], ' ', $step->role_slug)),
                'status'         => $status,    // pending | approved | rejected
                'is_current'     => $isCurrent,
                'approver_name'  => $approval?->approver?->name,
                'signed_at'      => $approval?->signed_at,
                'signature_path' => $approval?->signature_path,
                'approval_id'    => $approval?->id,
                'step_id'        => $step->id,
                'can_sign'       => $isCurrent && Auth::user()->hasRole($step->role_slug),
            ];
        })->toArray();
    }

    public function render()
    {
        return view('livewire.overtime.detail', [
            'timeline' => $this->timeline,
            'user'     => Auth::user(),
            'canPush'  => Auth::user()->can('pushToPayroll', HeaderFormOvertime::class),
        ])->layout('new.layouts.app');
    }
}
