<div class="modal fade" id="rejectModal">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="{{ route('pt.updaterevision', $id) }}" method="post">
        @method('PUT')
        @csrf
        <div class="modal-header">
          <h1 class="modal-title fs-5">Reason {{ $id }}</h1>
        </div>
        <div class="modal-body">
          <label for="remarks" class="form-label">Remarks</label>
          <textarea name="remarks" class="form-control" required></textarea>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Confirm</button>
        </div>
      </form>
    </div>
  </div>
</div>
