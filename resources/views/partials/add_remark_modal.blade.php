<div class="modal fade" id="add-remark-modal-{{ $detail->id }}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Add FG Warehouse</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
            <form method="POST" action="{{ route('addremarkadjust') }}">
                    @csrf
                    <input type="hidden" name="detail_id" value="{{ $detail->id }}">
                    
                    <div class="mb-3">
                        <label for="remark" class="form-label">Remark</label>
                        <input type="text" class="form-control" id="remark" name="remark" placeholder="Enter remark">
                    </div>


                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
                </form>
            </div>
        </div>
    </div>
</div>