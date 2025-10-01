<div class="modal fade" id="edit-monthly-budget-report-detail-{{ $detail->id }}">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('monthly.budget.report.detail.update', $detail->id) }}" method="post">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Detail for <strong>{{ $detail->name }}</strong></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-start pb-3 px-4">
                    <div class="form-group">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" value="{{ $detail->name }}">
                    </div>
                    @if ($report->department->name === 'MOULDING')
                        <div class="form-group mt-3">
                            <label for="spec" class="form-label">Spec</label>
                            <input type="text" name="spec" class="form-control" value="{{ $detail->spec }}">
                        </div>
                    @endif
                    <div class="form-group mt-3">
                        <label for="uom" class="form-label">UoM</label>
                        <input type="text" name="uom" class="form-control" value="{{ $detail->uom }}">
                    </div>
                    @if ($report->department->name === 'MOULDING')
                        <div class="form-group mt-3">
                            <label for="last_recorded_stock" class="form-label">Last Recorded Stock</label>
                            <input type="number" name="last_recorded_stock" class="form-control"
                                value="{{ $detail->last_recorded_stock }}">
                        </div>
                        <div class="form-group mt-3">
                            <label for="usage_per_month" class="form-label">Usage Per Month</label>
                            <input type="text" name="usage_per_month" class="form-control"
                                value="{{ $detail->usage_per_month }}">
                        </div>
                    @endif
                    <div class="form-group mt-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="text" name="quantity" class="form-control" value="{{ $detail->quantity }}">
                    </div>
                    <div class="form-group mt-3">
                        <label class="form-label" for="remark">Remark</label>
                        <textarea name="remark" id="remark" cols="30" rows="3" class="form-control">{{ $detail->remark }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
