<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class OvertimeFormStatusBadge extends Component
{
    public string $status;

    // status => [label, tailwindColorName]
    public const MAP = [
        'waiting-creator'     => ['Waiting Creator', 'sky'],
        'waiting-dept-head'   => ['Waiting Dept Head', 'amber'],
        'waiting-verificator' => ['Waiting Verificator', 'amber'],
        'waiting-gm'          => ['Waiting GM', 'amber'],
        'waiting-director'    => ['Waiting Director', 'amber'],
        'waiting-supervisor'  => ['Waiting Supervisor', 'indigo'],
        'approved'            => ['Approved', 'emerald'],
        'rejected'            => ['Rejected', 'rose'],
    ];

    public function __construct(string $status)
    {
        $this->status = $status;
    }

    public function render(): View|Closure|string
    {
        [$label, $color] = self::MAP[$this->status] ?? ['Unknown', 'slate'];

        return view('components.overtime-form-status-badge', compact('label', 'color'));
    }
}
