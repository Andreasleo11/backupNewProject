<?php

namespace App\Livewire\Verification;

use App\Application\Verification\UseCases\ApproveReport;
use App\Application\Verification\UseCases\RejectReport;
use App\Application\Verification\UseCases\SubmitReport;
use App\Domain\Verification\Services\VerificationEmailService;
use App\Domain\Verification\Services\VerificationExportService;
use App\Infrastructure\Persistence\Eloquent\Models\VerificationReport;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\WithFileUploads;
use Livewire\Component;

class Show extends Component
{
    use AuthorizesRequests, WithFileUploads;

    public VerificationReport $report;

    public string $remarks = '';

    // Mail form fields — bound to mail-modal inputs via wire:model
    public string $mailTo      = '';
    public string $mailCc      = '';
    public string $mailSubject = '';
    public string $mailBody    = '';

    // --- Lifecycle ----------------------------------------------------------

    public function mount(VerificationReport $report): void
    {
        $this->report = $report->load(['items', 'items.defects', 'files', 'approvalRequest', 'approvalRequest.steps']);
    }

    private function refreshReport(): void
    {
        $this->report->refresh()->load(['items', 'items.defects', 'files', 'approvalRequest', 'approvalRequest.steps']);
        $this->remarks = '';
    }

    // --- Commands -----------------------------------------------------------

    public function submit(SubmitReport $useCase): void
    {
        // Only owner (and DRAFT) by policy/use case
        $this->authorize('update', $this->report);

        $useCase->handle(
            reportId: $this->report->id,
            actorId: auth()->id()
        );

        $this->dispatch('toast', body: 'Submitted for approval.');
        $this->refreshReport();
    }

    public function approve(ApproveReport $useCase): void
    {
        // Policy: approver permission (coarse). Engine also checks step ownership.
        $this->authorize('approve', $this->report);

        $useCase->handle(
            reportId: $this->report->id,
            actorId: auth()->id(),
            remarks: $this->remarks ?: null
        );

        $this->dispatch('toast', body: 'Approved.');
        $this->refreshReport();
    }

    public function reject(RejectReport $useCase): void
    {
        $this->authorize('reject', $this->report);

        $useCase->handle(
            reportId: $this->report->id,
            actorId: auth()->id(),
            remarks: $this->remarks ?: null
        );

        $this->dispatch('toast', body: 'Rejected.');
        $this->refreshReport();
    }

    public ?int $editDoItemId = null;
    public string $editDoNumber = '';

    public function startEditDoNumber(int $itemId): void
    {
        $this->authorize('update', $this->report);
        $item = $this->report->items->firstWhere('id', $itemId);
        if ($item) {
            $this->editDoItemId = $itemId;
            $this->editDoNumber = $item->do_number ?? '';
        }
    }

    public function cancelEditDoNumber(): void
    {
        $this->editDoItemId = null;
        $this->editDoNumber = '';
    }

    public function saveDoNumber(): void
    {
        $this->authorize('update', $this->report);
        
        $item = $this->report->items->firstWhere('id', $this->editDoItemId);
        if ($item) {
            $item->update(['do_number' => $this->editDoNumber]);
            $this->dispatch('toast', body: 'DO Number updated.');
        }

        $this->cancelEditDoNumber();
        $this->refreshReport();
    }

    // --- PDF / Email / Delete -----------------------------------------------

    public function exportPdf(VerificationExportService $service)
    {
        return $service->exportToPdf($this->report->id);
    }

    public function savePdf(VerificationExportService $service): void
    {
        $service->savePdf($this->report->id);
        $this->dispatch('toast', body: 'PDF saved to storage.');
    }

    public function sendEmail(VerificationEmailService $emailService, VerificationExportService $exportService): void
    {
        $this->authorize('update', $this->report);

        $emailService->sendEmail($this->report->id, [
            'to'      => $this->mailTo,
            'cc'      => $this->mailCc,
            'subject' => $this->mailSubject,
            'body'    => $this->mailBody,
        ], $exportService);

        $this->dispatch('toast', body: 'Email sent.');
        $this->dispatch('close-mail-modal');
    }

    // --- Computed Properties for Legacy / Adjust Form -----------------------

    public function getLegacyIdProperty(): ?int
    {
        return $this->report->meta['legacy_id'] ?? null;
    }

    public function getHasAdjustFormProperty(): bool
    {
        // ponytail: during transition, check both legacy and new report_id columns
        if ($this->legacyId) {
            return \App\Models\HeaderFormAdjust::where('report_id', $this->legacyId)->exists();
        }
        return \App\Models\HeaderFormAdjust::where('verification_report_id', $this->report->id)->exists();
    }

    public function getAreAllDoNumbersFilledProperty(): bool
    {
        if ($this->report->items->isEmpty()) return false;
        return ! $this->report->items->contains(fn($i) => empty($i->do_number));
    }

    // --- Rendering ----------------------------------------------------------

    public function render()
    {
        return view('livewire.verification.show');
    }
}
