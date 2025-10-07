<?php

// app/Console/Commands/SendWeeklyComplianceDigest.php

namespace App\Console\Commands;

use App\Models\DepartmentComplianceSnapshot;
use App\Models\User;
use App\Notifications\Compliance\WeeklyComplianceDigest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class SendWeeklyComplianceDigest extends Command
{
    protected $signature = 'compliance:weekly-digest
        {--threshold=70 : Percent threshold}
        {--channel=both : mail|slack|both}
        {--to= : Comma-separated emails (optional). Defaults to all admins}
        {--limit=100 : Max rows to include in the digest (for very large orgs)}
    ';

    protected $description = 'Send a weekly digest listing all departments below a compliance threshold in one message.';

    public function handle(): int
    {
        $threshold = (int) $this->option('threshold');
        $limit = (int) $this->option('limit');
        $channel = strtolower($this->option('channel'));
        $toOpt = $this->option('to');

        // Pull low-compliance departments from snapshots
        $rows = DepartmentComplianceSnapshot::query()
            ->with('department:id,name,code')
            ->where('percent', '<', $threshold)
            ->orderBy('percent')
            ->limit($limit)
            ->get()
            ->map(fn ($s) => [
                'name' => $s->department->name,
                'code' => $s->department->code,
                'percent' => (int) $s->percent,
            ]);

        if ($rows->isEmpty()) {
            $this->info('No departments below threshold. Skipping send.');

            return self::SUCCESS;
        }

        // Choose recipients
        if ($toOpt) {
            $emails = collect(explode(',', $toOpt))
                ->map(fn ($v) => trim($v))
                ->filter()
                ->unique()
                ->values();

            // Look up users by email; fallback to route-notification for any email not in users
            $users = User::whereIn('email', $emails)->get();
            $routes = $emails->diff($users->pluck('email'))->values();

            // Build notifiables list: actual users + anonymous routes
            $notifiables = collect($users->all());
            foreach ($routes as $email) {
                $notifiables->push(
                    (new class($email)
                    {
                        public function __construct(public string $email) {}

                        public function routeNotificationForMail()
                        {
                            return $this->email;
                        }
                        // Add other channels if needed
                    })
                );
            }
        } else {
            // Default: all admins (adjust your column / scope)
            // $notifiables = User::where('is_admin', true)->get();
            $admin = \App\Models\User::where('email', 'raymond@daijo.co.id')->first();
            $yuli = \App\Models\User::where('email', 'yuli@daijo.co.id')->first();
            $notifiables = array_filter([$admin, $yuli]);
        }

        // Build notification
        $dashboardUrl = route('compliance.dashboard');
        $notification = new WeeklyComplianceDigest(collect($rows), $threshold, $dashboardUrl);

        // Send via chosen channel(s)
        switch ($channel) {
            case 'mail':
                Notification::send($notifiables, $notification->locale(app()->getLocale())->onQueue('mail'));
                break;
                // case 'slack':
                //     // Route-level: relies on each notifiable having routeNotificationForSlack() or set a global route below:
                //     foreach ($notifiables as $n) {
                //         Notification::route('slack', config('services.slack.webhook'))
                //             ->notify($notification);
                //     }
                //     break;
                // case 'both':
                // default:
                //     Notification::send($notifiables, $notification->locale(app()->getLocale())->onQueue('mail'));
                //     foreach ($notifiables as $n) {
                //         Notification::route('slack', config('services.slack.webhook'))
                //             ->notify($notification);
                //     }
                //     break;
        }

        $this->info('Weekly digest sent to '.count($notifiables).' recipient(s). Rows included: '.$rows->count().". Threshold: {$threshold}%.");

        return self::SUCCESS;
    }
}
