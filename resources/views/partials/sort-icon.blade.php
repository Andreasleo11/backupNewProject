@php
    $active = $sortField === $field;
    $dir = $direction ?? 'asc';
@endphp
@if ($active)
    <i class="bx {{ $dir === 'asc' ? 'bx-sort-up' : 'bx-sort-down' }} ms-1 small align-middle"></i>
@else
    <i class="bx bx-sort ms-1 small align-middle text-muted"></i>
@endif
