<div class="modal fade" id="edit-purchase-request-received-{{ $detail->id }}">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="{{ route('purchaserequest.update.receivedQuantity', $detail->id) }}"
        method="post">
        @csrf
        <div class="modal-header">
          <div class="modal-title h5">
            Edit Received Quantity
          </div>
          <button class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <span id="rangeValue" class="h4">{{ $detail->received_quantity }}</span>
            <input type="range" class="form-range" min="0" max="{{ $detail->quantity }}"
              onchange="rangeSlide(this.value)" onmousemove="rangeSlide(this.value)"
              value="{{ $detail->received_quantity }}" name="received_quantity">
            <script>
              function rangeSlide(value) {
                document.getElementById('rangeValue').innerHTML = value;
              }
            </script>
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
