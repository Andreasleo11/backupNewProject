<div class="container py-4">

  {{-- Header + actions --}}
  <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
    <div class="d-flex align-items-center gap-2">
      <h1 class="h5 mb-0">Departments — Compliance Overview</h1>
      <span class="badge bg-dark-subtle text-dark">{{ $items->total() }} total</span>
    </div>
  </div>

  {{-- KPI cards (reflect current filtered page) --}}
  <div class="row g-2 mb-3">
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body py-3">
          <div class="small text-muted">On this page</div>
          <div class="fs-5 fw-semibold">{{ $kpi['count'] }}</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body py-3">
          <div class="small text-muted">Complete (100%)</div>
          <div class="fs-5 fw-semibold text-success">{{ $kpi['complete'] }}</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body py-3">
          <div class="small text-muted">Incomplete</div>
          <div class="fs-5 fw-semibold text-warning">{{ $kpi['incomplete'] }}</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body py-3">
          <div class="small text-muted">Avg compliance</div>
          <div class="fs-5 fw-semibold">{{ $kpi['avg'] }}%</div>
        </div>
      </div>
    </div>
  </div>

  {{-- Toolbar --}}
  <div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
      <div class="row g-2 align-items-center">
        <div class="col-12 col-lg-5">
          <div class="position-relative">
            <i class="bi bi-search position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
            <input type="text" class="form-control ps-5" placeholder="Search department by name or code…"
                   wire:model.live.debounce.300ms="search">
          </div>
        </div>

        <div class="col-6 col-lg-2">
          <select class="form-select form-select-sm" wire:model.live="status" title="Filter status">
            <option value="all">All</option>
            <option value="complete">Complete (100%)</option>
            <option value="incomplete">Incomplete</option>
          </select>
        </div>

        <div class="col-6 col-lg-2">
          <select class="form-select form-select-sm" wire:model.live="bucket" title="Compliance bucket">
            <option value="">All buckets</option>
            <option value="0-49">0–49%</option>
            <option value="50-99">50–99%</option>
            <option value="100">100%</option>
          </select>
        </div>

        <div class="col-6 col-lg-2">
          <select class="form-select form-select-sm" wire:model.live="sort">
            <option value="name">Sort: Name</option>
            <option value="code">Sort: Code</option>
            <option value="percent">Sort: Percent</option>
          </select>
        </div>

        <div class="col-6 col-lg-1 d-flex gap-2">
          <button class="btn btn-outline-secondary btn-sm w-100" wire:click="toggleDir" title="Toggle sort direction">
            <i class="bi {{ $dir==='asc' ? 'bi-arrow-down-up' : 'bi-arrow-up-down' }}"></i>
          </button>
          <select class="form-select form-select-sm" wire:model.live="perPage" title="Per page" style="max-width:90px">
            <option>10</option><option>25</option><option>50</option>
          </select>
        </div>
      </div>
    </div>
  </div>

  {{-- Desktop TABLE --}}
  <div class="table-responsive d-none d-md-block">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light sticky-top" style="top: 56px; z-index: 5;">
        <tr>
          <th role="button" wire:click="sortBy('code')">Code</th>
          <th role="button" wire:click="sortBy('name')">Department</th>
          <th style="width:320px" role="button" wire:click="sortBy('percent')">Compliance</th>
          <th>Status</th>
          <th class="text-end">Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse($items as $row)
          <tr wire:key="dept-row-{{ $row['dept']->id }}">
            <td class="text-muted small">{{ $row['dept']->code ?? '—' }}</td>
            <td class="fw-semibold">{{ $row['dept']->name }}</td>
            <td>
              <div class="d-flex align-items-center gap-2">
                <div class="progress flex-grow-1" role="progressbar"
                     aria-valuenow="{{ $row['percent'] }}" aria-valuemin="0" aria-valuemax="100">
                  <div class="progress-bar {{ $row['percent']==100 ? 'bg-success' : '' }}"
                       style="width: {{ $row['percent'] }}%"></div>
                </div>
                <span class="small text-muted">{{ $row['percent'] }}%</span>
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
            <td colspan="5" class="text-center text-muted py-4">No departments.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Mobile CARDS --}}
  <div class="d-md-none">
    @forelse($items as $row)
      <div class="card border-0 shadow-sm mb-2" wire:key="dept-card-{{ $row['dept']->id }}">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <div class="text-muted small">{{ $row['dept']->code ?? '—' }}</div>
              <div class="fw-semibold">{{ $row['dept']->name }}</div>
            </div>
            <a href="{{ route('departments.compliance', $row['dept']) }}" class="btn btn-sm btn-outline-secondary">
              Open
            </a>
          </div>
          <div class="mt-3">
            <div class="progress" role="progressbar" aria-valuenow="{{ $row['percent'] }}" aria-valuemin="0" aria-valuemax="100">
              <div class="progress-bar {{ $row['percent']==100 ? 'bg-success' : '' }}" style="width: {{ $row['percent'] }}%"></div>
            </div>
            <div class="d-flex justify-content-between mt-2 small">
              <span>Status:</span>
              @if($row['percent']>=100)
                <span class="badge text-bg-success">Complete</span>
              @else
                <span class="badge text-bg-warning">Incomplete</span>
              @endif
            </div>
          </div>
        </div>
      </div>
    @empty
      <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5 text-muted">
          <div class="fs-2 mb-2">🔎</div>No departments.
        </div>
      </div>
    @endforelse
  </div>

  {{-- Footer / Pagination --}}
  <div class="mt-3">
    {{ $items->links() }}
  </div>
</div>

@pushOnce('extraCss')
<style>
  .form-control:focus, .form-select:focus, .btn:focus { box-shadow: 0 0 0 .15rem rgba(13,110,253,.15); }
  .table > :not(caption) > * > * { padding-top:.65rem; padding-bottom:.65rem; }
</style>
@endPushOnce
