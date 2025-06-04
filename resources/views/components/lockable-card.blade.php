@props([
    'locked' => false, // true ➜ overlay + no pointer events
    'title', // card header text
    'overlay' => 'Locked',
])

<div {{ $attributes->class(['card', 'position-relative', $locked ? 'opacity-50' : '']) }}>
    <div class="card-body {{ $locked ? 'pe-none' : 'position-relative z-1 opacity-100' }} ">
        @isset($title)
            <h5 class="text-primary fw-bold mb-4 mt-1 pb-2 border-bottom">
                {!! $title !!}
            </h5>
        @endisset

        {{ $slot }}
    </div>
    @if ($locked)
        <div
            class="position-absolute top-0 start-0 w-100 h-100 d-flex
                    flex-column justify-content-center align-items-center
                    bg-light bg-opacity-75 text-center rounded z-20">
            <i class="bi bi-lock-fill fs-1 mb-2"></i>
            <span class="fw-semibold">
                {!! $overlay !!}
            </span>
        </div>
    @else
        <div
            class="pointer-events-none position-absolute top-0 start-0 w-100 h-100 d-flex flex-column justify-content-center align-items-center text-center rounded">
            <span class="text-secondary opacity-25" style="font-size: 2rem; z-index: 1">
                {!! $overlay !!}
            </span>
        </div>
    @endif
</div>
