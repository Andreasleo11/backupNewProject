{{-- resources/views/components/inspection-section.blade.php --}}
@props(['parent', 'id', 'title'])
<div class="accordion-item">
  <h2 class="accordion-header" id="hd-{{ $id }}">
    <button class="accordion-button collapsed" data-bs-toggle="collapse"
      data-bs-target="#cl-{{ $id }}" aria-expanded="false">
      {{ $title }}
    </button>
  </h2>
  <div id="cl-{{ $id }}" class="accordion-collapse collapse"
    data-bs-parent="#{{ $parent }}">
    <div class="accordion-body p-0">
      {{ $slot }}
    </div>
  </div>
</div>
