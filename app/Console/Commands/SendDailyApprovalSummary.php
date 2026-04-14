<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendDailyApprovalSummary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-approval-summary';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // 1. Fetch all users who could potentially need a daily summary
        // (Either global daily mode/both or has any module overrides)
        $users = \App\Infrastructure\Persistence\Eloquent\Models\User::whereIn('email_notification_mode', ['daily_summary', 'both'])
            ->orWhereNotNull('notification_preferences')
            ->get();

        $this->info("Scanning " . $users->count() . " potential users for daily summaries.");

        foreach ($users as $user) {
            // 2. Fetch all requests where it is currently this user's turn to take action
            $allActionable = \App\Infrastructure\Persistence\Eloquent\Models\ApprovalRequest::forUser($user)
                ->where('status', 'IN_REVIEW')
                ->with(['steps', 'approvable'])
                ->whereHas('steps', function ($sq) use ($user) {
                    $roleIds = $user->roles->pluck('id')->toArray();
                    $roleNames = $user->getRoleNames()->toArray();

                    $sq->whereColumn('sequence', 'approval_requests.current_step')
                       ->where(function ($match) use ($user, $roleIds, $roleNames) {
                           $match->where(function ($uMatch) use ($user) {
                               $uMatch->where('approver_type', 'user')
                                      ->where('approver_id', $user->id);
                           })->orWhere(function ($rMatch) use ($roleIds, $roleNames) {
                               $rMatch->where('approver_type', 'role')
                                      ->where(function ($q) use ($roleIds, $roleNames) {
                                          if (!empty($roleIds)) {
                                              $q->whereIn('approver_id', $roleIds);
                                          }
                                          if (!empty($roleNames)) {
                                              $q->orWhereIn('approver_id', $roleNames);
                                          }
                                      });
                           });
                       });
                })
                ->get();

            // 3. Filter these requests by the resolved notification preference for their specific module
            $scopingManager = app(\App\Infrastructure\Approval\Services\ApprovalScopingManager::class);

            $summaryRequests = $allActionable->filter(function ($request) use ($user, $scopingManager) {
                if (!$request->approvable) return false;

                // 1. Jurisdictional Scoping
                // Verify the user has the right to approve THIS specific request based on department boundaries.
                $step = $request->steps->firstWhere('sequence', (int) $request->current_step);
                
                if ($step && $step->approver_type === 'role') {
                    $roleSlug = $step->approver_snapshot_role_slug;
                    if (! $scopingManager->isUserEligible($user, $roleSlug, $request->approvable)) {
                        return false;
                    }
                }
                
                // 2. Personal Notification Preferences Check
                $moduleClass = get_class($request->approvable);
                return $scopingManager->wantsNotification($user, $moduleClass, 'daily_summary');
            });

            if ($summaryRequests->isNotEmpty()) {
                $user->notify(new \App\Notifications\ApprovalSummaryNotification($summaryRequests));
                $this->info("Sent summary to {$user->name} ({$summaryRequests->count()} modules).");
            }
        }

        $this->info("Daily approval summary process completed.");
    }
}
