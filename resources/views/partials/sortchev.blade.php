{{-- resources/views/partials/sortchev.blade.php --}}
@props(['field'])
<i
    class="bi ms-1 {{ $sort === $field ? ($dir === 'asc' ? 'bi-caret-up-fill' : 'bi-caret-down-fill') : 'bi-arrow-down-up text-muted' }}">
</i>
