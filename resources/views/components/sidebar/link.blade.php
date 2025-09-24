@props([
  'href' => '#',
  'icon' => null,          // e.g. 'bx bx-file'
  'active' => false,       // pass: request()->routeIs('route.name*')
  'badge' => null,         // optional small badge text
])

<a {{ $attributes->merge([
      'href' => $href,
      'class' => 'sidebar-link ' . ($active ? 'active' : ''),
    ]) }}>
  @if($icon)
    <i class="{{ $icon }}"></i>
  @endif
  <span>{{ $slot }}</span>
  @if($badge)
    <span class="badge bg-primary ms-auto">{{ $badge }}</span>
  @endif
</a>