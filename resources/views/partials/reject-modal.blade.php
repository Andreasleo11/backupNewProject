<div class="modal fade" id="rejectModal">
    <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{route('director.qaqc.reject', $id)}}" method="post">
                    @method('PUT')
                    @csrf
                    <div class="modal-header">
                        <h1 class="modal-title fs-5">Reason {{ $id }}</h1>
                    </div>
                    <div class="modal-body">
                        <label for="description" class="form-label">Description</label>
                        <textarea name="description" class="form-control" placeholder="Tell us why you rejecting this report..." required></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Confirm</button>
                    </div>
                </form>
            </div>
    </div>
</div>
