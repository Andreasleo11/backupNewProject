<?php

namespace App\Enums;

enum MonthlyBudgetSummaryStatus: int
{
    case WAITING_CREATOR = 1;
    case WAITING_GM = 2;
    case WAITING_DEPT_HEAD = 3;
    case WAITING_DIRECTOR = 4;
    case APPROVED = 5;
    case REJECTED = 6;
    case CANCELLED = 7;

    public function label(): string
    {
        return match ($this) {
            self::WAITING_CREATOR => 'Waiting Creator',
            self::WAITING_GM => 'Waiting for GM',
            self::WAITING_DEPT_HEAD => 'Waiting for Dept Head',
            self::WAITING_DIRECTOR => 'Waiting for Director',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function icon(): string
    {
        // boxicons class
        return match ($this) {
            self::CANCELLED => 'bx-block',
            self::REJECTED => 'bx-x-circle',
            self::APPROVED => 'bx-check-circle',
            self::WAITING_DIRECTOR => 'bx-user-voice',
            self::WAITING_DEPT_HEAD => 'bx-user-circle',
            self::WAITING_GM => 'bx-user-check',
            self::WAITING_CREATOR => 'bx-edit-alt',
        };
    }

    public function badgeClasses(): string
    {
        // Tailwind badge warna
        return match ($this) {
            self::CANCELLED, self::REJECTED => 'bg-rose-50 text-rose-700 ring-1 ring-rose-200',

            self::APPROVED => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200',

            self::WAITING_DIRECTOR => 'bg-amber-50 text-amber-700 ring-1 ring-amber-200',

            self::WAITING_DEPT_HEAD => 'bg-sky-50 text-sky-700 ring-1 ring-sky-200',

            self::WAITING_GM => 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-200',

            self::WAITING_CREATOR => 'bg-slate-100 text-slate-700 ring-1 ring-slate-200',
        };
    }

    public function hasTooltipReason(): bool
    {
        return in_array($this, [self::REJECTED, self::CANCELLED], true);
    }

    public function tooltipLabel(): string
    {
        return match ($this) {
            self::CANCELLED => 'Cancel Reason',
            self::REJECTED => 'Reject Reason',
            default => '',
        };
    }
}
