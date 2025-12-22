@props([
    'variant' => 'neutral', // success, danger, warning, neutral, dark, info, dll
])

@php
    $base = 'inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-wide';

    $variants = [
        'success' => 'bg-emerald-100 text-emerald-700',
        'danger'  => 'bg-rose-100 text-rose-700',
        'warning' => 'bg-amber-100 text-amber-700',
        'neutral' => 'bg-slate-100 text-slate-700',
        'dark'    => 'bg-slate-800 text-slate-50',
        'info'    => 'bg-sky-100 text-sky-700',
    ];

    $classes = $base . ' ' . ($variants[$variant] ?? $variants['neutral']);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>
