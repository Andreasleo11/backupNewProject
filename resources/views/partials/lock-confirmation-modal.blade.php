<div class="modal fade" id="lock-confirmation-modal-{{ $id }}">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="{{ $route }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">{{ $title }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"
            aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="filter_month" id="filter-month-input">
          {!! $body !!}
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger"><i class='bx bxs-lock'></i> Lock</button>
        </div>
      </form>
    </div>
  </div>
</div>
