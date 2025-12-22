<?php

namespace App\Livewire\Verification;

use App\Application\Verification\UseCases\ApproveReport;
use App\Application\Verification\UseCases\RejectReport;
use App\Application\Verification\UseCases\SubmitReport;
use App\Infrastructure\Persistence\Eloquent\Models\VerificationReport;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class Show extends Component
{
    use AuthorizesRequests;

    public VerificationReport $report;

    public string $remarks = '';

    // --- Lifecycle ----------------------------------------------------------

    public function mount(VerificationReport $report): void
    {
        $this->report = $report->load(['items', 'items.defects']);
    }

    private function refreshReport(): void
    {
        $this->report->refresh()->load(['items', 'items.defects']);
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
        $this->authorize('approve', $this->report);

        $useCase->handle(
            reportId: $this->report->id,
            actorId: auth()->id(),
            remarks: $this->remarks ?: null
        );

        $this->dispatch('toast', body: 'Rejected.');
        $this->refreshReport();
    }

    // --- Rendering ----------------------------------------------------------

    public function render()
    {
        return view('livewire.verification.show');
    }
}
