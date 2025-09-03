@props([
    'locked' => false,
    'title',
    'overlay' => 'Locked',
])

<div {{ $attributes->class(['card', 'position-relative', $locked ? 'opacity-50' : '']) }}>
  {{-- Body: clickable when unlocked, blocked when locked --}}
  <div class="card-body position-relative {{ $locked ? 'pe-none' : '' }} z-1">
    @isset($title)
      <h5 class="text-primary fw-bold mb-4 mt-1 pb-2 border-bottom">{!! $title !!}</h5>
    @endisset
    {{ $slot }}
  </div>

  @if ($locked)
    {{-- Real lock overlay: blocks clicks --}}
    <div
      class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column justify-content-center align-items-center
                    bg-light bg-opacity-75 text-center rounded z-3">
      <i class="bi bi-lock-fill fs-1 mb-2"></i>
      <span class="fw-semibold">{!! $overlay !!}</span>
    </div>
  @else
    {{-- Ghost watermark: behind content + cannot intercept clicks --}}
    <div
      class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column justify-content-center align-items-center
                    text-center rounded pe-none"
      style="z-index:0;">
      <span class="text-secondary opacity-25" style="font-size:2rem;">
        {!! $overlay !!}
      </span>
    </div>
  @endif
</div>
