@if ($fot->is_approve === 0)
    <span class="badge px-3 py-2 fs-6 bg-danger">Rejected</span>
@elseif ($fot->is_approve === 1)
    <span class="badge px-3 py-2 fs-6 bg-success">Approved</span>
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
