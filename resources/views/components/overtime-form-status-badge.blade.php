@props(['label', 'color'])

<span
    class="inline-flex items-center rounded-full border px-3 py-1.5 text-xs font-semibold
           bg-{{ $color }}-100 text-{{ $color }}-800 border-{{ $color }}-200">
    {{ $label }}
</span>
