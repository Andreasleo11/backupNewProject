<div class="modal fade" id="ask-a-revision-modal-{{ $report->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('spk.revision', $report->id) }}">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Ask A Revision</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Revision Reason</label>
                        <textarea class="form-control" name="revision_reason" id="revision_reason" cols="30" rows="5"
                            placeholder="e.g. Layar laptop kembali bluescreen dan tidak bisa dinyalakan walaupun sudah dicoba dicabut colok"
                            required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>
