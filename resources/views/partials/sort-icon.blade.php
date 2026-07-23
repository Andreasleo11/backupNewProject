@php
    $active = $sortField === $field;
    $dir = $direction ?? 'asc';
@endphp
@if ($active)
    <x-icon :name="$dir === 'asc' ? 'bx-sort-up' : 'bx-sort-down'" class="ms-1 align-middle" />
@else
    <x-bx-sort class="ms-1 small align-middle text-muted" />
@endif
