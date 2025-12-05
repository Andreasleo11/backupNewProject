@php
    /** @var int|null $status */
    /** @var int|bool|null $isCancel */
    /** @var \App\Models\MonthlyBudgetReport|null $report */
    $badgeBase = 'inline-flex items-center rounded-full px-3 py-1 text-[11px] font-semibold';
@endphp

@if ((int) $isCancel === 1)
    <span class="{{ $badgeBase }} bg-rose-100 text-rose-700 ring-1 ring-rose-200">
        Canceled
    </span>

    @if (!empty($report?->cancel_reason))
        <button type="button"
            class="ml-1 inline-flex items-center rounded-full border border-slate-200 bg-white px-2 py-1 text-[11px] text-slate-600 hover:bg-slate-50"
            title="Cancel Reason: {{ $report->cancel_reason }}">
            <i class='bx bx-info-circle text-[0.9rem]'></i>
        </button>
    @endif
@else
    @switch((int) $status)
        @case(1)
            <span class="{{ $badgeBase }} bg-slate-100 text-slate-700 ring-1 ring-slate-200">
                Waiting Creator
            </span>
        @break

        @case(2)
            <span class="{{ $badgeBase }} bg-slate-100 text-slate-700 ring-1 ring-slate-200">
                Waiting Dept Head
            </span>
        @break

        @case(3)
            <span class="{{ $badgeBase }} bg-slate-100 text-slate-700 ring-1 ring-slate-200">
                Waiting Head Design
            </span>
        @break

        @case(4)
            <span class="{{ $badgeBase }} bg-amber-100 text-amber-800 ring-1 ring-amber-200">
                Waiting GM
            </span>
        @break

        @case(5)
            <span class="{{ $badgeBase }} bg-amber-100 text-amber-800 ring-1 ring-amber-200">
                Waiting Director
            </span>
        @break

        @case(6)
            <span class="{{ $badgeBase }} bg-emerald-100 text-emerald-800 ring-1 ring-emerald-200">
                Approved
            </span>
        @break

        @case(7)
            <span class="{{ $badgeBase }} bg-rose-100 text-rose-700 ring-1 ring-rose-200">
                Rejected
            </span>

            @if (!empty($report?->reject_reason))
                <button type="button"
                    class="ml-1 inline-flex items-center rounded-full border border-slate-200 bg-white px-2 py-1 text-[11px] text-slate-600 hover:bg-slate-50"
                    title="Reject Reason: {{ $report->reject_reason }}">
                    <i class='bx bx-info-circle text-[0.9rem]'></i>
                </button>
            @endif
        @break
    @endswitch
@endif
