<div class="container py-4" wire:init="load">

  {{-- Header / Controls --}}
  <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Inspection Reports</h3>

    <div class="d-flex flex-wrap gap-2 align-items-center">
      {{-- Per page --}}
      <div class="input-group input-group-sm" style="width: 160px;">
        <span class="input-group-text">Rows</span>
        <select class="form-select" wire:model.live="perPage" wire:loading.attr="disabled">
          @foreach($perPageOptions as $opt)
            <option value="{{ $opt }}">{{$opt}}</option>
          @endforeach
        </select>
      </div>

      {{-- Global search --}}
      <div class="input-group">
        <input type="search" class="form-control" placeholder="Search document, customer, part…"
               wire:key="search-box" wire:model.live.debounce.300ms="search">
        <button class="btn btn-outline-secondary" type="button" wire:click="clearSearch" wire:loading.attr="disabled">
          Clear
        </button>
      </div>

      {{-- Column visibility --}}
      <div class="dropdown">
        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown"
                wire:loading.attr="disabled">
          Columns
        </button>
        <div class="dropdown-menu dropdown-menu-end p-2" style="min-width: 220px;">
          @foreach($showCol as $key => $val)
            <label class="dropdown-item d-flex align-items-center gap-2">
              <input type="checkbox" class="form-check-input" wire:model.live="showCol.{{ $key }}">
              <span class="text-capitalize">{{ str_replace('_',' ',$key) }}</span>
            </label>
          @endforeach
        </div>
      </div>
      <button class="btn btn-outline-primary"
              wire:click="exportCsv"
              wire:loading.attr="disabled"
              wire:target="exportCsv">
        <i class="bi bi-download me-1"></i> Export CSV
      </button>
      <button class="btn btn-outline-secondary" type="button" wire:click="clearFilters" wire:loading.attr="disabled">
        Reset Filters
      </button>
    </div>
  </div>
  {{-- Announcements for screen readers --}}
  <div class="visually-hidden" aria-live="polite" aria-atomic="true">
    @php
      $announce = '';
      if ($ready && $reports instanceof \Illuminate\Contracts\Pagination\Paginator) {
          $announce = "Sorted by {$sortField} " . ($sortDir === 'asc' ? 'ascending' : 'descending')
                    . ". Showing results ".$reports->firstItem()." to ".$reports->lastItem()
                    . " of ".$reports->total().".";
      }
    @endphp
    {{ $announce }}
  </div>
  <div class="table-responsive">
    <table class="table table-striped align-middle mb-0" aria-describedby="reports-caption" aria-busy="{{ $ready ? 'false' : 'true' }}">
      <caption id="reports-caption" class="visually-hidden">
        Inspection reports with sortable columns and per-column filters.
      </caption>
      @php
        /**
        * Accessible sortable column header.
        * Usage: {!! $a11yTh('Date', 'inspection_date') !!}
        */
        $a11yTh = function (string $label, string $field) {
            $isActive = $this->sortField === $field;
            $dir = $isActive ? $this->sortDir : 'none'; // asc|desc|none
            $ariaSort = $dir === 'asc' ? 'ascending' : ($dir === 'desc' ? 'descending' : 'none');

            // Determine next direction (what will happen if the user clicks)
            $next = $isActive ? ($this->sortDir === 'asc' ? 'desc' : 'asc') : 'asc';
            $srNext = $next === 'asc' ? 'ascending' : 'descending';

            $icon = $isActive
                ? ($this->sortDir === 'asc' ? 'bi-caret-up-fill' : 'bi-caret-down-fill')
                : 'bi-arrow-down-up';

            // th with aria-sort and a button that announces the action
            return <<<HTML
              <th scope="col" aria-sort="{$ariaSort}">
                <button type="button"
                        class="btn btn-link p-0 text-decoration-none"
                        wire:click="sortBy('{$field}')"
                        aria-label="Sort by {$label} {$srNext}">
                  {$label} <i class="bi {$icon} ms-1" aria-hidden="true"></i>
                </button>
              </th>
            HTML;
        };
      @endphp
      <thead class="table-light">
        {{-- Sortable header row (unchanged) --}}
        <tr>
          <th scope="col" style="width:70px;">#</th>

          @if($showCol['document_number'])
            {!! $a11yTh('Document No.', 'document_number') !!}
          @endif
          @if($showCol['inspection_date'])
            {!! $a11yTh('Date', 'inspection_date') !!}
          @endif
          @if($showCol['shift'])
            {!! $a11yTh('Shift', 'shift') !!}
          @endif
          @if($showCol['customer'])
            {!! $a11yTh('Customer', 'customer') !!}
          @endif
          @if($showCol['part_number'])
            {!! $a11yTh('Part Number', 'part_number') !!}
          @endif

          <th scope="col" class="text-end" style="width:140px;">Actions</th>
        </tr>

        {{-- Filter inputs row (unchanged) --}}
        <tr class="small">
          <th></th>

          @if($showCol['document_number'])
            <th>
              <input type="text" class="form-control form-control-sm"
                     placeholder="Doc no…"
                     wire:key="f-doc"
                     wire:model.live.debounce.500ms="filters.document_number">
            </th>
          @endif

          @if($showCol['inspection_date'])
            <th>
              <div class="d-flex gap-1">
                <input type="date" class="form-control form-control-sm"
                       wire:key="f-date_from"
                       wire:model.live="filters.date_from" title="From">
                <input type="date" class="form-control form-control-sm"
                       wire:key="f-date_to"
                       wire:model.live="filters.date_to" title="To">
              </div>
            </th>
          @endif

          @if($showCol['shift'])
            <th>
              <select class="form-select form-select-sm" wire:model.live="filters.shift" wire:loading.attr="disabled">
                <option value="">All</option>
                @foreach($shiftOptions as $opt)
                  <option value="{{ $opt }}">{{ $opt }}</option>
                @endforeach
              </select>
            </th>
          @endif

          @if($showCol['customer'])
            <th>
              <input type="text" class="form-control form-control-sm"
                     placeholder="Customer…"
                     wire:key="f-cust"
                     wire:model.live.debounce.500ms="filters.customer">
            </th>
          @endif

          @if($showCol['part_number'])
            <th>
              <input type="text" class="form-control form-control-sm"
                     placeholder="Part no…"
                     wire:key="f-part"
                     wire:model.live.debounce.500ms="filters.part_number">
            </th>
          @endif

          <th></th>
        </tr>
      </thead>

      <tbody wire:loading.class="opacity-50" role="rowgroup">
        {{-- SKELETON ROWS (shown until $ready === true) --}}
        @if(!$ready)
          @php
            // number of skeleton rows to reflect current perPage (cap for visual neatness)
            $skeleton = min($perPage, 10);
            // helper to render one skeleton cell
            $sk = fn($w = '100%') => '<span class="placeholder col-12" style="max-width:'.$w.'"></span>';
          @endphp

          @for($i=0; $i<$skeleton; $i++)
            <tr>
              <td>{!! $sk('40px') !!}</td>

              @if($showCol['document_number'])
                <td>{!! $sk('160px') !!}</td>
              @endif

              @if($showCol['inspection_date'])
                <td>{!! $sk('120px') !!}</td>
              @endif

              @if($showCol['shift'])
                <td>{!! $sk('60px') !!}</td>
              @endif

              @if($showCol['customer'])
                <td>{!! $sk('180px') !!}</td>
              @endif

              @if($showCol['part_number'])
                <td>{!! $sk('140px') !!}</td>
              @endif

              <td class="text-end">{!! $sk('90px') !!}</td>
            </tr>
          @endfor

        {{-- REAL ROWS (after $ready = true) --}}
        @else
          @forelse($reports as $r)
            <tr wire:key="row-{{ $r->getKey() }}">
              <td>{{ $reports->firstItem() + $loop->index }}</td>

              @if($showCol['document_number'])
                <td class="font-monospace">{{ $r->document_number }}</td>
              @endif

              @if($showCol['inspection_date'])
                <td>{{ \Illuminate\Support\Carbon::parse($r->inspection_date)->format('Y-m-d') }}</td>
              @endif

              @if($showCol['shift'])
                <td>{{ $r->shift }}</td>
              @endif

              @if($showCol['customer'])
                <td>{{ $r->customer }}</td>
              @endif

              @if($showCol['part_number'])
                <td>{{ $r->part_number }}</td>
              @endif

              <td class="text-end">
                @php
                    $qs = request()->getQueryString();
                @endphp
                <a wire:navigate
                  href="{{ route('inspection-reports.show', ['inspection_report' => $r->id]) }}"
                  class="btn btn-sm btn-primary">
                  View
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center py-4 text-muted">No inspection reports found.</td>
            </tr>
          @endforelse
        @endif
      </tbody>
    </table>
  </div>

  {{-- Pagination --}}
  <div class="mt-3">
    @if($ready)
      {{ $reports->onEachSide(1)->links() }}
    @else
      {{-- skeleton pagination --}}
      <div class="d-flex justify-content-between">
        <span class="placeholder col-4"></span>
        <span class="placeholder col-5"></span>
      </div>
    @endif
  </div>
</div>
