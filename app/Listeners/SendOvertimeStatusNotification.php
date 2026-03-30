<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\OvertimeStatusChanged;
use App\Domain\Overtime\Models\OvertimeForm;
use App\Models\User;
use App\Notifications\FormOvertimeNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Queued listener: sends email notification to the next approver
 * when an overtime form's status changes.
 *
 * Uses the approval flow (role_slug on current step) to determine
 * the recipient — no hardcoded names or emails.
 */
class SendOvertimeStatusNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(OvertimeStatusChanged $event): void
    {
        $form = $event->form->loadMissing(['user', 'department', 'approvalRequest.steps']);

        // Terminal states: notify the creator (and optionally verificators).
        if (in_array($form->status, ['approved', 'rejected'], true)) {
            $this->notifyCreator($form);
            return;
        }

        // Determine the next approver via role_slug on the current step.
        $req = $form->approvalRequest;
        if (! $req || $req->status !== 'IN_REVIEW') {
            return;
        }

        $currentStep = $req->steps->where('sequence', $req->current_step)->first();
        if (! $currentStep) {
            return;
        }

        $roleSlug = $currentStep->approver_snapshot_role_slug ?? $currentStep->role_slug ?? '';
        if (! $roleSlug) {
            return;
        }

        // Find users who hold this role.
        $recipients = User::role($roleSlug)
            ->when(
                // GM: scope to the form's branch if a branch attribute exists on the user.
                $roleSlug === 'general-manager',
                fn ($q) => $q->where('branch', $form->branch),
            )
            ->get();

        foreach ($recipients as $recipient) {
            $recipient->notify(new FormOvertimeNotification($form, $this->buildDetails($form)));
        }
    }

    private function notifyCreator(OvertimeForm $form): void
    {
        $creator = $form->user;
        if ($creator) {
            $creator->notify(new FormOvertimeNotification($form, $this->buildDetails($form)));
        }
    }

    private function buildDetails(OvertimeForm $form): array
    {
        $status = ucwords(str_replace('-', ' ', $form->status));
        $formattedDate = $form->created_at?->format('d-m-Y') ?? '-';
        $appUrl = config('app.url', 'http://localhost');

        return [
            'greeting' => 'Form Overtime Notification',
            'body'     => implode('<br>', [
                "We waiting for your sign for this report:",
                "- Report ID : {$form->id}",
                "- Department : {$form->department?->name}",
                "- Create Date: {$formattedDate}",
                "- Created By : {$form->user?->name}",
            "- Status     : {$status}",
            ]),
            'actionText' => 'View Detail',
            'actionURL'  => route('overtime.detail', $form->id),
        ];
    }
}

