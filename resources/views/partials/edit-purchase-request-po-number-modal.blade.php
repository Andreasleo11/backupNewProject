<div class="modal fade" id="edit-purchase-request-po-number-{{ $pr->id }}">
  <div class="modal-dialog">
    <div class="modal-content text-start">
      <form action="{{ route('purchaserequest.update.ponumber', $pr->id) }}" method="post">
        @csrf
        @method('put')
        <div class="modal-header">
          <div class="modal-title h5">
            Edit PO Number for <strong>{{ $pr->doc_num }}</strong>
          </div>
          <button class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="po_number" class=" form-label">PO Number</label>
            <input id="po_number" type="text" class="form-control" value="{{ $pr->po_number }}"
              name="po_number">
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
