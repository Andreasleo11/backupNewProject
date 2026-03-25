<?php

namespace App\Livewire\Overtime;

use App\Domain\Overtime\Services\OvertimeApprovalService;
use App\Domain\Overtime\Models\OvertimeFormDetail;
use App\Domain\Overtime\Models\OvertimeForm;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Detail extends Component
{
    public OvertimeForm $form;
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
        $this->form = OvertimeForm::with([
            'user',
            'department',
            'approvalRequest.steps',
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
        $this->authorize('pushToPayroll', OvertimeForm::class);

        $detail = OvertimeFormDetail::with('employee', 'header')->findOrFail($detailId);
        $service = app(\App\Domain\Overtime\Services\OvertimeJPayrollService::class);
        $result = $service->pushSingleDetail($detail, $action);

        $this->pushSuccess = $result['success'];
        $this->pushMessage = $result['message'];
        $this->loadForm();
    }

    public function pushAll(): void
    {
        $this->authorize('pushToPayroll', OvertimeForm::class);

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
     * Build approval timeline for the view using Unified Engine data.
     */
    public function getTimelineProperty(): array
    {
        $req = $this->form->approvalRequest;

        if (! $req) {
            return [];
        }

        return $req->steps->sortBy('sequence')->map(function ($step) use ($req) {
            $isCurrent = $req->current_step === $step->sequence && $req->status === 'IN_REVIEW';
            
            $status = 'pending';
            if ($step->sequence < $req->current_step) $status = 'approved';
            if ($step->sequence === $req->current_step && $req->status === 'REJECTED') $status = 'rejected';

            $roleSlug = $step->approver_snapshot_role_slug ?? 'approver';

            // The unified engine maintains action timestamps directly on the step
            $signedAt = in_array($status, ['approved', 'rejected']) ? $step->acted_at : null;

            return [
                'step_order'     => $step->sequence,
                'role_slug'      => $roleSlug,
                'label'          => ucwords(str_replace(['-', '_'], ' ', $roleSlug)),
                'status'         => $status,
                'is_current'     => $isCurrent,
                'approver_name'  => $step->approver_name, // fallback or resolved name
                'signed_at'      => $signedAt,
                'signature_path' => $step->signature_url, // Maps to getSignatureUrlAttribute()
                'approval_id'    => $step->id, // Use step ID for references
                'step_id'        => $step->id,
                'can_sign'       => $isCurrent && Auth::user()->hasRole($roleSlug),
            ];
        })->values()->toArray();
    }

    public function render()
    {
        return view('livewire.overtime.detail', [
            'timeline' => $this->timeline,
            'user'     => Auth::user(),
            'canPush'  => Auth::user()->can('pushToPayroll', OvertimeForm::class),
        ])->layout('new.layouts.app');
    }
}

