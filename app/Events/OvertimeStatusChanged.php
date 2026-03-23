<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\HeaderFormOvertime;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OvertimeStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly HeaderFormOvertime $form,
    ) {}
}
