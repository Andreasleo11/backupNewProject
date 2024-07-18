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
    @switch($fot->status)
        @case(1)
            <span class="badge px-3 py-2 fs-6 bg-warning text-dark">Waiting for Dept Head</span>
        @break

        @case(2)
            <span class="badge px-3 py-2 fs-6 bg-warning text-dark">Waiting for Verificator</span>
        @break

        @case(3)
            <span class="badge px-3 py-2 fs-6 bg-warning text-dark">Waiting for GM</span>
        @break

        @case(9)
            <span class="badge px-3 py-2 fs-6 bg-warning text-dark">Waiting Director</span>
        @break

        @case(6)
            <span class="badge px-3 py-2 fs-6 bg-info text-dark">Waiting for Supervisor</span>
        @break

        @default
            <span class="badge px-3 py-2 fs-6 bg-secondary">Unknown</span>
    @endswitch
@endif
