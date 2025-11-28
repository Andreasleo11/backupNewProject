<div class="container py-4">
  {{-- Header --}}
  <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
    <div>
      <h1 class="h5 mb-0">{{ $report->title }}</h1>
      <div class="small text-muted">
        Doc#: <span class="fw-semibold">{{ $report->document_number }}</span>
        <span class="mx-2">•</span>
        Created {{ $report->created_at->format('d M Y H:i') }}
      </div>
    </div>

    <div class="d-flex align-items-center gap-2">
      <span class="badge text-bg-{{ [
          'DRAFT' => 'secondary', 'IN_REVIEW' => 'warning',
          'APPROVED' => 'success', 'REJECTED' => 'danger'
        ][$report->status] ?? 'secondary' }}">
        {{ $report->status }}
      </span>

      <a href="{{ route('verification.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back
      </a>

      @can('update', $report)
        @if($report->status === 'DRAFT')
          <a href="{{ route('verification.edit', $report->id) }}" class="btn btn-primary">
            <i class="bi bi-pencil-square"></i> Edit
          </a>
        @endif
      @endcan
    </div>
  </div>

  {{-- Top cards --}}
  <div class="row g-3 mb-3">
    <div class="col-12 col-md-8">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="small text-muted mb-1">Description</div>
          <div>{{ $report->description ?: '—' }}</div>

          <hr class="my-3">

          <div class="small text-muted mb-1">Meta</div>
          <div class="d-flex flex-wrap gap-3">
            <div><span class="text-muted">Department:</span> {{ data_get($report->meta,'department','—') }}</div>
          </div>
        </div>
      </div>
    </div>

    {{-- Actions (submit/approve/reject) --}}
    <div class="col-12 col-md-4">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="fw-semibold mb-2">Actions</div>

          {{-- Submit (creator) --}}
          @can('update', $report)
            @if($report->status === 'DRAFT')
              <div class="mb-3">
                <label class="form-label">Remarks (optional)</label>
                <textarea rows="2" class="form-control" wire:model.live.defer="remarks"
                          placeholder="Any notes to approvers"></textarea>
              </div>
              <button class="btn btn-primary w-100" wire:click="submit">
                <i class="bi bi-send"></i> Submit for Approval
              </button>
            @endif
          @endcan

          {{-- Approve / Reject (approvers) --}}
          @if($report->status === 'IN_REVIEW')
            <div class="mb-2">
              <label class="form-label">Remarks (optional)</label>
              <textarea rows="2" class="form-control" wire:model.live.defer="remarks"
                        placeholder="Reason / note"></textarea>
            </div>
            <div class="d-flex gap-2">
              <button class="btn btn-success flex-fill" wire:click="approve">
                <i class="bi bi-check2-circle"></i> Approve
              </button>
              <button class="btn btn-outline-danger flex-fill" wire:click="reject">
                <i class="bi bi-x-circle"></i> Reject
              </button>
            </div>
            <div class="form-text mt-2">
              Only the assigned approver can act; others will be blocked by the engine.
            </div>
          @endif

          @if(in_array($report->status, ['APPROVED','REJECTED']))
            <div class="alert alert-light border mt-2 mb-0">
              This report is <strong>{{ strtolower($report->status) }}</strong>.
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- Items table --}}
  <div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="fw-semibold">Items</div>
        <div class="small text-muted">
          Total: <span class="fw-semibold">
            {{ number_format($report->items->sum('amount'), 2) }}
          </span>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table align-middle">
          <thead class="table-light">
          <tr>
            <th style="width: 30%">Name</th>
            <th style="width: 50%">Notes</th>
            <th class="text-end" style="width: 20%">Amount</th>
          </tr>
          </thead>
          <tbody>
          @forelse($report->items as $i)
            <tr>
              <td>{{ $i->name }}</td>
              <td class="text-muted">{{ $i->notes ?: '—' }}</td>
              <td class="text-end">{{ number_format($i->amount, 2) }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="3" class="text-center text-muted py-4">No items.</td>
            </tr>
          @endforelse
          </tbody>
          @if($report->items->count())
            <tfoot>
            <tr>
              <th colspan="2" class="text-end">Total</th>
              <th class="text-end">{{ number_format($report->items->sum('amount'), 2) }}</th>
            </tr>
            </tfoot>
          @endif
        </table>
      </div>
    </div>
  </div>

  {{-- Generic Approval Timeline widget --}}
  @livewire('approval.timeline', [
    'approvableType' => \App\Infrastructure\Persistence\Eloquent\Models\VerificationReport::class,
    'approvableId'   => $report->id
  ])
</div>

