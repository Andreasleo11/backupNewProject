<div class="card shadow h-100 pt-2 btn btn-light text-start"
  style="border-left: 3px solid {{ $attributes->get('color', '') }}">
  <div class="card-body">
    <div class="row no-gutters align-items-center">
      <div class="col mr-2">
        <div class="fs-5 fw-bold text-uppercase mb-1 {{ $attributes->get('titleColor', '') }}">
          {{ $attributes->get('title', '') }}
        </div>
        <div class="h4 mb-0 fw-bold text-secondary">
          {{ $attributes->get('content', '') }}
        </div>
      </div>
      <div class="col-auto">
        {!! $attributes->get('icon', '') !!}
      </div>
    </div>
    <div class="text-secondary fw-lighter mt-2">Click to view</div>
  </div>
</div>
