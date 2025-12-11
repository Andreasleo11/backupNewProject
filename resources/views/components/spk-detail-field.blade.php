@props([
    'label',
    'value' => '-',
])

<div class="space-y-1.5">
    <span class="block text-xs font-semibold text-slate-700">
        {{ $label }}
    </span>
    <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-800">
        {{ $value ?: '-' }}
    </div>
</div>
