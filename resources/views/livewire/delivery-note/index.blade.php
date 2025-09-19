<div>
  {{-- Sidebar toggle button for small screens --}}
  <div class="d-lg-none bg-light border-bottom py-2 px-3">
    <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="collapse"
      data-bs-target="#filterSidebar">
      ☰ Filters
    </button>
  </div>

  <div class="container-fluid">
    <div class="row">

      {{-- Sidebar Filters --}}
      <div class="col-lg-3">
        <div class="collapse d-lg-block border-end bg-light py-3 px-3" id="filterSidebar">
          <h5 class="fw-semibold mb-3">🔎 Filters</h5>

          <div class="mb-3">
            <label>Status</label>
            <select class="form-select" wire:model.defer="inputStatus">
              <option value="all">All</option>
              <option value="draft">Draft</option>
              <option value="submitted">Submitted</option>
            </select>
          </div>

          <div class="mb-3">
            <label>Branch</label>
            <select class="form-select" wire:model.defer="inputBranch">
              <option value="all">All</option>
              <option value="JAKARTA">JAKARTA</option>
              <option value="KARAWANG">KARAWANG</option>
            </select>
          </div>

          <div class="mb-3">
            <label>Ritasi</label>
            <select class="form-select" wire:model.defer="inputRitasi">
              <option value="all">All</option>
              <option value="1">1 (Pagi)</option>
              <option value="2">2 (Siang)</option>
              <option value="3">3 (Sore)</option>
              <option value="4">4 (Malam)</option>
            </select>
          </div>

          <div class="mb-3">
            <label>From Date</label>
            <input type="date" class="form-control" wire:model.defer="inputFromDate">
          </div>

          <div class="mb-3">
            <label>To Date</label>
            <input type="date" class="form-control" wire:model.defer="inputToDate">
          </div>

          <div class="mb-3">
            <label>Search</label>
            <input type="text" class="form-control" wire:model.live="searchAll"
              placeholder="Search...">
          </div>

          <button wire:click="applyFilters" class="btn btn-primary w-100">
            🔍 Apply Filters
          </button>
        </div>
      </div>

      {{-- Main Content --}}
      <div class="col-lg-9 py-3 px-4">
        @if (session()->has('success'))
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-4">
          <h3 class="fw-bold mb-0">📋 Delivery Notes</h3>
          <a href="{{ route('delivery-notes.create') }}" class="btn btn-primary">
            + Create Delivery Note
          </a>
        </div>

        {{-- Active Filters Summary --}}
        @if (
            $filterStatus !== 'all' ||
                $filterBranch !== 'all' ||
                $filterRitasi !== 'all' ||
                $fromDate ||
                $toDate ||
                $searchAll)
          <div class="alert alert-info small">
            <strong>Active Filters:</strong>
            <ul class="mb-0">
              @if ($filterStatus !== 'all')
                <li>Status: <strong>{{ ucfirst($filterStatus) }}</strong></li>
              @endif
              @if ($filterBranch !== 'all')
                <li>Branch: <strong>{{ $filterBranch }}</strong></li>
              @endif
              @if ($filterRitasi !== 'all')
                <li>Ritasi: <strong>{{ $filterRitasi }}</strong></li>
              @endif
              @if ($fromDate)
                <li>From: <strong>{{ $fromDate }}</strong></li>
              @endif
              @if ($toDate)
                <li>To: <strong>{{ $toDate }}</strong></li>
              @endif
              @if ($searchAll)
                <li>Search: <strong>{{ $searchAll }}</strong></li>
              @endif
            </ul>
          </div>
        @endif

        {{-- Table --}}
        <div class="table-responsive">
          @include('livewire.delivery-note._table')
        </div>

        {{ $deliveryNotes->links() }}
      </div>
    </div>
  </div>
</div>
