{{-- IMPORTANT: exactly one root element, no content before/after --}}
<div id="progress-panel" @if ($this->job?->status === 'running') wire:poll.1500ms="refreshProgress" @endif
  wire:key="progress-panel-{{ $jobId }}">

  @if ($jobId && $this->job)
    <div class="d-flex justify-content-between align-items-end mb-2">
      <div class="small text-muted">
        <span class="me-3">
          Processed:
          <strong>{{ number_format($this->job->processed_rows ?? 0) }}</strong> /
          <strong>{{ number_format($this->job->total_rows ?? 0) }}</strong>
        </span>
        @php
          $eta = $this->etaSeconds;
          $mins = $eta ? intdiv($eta, 60) : null;
          $secs = $eta ? $eta % 60 : null;
        @endphp
        @if ($this->job->status === 'running' && $eta !== null)
          <span class="text-muted">ETA: ~{{ $mins }}m {{ $secs }}s</span>
        @endif
        @if ($this->job->status === 'completed' && $this->job->started_at && $this->job->finished_at)
          <span class="text-muted">• Duration:
            {{ $this->job->started_at->shortAbsoluteDiffForHumans($this->job->finished_at) }}
          </span>
        @endif
      </div>
      <div class="small">
        <span class="text-muted me-2">Progress</span>
        <strong>{{ $this->progress }}%</strong>
      </div>
    </div>

    <div class="progress" style="height: 26px;">
      <div
        class="progress-bar {{ $this->job->status === 'completed' ? 'bg-success' : '' }}
           @if ($this->job->status === 'running') progress-bar-striped progress-bar-animated @endif"
        role="progressbar" style="width: {{ $this->progress }}%;"
        aria-valuenow="{{ $this->progress }}" aria-valuemin="0" aria-valuemax="100">
        {{ $this->progress }}%
      </div>
    </div>

    @if ($this->job->status === 'failed')
      <div class="alert alert-danger mt-3 mb-0">
        <div class="fw-semibold mb-1">Import failed</div>
        <div class="small">{{ $this->job->error ?: 'Unknown error.' }}</div>
      </div>
    @endif

    @if (
        $this->job?->status === 'running' &&
            $this->heartbeatAgeSeconds !== null &&
            $this->heartbeatAgeSeconds >= 120)
      <div class="alert alert-warning mt-3">
        <div class="fw-semibold">No progress in {{ $this->heartbeatAgeSeconds }}s</div>
        <div class="small">The worker may be stalled or offline. You can retry the worker or mark
          this run as failed.</div>
        <button type="button" class="btn btn-sm btn-outline-danger mt-2"
          wire:click="forceFailIfStalled">
          Mark as Failed
        </button>
      </div>
    @endif
  @else
    {{-- Keep the root, but show a neutral placeholder when no job --}}
    <div class="text-muted small">No job selected.</div>
  @endif
</div>
