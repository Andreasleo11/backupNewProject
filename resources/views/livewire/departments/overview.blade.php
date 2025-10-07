<div class="container py-4">
  <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
    <h1 class="h5 mb-0">Departments — Compliance Overview</h1>
    <div class="d-flex gap-2">
      <select class="form-select form-select-sm" style="width:auto" wire:model.live="status">
        <option value="all">All</option>
        <option value="complete">Complete (100%)</option>
        <option value="incomplete">Incomplete</option>
      </select>
      <select class="form-select form-select-sm" style="width:auto" wire:model.live="perPage">
        <option>10</option><option>25</option><option>50</option>
      </select>
    </div>
  </div>

  <div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
      <div class="row g-2 align-items-center">
        <div class="col-12 col-md-6">
          <div class="position-relative">
            <i class="bi bi-search position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
            <input type="text" class="form-control ps-5" placeholder="Search department…"
                   wire:model.live.debounce.300ms="search">
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead class="table-light">
        <tr>
          <th>Department</th>
          <th>Compliance</th>
          <th>Status</th>
          <th class="text-end">Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse($items as $row)
          <tr>
            <td>
              <div class="fw-semibold">{{ $row['dept']->name }}</div>
              <div class="small text-muted">{{ $row['dept']->code ?? '—' }}</div>
            </td>
            <td style="width:260px">
              <div class="progress" role="progressbar" aria-valuenow="{{ $row['percent'] }}" aria-valuemin="0" aria-valuemax="100">
                <div class="progress-bar {{ $row['percent']==100 ? 'bg-success' : '' }}" style="width: {{ $row['percent'] }}%">
                  {{ $row['percent'] }}%
                </div>
              </div>
            </td>
            <td>
              @if($row['percent']>=100)
                <span class="badge text-bg-success">Complete</span>
              @else
                <span class="badge text-bg-warning">Incomplete</span>
              @endif
            </td>
            <td class="text-end">
              <a href="{{ route('departments.compliance', $row['dept']) }}" class="btn btn-sm btn-outline-secondary">
                Open
              </a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="4" class="text-center text-muted py-4">No departments.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="d-flex justify-content-between align-items-center mt-2">
    <div class="small text-muted">
      Showing {{ $items->firstItem() }}–{{ $items->lastItem() }} of {{ $items->total() }}
    </div>
    {{ $items->links() }}
  </div>
</div>
