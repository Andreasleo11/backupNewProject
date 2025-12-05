@php
    use App\Enums\MonthlyBudgetSummaryStatus;

    $statusEnum = $status instanceof MonthlyBudgetSummaryStatus
        ? $status
        : MonthlyBudgetSummaryStatus::tryFrom((int) $status);

    $baseBadge = 'inline-flex items-center rounded-full px-3 py-1 text-[11px] font-medium';
@endphp

@if (!$statusEnum)
    <span class="{{ $baseBadge }} bg-slate-100 text-slate-500 ring-1 ring-slate-200">
        <i class="bx bx-help-circle mr-1 text-[0.95rem]"></i>
        Unknown
    </span>
@else
    {{-- Badge status utama --}}
    <span class="{{ $baseBadge }} {{ $statusEnum->badgeClasses() }}">
        <i class="{{ $statusEnum->icon() }} mr-1 text-[0.95rem]"></i>
        {{ $statusEnum->label() }}
    </span>

    {{-- Tooltip alasan reject / cancel --}}
    @if ($statusEnum->hasTooltipReason())
        @php
            $reason = $statusEnum === MonthlyBudgetSummaryStatus::CANCELLED
                ? ($report->cancel_reason ?? '-')
                : ($report->reject_reason ?? '-');
        @endphp

        <button
            type="button"
            data-bs-toggle="tooltip"
            data-bs-title="{{ $statusEnum->tooltipLabel() }}: {{ $reason }}"
            class="inline-flex items-center ml-2 rounded-md border border-slate-300 bg-white px-2 py-1
                   text-[10px] text-slate-600 shadow-sm hover:bg-slate-50"
        >
            <i class="bx bx-info-circle text-[0.9rem]"></i>
        </button>
    @endif
@endif
