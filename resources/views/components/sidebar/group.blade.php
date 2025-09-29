@props([
    'id', // unique collapse id, e.g. 'adminGroup'
    'icon' => null, // 'bx bx-bug'
    'title', // 'Admin'
    'open' => false, // bool: auto open when any child link is active
])

@php
    $linkClasses = 'sidebar-link has-dropdown ' . ($open ? '' : 'collapsed');
    $ulClasses = 'sidebar-dropdown list-unstyled collapse' . ($open ? ' show' : '');
@endphp

<li class="sidebar-item">
    <a href="#" class="{{ $linkClasses }}" data-bs-toggle="collapse" data-bs-target="#{{ $id }}"
        aria-expanded="{{ $open ? 'true' : 'false' }}" aria-controls="{{ $id }}">
        @if ($icon)
            <i class="{{ $icon }}"></i>
        @endif
        <span>{{ $title }}</span>
    </a>

    <ul id="{{ $id }}" class="{{ $ulClasses }}" data-bs-parent="#sidebar">
        {{ $slot }}
    </ul>
</li>
