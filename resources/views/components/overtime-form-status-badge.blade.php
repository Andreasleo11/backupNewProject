@php
    /**
     * OvertimeFormStatusBadge component
     *
     * IMPORTANT: JIT-safe approach — all Tailwind class strings are complete literals,
     * never dynamically constructed (e.g. "bg-{{ $color }}-100" would be purged by JIT).
     *
     * Uses \App\Livewire\Overtime\Index::statusMeta() as the single source of truth
     * so the label map never drifts between badge and backend.
     */
    $meta = \App\Livewire\Overtime\Index::statusMeta($status ?? null);
@endphp

<span
    class="inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-[10px] font-black uppercase tracking-wide
             {{ $meta['classes'] }}">
    <i class="bx {{ $meta['icon'] }}"></i>
    {{ $meta['label'] }}
</span>
