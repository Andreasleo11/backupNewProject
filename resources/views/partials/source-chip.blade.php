@php
  $src = strtoupper($source ?? '');
  $icon = [
    'CUSTOMER' => 'bi-person',
    'DAIJO' => 'bi-building',
    'SUPPLIER' => 'bi-truck',
  ][$src] ?? 'bi-question-circle';
@endphp
<span class="badge text-bg-light border">
  <i class="bi {{ $icon }}"></i>
  {{ $src ?: '—' }}
</span>
