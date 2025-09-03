<div class="modal fade" tabindex="-1"id="edit-do-number-{{ $detail->id }}">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="{{ route('update.do.number', $detail->id) }}">
        @csrf
        @method('PUT')
        <div class="modal-header">
          <h5 class="modal-title">Edit DO Number for <strong>{{ $detail->part_name }}</strong> </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"
            aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="name" class="form-label">Do Number</label>
            <input type="text" class="form-control" name="do_num" value="{{ $detail->do_num }}"
              required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>
