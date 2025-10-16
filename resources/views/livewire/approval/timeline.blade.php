<div class="card border-0 shadow-sm">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <div class="h6 mb-0">Approval Timeline</div>
      <span class="badge text-bg-light">
        {{ $request?->status ?? 'DRAFT' }}
      </span>
    </div>

    @if(!$request)
      <div class="text-muted small">No approval request yet.</div>
    @else
      <div class="mb-3">
        <div class="small text-muted">Steps</div>
        <ol class="list-group list-group-numbered">
          @foreach($request->steps as $s)
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <div>
                <div class="fw-semibold">
                  @if($s->approver_type === 'user')
                    User #{{ $s->approver_id }}
                  @else
                    Role #{{ $s->approver_id }}
                  @endif
                </div>
                <div class="small text-muted">Sequence {{ $s->sequence }}</div>
              </div>
              <span class="badge rounded-pill text-bg-{{ [
                'PENDING'=>'secondary','APPROVED'=>'success','REJECTED'=>'danger','SKIPPED'=>'warning'
              ][$s->status] ?? 'secondary' }}">
                {{ $s->status }}
              </span>
            </li>
          @endforeach
        </ol>
      </div>

      <div>
        <div class="small text-muted">History</div>
        <ul class="list-group">
          @foreach($request->actions as $a)
            <li class="list-group-item">
              <div class="d-flex justify-content-between">
                <div>
                  <span class="fw-semibold">{{ $a->from_status ?? '—' }}</span>
                  <i class="bi bi-arrow-right-short"></i>
                  <span class="fw-semibold">{{ $a->to_status }}</span>
                  @if($a->remarks) <span class="ms-2 text-muted">— {{ $a->remarks }}</span> @endif
                </div>
                <div class="small text-muted">{{ $a->created_at->format('d M Y H:i') }}</div>
              </div>
            </li>
          @endforeach
        </ul>
      </div>
    @endif
  </div>
</div>
