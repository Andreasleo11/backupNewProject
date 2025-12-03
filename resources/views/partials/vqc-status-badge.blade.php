@php
    use Illuminate\Support\Facades\Date;

    $hoursDifference = $report->rejected_at
        ? Date::now()->diffInHours($report->rejected_at)
        : null;
@endphp

@if ($report->is_approve === 1)
    {{-- APPROVED --}}
    <x-status-pill variant="success">
        APPROVED
    </x-status-pill>

@elseif($report->is_approve === 0)
    {{-- REJECTED --}}
    <x-status-pill variant="danger">
        REJECTED
    </x-status-pill>

    @if ($report->is_locked)
        {{-- Lock icon kecil di samping --}}
        <span
            class="ml-1 inline-flex h-7 w-7 items-center justify-center 
                   rounded-full bg-slate-800 text-slate-50 text-xs">
            <i class='bx bxs-lock-alt text-[0.8rem]'></i>
        </span>
    @endif

@elseif($report->rejected_at && $hoursDifference !== null && $hoursDifference < 24)
    @if ($report->autograph_3 != null)
        {{-- Revisi sudah di-approve atasan, tunggu final approval --}}
        <x-status-pill variant="warning">
            WAITING ON APPROVAL
        </x-status-pill>
    @else
        {{-- Status revisi --}}
        <x-status-pill variant="neutral">
            REVISION
        </x-status-pill>

        @if ($report->has_been_emailed)
            <span
                class="ml-1 inline-flex h-7 w-7 items-center justify-center 
                       rounded-full bg-slate-100 text-slate-500">
                <i class='bx bx-mail-send text-[0.9rem]'></i>
            </span>
        @endif
    @endif

@elseif(($report->autograph_1 || $report->autograph_2) && $report->autograph_3)
    {{-- Sudah ada tanda tangan, menunggu approve final --}}
    <x-status-pill variant="warning">
        WAITING ON APPROVAL
    </x-status-pill>

@else
    {{-- Menunggu tanda tangan awal --}}
    <x-status-pill variant="neutral">
        WAITING SIGNATURE
    </x-status-pill>

    @if ($report->has_been_emailed)
        <span
            class="ml-1 inline-flex h-7 w-7 items-center justify-center 
                   rounded-full bg-slate-100 text-slate-500">
            <i class='bx bx-mail-send text-[0.9rem]'></i>
        </span>
    @endif
@endif
