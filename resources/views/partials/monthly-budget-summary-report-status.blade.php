@if ($report->approved_autograph)
    <span class="badge text-bg-success px-3 py-2 fs-6">Approved</span>
@elseif($report->is_known_autograph)
    <span class="badge text-bg-warning px-3 py-2 fs-6">Waiting for Director</span>
@elseif($report->created_autograph)
    <span class="badge text-bg-secondary px-3 py-2 fs-6">Waiting for GM</span>
@elseif(!$report->created_autograph)
    <span class="badge text-black-50 bg-primary-subtle px-3 py-2 fs-6">Waiting Creator</span>
@endif
