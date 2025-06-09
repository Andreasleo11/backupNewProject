@if ($fot->is_approve === 0)
    <span class="badge px-3 py-2 fs-6 bg-danger">Rejected</span>
@elseif ($fot->is_approve === 1)
    <div class="d-flex justify-content-center align-items-center">

        <span class="badge px-3 py-2 fs-6 bg-success">Approved</span>
        @if ($fot->is_export)
            <i class='bx bxs-cloud-download text-success bx-md'></i>
        @endif
    </div>
@else
    <x-overtime-form-status-badge :status="$fot->status" />
@endif
