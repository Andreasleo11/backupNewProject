<?php

// app/Infrastructure/Events/ReportStatusChanged.php

namespace App\Infrastructure\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class ReportStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public int $reportId,
        public ?string $from,
        public string $to
    ) {}

    public function broadcastOn()
    {
        return ['verification.reports'];
    } // or private channel per user

    public function broadcastAs()
    {
        return 'ReportStatusChanged';
    }
}
