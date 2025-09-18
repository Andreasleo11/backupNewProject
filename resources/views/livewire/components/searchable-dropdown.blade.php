<div>
  <div x-data="{ open: false }" class="position-relative" x-cloak>
    <label class="form-label">{!! $labelHtml ?? $label !!}</label>
    <input type="text" class="form-control @if($hasError) is-invalid @endif @if($isSaved) is-valid @endif"
      placeholder="{{ $placeholder }}" wire:model.live="search" @keydown="open = true"
      @click.outside="open = false" value="{{ $value }}"/>
  
    <ul class="list-group position-absolute w-100 shadow"
      style="z-index: 1000; max-height: 200px; overflow-y: auto;"
      x-on:scroll.passive="
                if($el.scrollTop + $el.clientHeight >= $el.scrollHeight - 10) {
                    $wire.loadMore();
                }"
      x-show="open" x-transition>
      @foreach ($results as $item)
        <li class="list-group-item list-group-item-action"
          wire:click="select('{{ addslashes($item) }}')" @click="open = false">
          {{ $item }}
        </li>
      @endforeach

      @if (!$hasMore && count($results) === 0)
        <li class="list-group-item text-muted">No results found.</li>
      @endif
      @if ($hasMore)
        <li class="list-group-item text-center text-muted">Loading more...</li>
      @endif
    </ul>
  </div>
</div>
