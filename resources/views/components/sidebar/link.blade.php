@props([
    'href' => '#',
    'icon' => null,
    'active' => false,
    'badge' => null,
])

@php
    // Plain text label from slot for title/aria-label
    $label = trim(preg_replace('/\s+/', ' ', strip_tags($slot)));
@endphp

<a
    {{ $attributes->merge([
        'href' => $href,
        'class' => 'sidebar-link ' . ($active ? 'active' : ''),
        'title' => $label,
        'aria-label' => $label,
    ]) }}>
    @if ($icon)
        <i class="{{ $icon }}"></i>
    @endif
    <span>{{ $slot }}</span>
    @if ($badge)
        <span class="badge bg-primary ms-auto">{{ $badge }}</span>
    @endif
</a>
