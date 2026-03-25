<?php

namespace App\Livewire\Overtime;

use App\Domain\Overtime\Services\OvertimeApprovalService;
use App\Domain\Overtime\Models\OvertimeFormDetail;
use App\Domain\Overtime\Models\OvertimeForm;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('new.layouts.app')]
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

        $this->authorize('view', $this->form);
    }

    // ── Approval actions ─────────────────────────────────────────────────────

    public function sign(int $stepId): void
    {
        $this->authorize('approve', $this->form);

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
        $this->authorize('reject', $this->form);
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
        $this->authorize('pushToPayroll', $this->form);

        $detail = OvertimeFormDetail::with('employee', 'header')->findOrFail($detailId);
        $service = app(\App\Domain\Overtime\Services\OvertimeJPayrollService::class);
        $result = $service->pushSingleDetail($detail, $action);

        $this->pushSuccess = $result['success'];
        $this->pushMessage = $result['message'];
        $this->loadForm();
    }

    public function pushAll(): void
    {
        $this->authorize('pushToPayroll', $this->form);

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
            // Read status DIRECTLY from the step row (engine stores APPROVED/REJECTED/PENDING).
            // Do NOT derive from sequence < current_step — that breaks for the final step
            // because current_step is never advanced beyond the last sequence number.
            $rawStatus = strtolower($step->status ?? 'pending'); // normalise to lowercase for view logic

            // Map engine statuses to view statuses
            $status = match ($rawStatus) {
                'approved' => 'approved',
                'rejected' => 'rejected',
                'canceled' => 'rejected', // treat cancelled as rejected visually
                default    => 'pending',
            };

            $isCurrent = $req->current_step === $step->sequence && $req->status === 'IN_REVIEW';

            $roleSlug = $step->approver_snapshot_role_slug ?? 'approver';

            // signed_at and signature are populated by the engine only when APPROVED/REJECTED
            $signedAt = in_array($status, ['approved', 'rejected']) ? $step->acted_at : null;

            return [
                'step_order'     => $step->sequence,
                'role_slug'      => $roleSlug,
                'label'          => $step->approver_label ?? ucwords(str_replace(['-', '_'], ' ', $roleSlug)),
                'status'         => $status,
                'is_current'     => $isCurrent,
                'approver_name'  => $step->approver_name,
                'signed_at'      => $signedAt,
                'signature_path' => $step->signature_url,
                'approval_id'    => $step->id,
                'step_id'        => $step->id,
                'can_sign'       => $isCurrent && Auth::user()->can('approve', $this->form),
            ];
        })->values()->toArray();
    }

    public function render()
    {
        return view('livewire.overtime.detail', [
            'timeline'  => $this->timeline,
            'user'      => Auth::user(),
            'canPush'   => Auth::user()->can('pushToPayroll', $this->form),
            'canReview' => Auth::user()->can('reviewDetail', $this->form),
        ]);
    }
}

