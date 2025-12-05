@php
  $sev = strtoupper($severity ?? '');
  $map = [
    'LOW' => 'success',
    'MEDIUM' => 'warning',
    'HIGH' => 'danger',
  ];
  $cls = $map[$sev] ?? 'secondary';
@endphp
<span class="badge text-bg-{{ $cls }}">{{ $sev ?: '—' }}</span>
