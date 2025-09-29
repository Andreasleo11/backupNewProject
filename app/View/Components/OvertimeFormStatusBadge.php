<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class OvertimeFormStatusBadge extends Component
{
    public string $status;

    // Centralised definition
    public const MAP = [
        'waiting-creator' => ['Waiting Creator', 'primary-subtle', 'primary'],
        'waiting-dept-head' => ['Waiting Dept Head', 'warning', 'dark'],
        'waiting-verificator' => ['Waiting Verificator', 'warning', 'dark'],
        'waiting-gm' => ['Waiting GM', 'warning', 'dark'],
        'waiting-director' => ['Waiting Director', 'warning', 'dark'],
        'waiting-supervisor' => ['Waiting Supervisor', 'info', 'dark'],
        'approved' => ['Approved', 'success', 'white'],
        'rejected' => ['Rejected', 'danger', 'white'],
    ];

    public function __construct(string $status)
    {
        $this->status = $status;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        [$label, $bg, $text] = self::MAP[$this->status] ?? ['Unknown', 'secondary', 'light'];

        return view('components.overtime-form-status-badge', compact('label', 'bg', 'text'));
    }
}
