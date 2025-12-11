@php
    $map = [
        0 => ['label' => 'WAITING CREATOR',    'class' => 'bg-slate-50 text-slate-700 ring-slate-200'],
        1 => ['label' => 'WAITING DEPT HEAD',  'class' => 'bg-amber-50 text-amber-800 ring-amber-200'],
        6 => ['label' => 'WAITING PPIC',       'class' => 'bg-amber-50 text-amber-800 ring-amber-200'],
        2 => ['label' => 'WAITING ADMIN',      'class' => 'bg-sky-50 text-sky-800 ring-sky-200'],
        3 => ['label' => 'IN PROGRESS',        'class' => 'bg-indigo-50 text-indigo-800 ring-indigo-200'],
        4 => ['label' => 'DONE',               'class' => 'bg-emerald-50 text-emerald-800 ring-emerald-200'],
        5 => ['label' => 'FINISH',             'class' => 'bg-emerald-50 text-emerald-800 ring-emerald-200'],
    ];

    $cfg = $map[$status] ?? ['label' => 'UNKNOWN', 'class' => 'bg-slate-100 text-slate-600 ring-slate-200'];
@endphp

<div class="inline-flex items-center gap-1">
    <span
        class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-semibold ring-1 {{ $cfg['class'] }}">
        {{ $cfg['label'] }}
    </span>

    @if ($is_urgent)
        <span
            class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2 py-0.5 text-[10px] font-semibold text-red-700">
            <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd"
                      d="M10 2a1 1 0 0 1 .894.553l7 14A1 1 0 0 1 17 18H3a1 1 0 0 1-.894-1.447l7-14A1 1 0 0 1 10 2Zm0 4a.75.75 0 0 0-.75.75v4.5a.75.75 0 0 0 1.5 0v-4.5A.75.75 0 0 0 10 6Zm0 8a.875.875 0 1 0 0-1.75.875.875 0 0 0 0 1.75Z"
                      clip-rule="evenodd" />
            </svg>
            <span>Urgent</span>
        </span>
    @endif
</div>
