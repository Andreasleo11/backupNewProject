@if ($po->status === 1)
    <span class="badge text-bg-warning px-3 py-2 fs-6">Waiting</span>
@elseif ($po->status === 2)
    <span class="badge text-bg-success px-3 py-2 fs-6">Approved</span>
@elseif ($po->status === 3)
    <span class="badge text-bg-danger px-3 py-2 fs-6">Rejected</span>
@endif
