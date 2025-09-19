@if ($isCancel === 1)
  <span class="badge text-bg-danger px-3 py-2 fs-6">Canceled</span>
  <button data-bs-toggle="tooltip" data-bs-title="Reject Reason: {{ $report->cancel_reason ?? '-' }}"
    class="btn btn-secondary btn-sm align-items-center">
    <i class='bx bx-info-circle'></i></button>
@else
  @if ($status === 1)
    <span class="badge text-black-50 bg-primary-subtle px-3 py-2 fs-6">Waiting Creator</span>
  @elseif($status === 2)
    <span class="badge text-bg-secondary px-3 py-2 fs-6">Waiting Dept Head</span>
  @elseif($status === 3)
    <span class="badge text-bg-secondary px-3 py-2 fs-6">Waiting Head Design</span>
  @elseif($status === 4)
    <span class="badge text-bg-warning px-3 py-2 fs-6">Waiting GM</span>
  @elseif($status === 5)
    <span class="badge text-bg-warning px-3 py-2 fs-6">Waiting Director</span>
  @elseif($status === 6)
    <span class="badge text-bg-success px-3 py-2 fs-6">Approved</span>
  @elseif($status === 7)
    <span class="badge text-bg-danger px-3 py-2 fs-6">Rejected</span>
    <button data-bs-toggle="tooltip"
      data-bs-title="Reject Reason: {{ $report->reject_reason ?? '-' }}"
      class="btn btn-secondary btn-sm align-items-center">
      <i class='bx bx-info-circle'></i></button>
  @endif
@endif

<script type="module">
  const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
  const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(
    tooltipTriggerEl));
</script>
