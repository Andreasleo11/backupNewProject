<div class="d-flex justify-content-between align-items-center mb-3">
  <button id="toggleFilters" class="btn btn-primary">
    <i class='bx bx-filter-alt'></i> Show Filters
  </button>
</div>

<div id="filterSection" class="{{ $filtersApplied ? '' : 'd-none' }}">
  <form id="filterForm" method="GET" action="{{ $filterRoute }}">
    <div class="row mb-4">
      <div class="col-md-3">
        <select id="columnSelect" name="filterColumn" class="form-select">
          <option value="" selected disabled>Select Column</option>
          @foreach ($filterColumns as $value => $label)
            <option value="{{ $value }}"
              {{ request()->get('filterColumn') == $value ? 'selected' : '' }}>{{ $label }}
            </option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3">
        <select id="actionSelect" name="filterAction" class="form-select">
          <option value="" selected disabled>Select Action</option>
          <option value="contains"
            {{ request()->get('filterAction') == 'contains' ? 'selected' : '' }}>
            Contains</option>
          <option value="equals" {{ request()->get('filterAction') == 'equals' ? 'selected' : '' }}>
            Equals
          </option>
          <option value="startswith"
            {{ request()->get('filterAction') == 'startswith' ? 'selected' : '' }}>
            Starts With</option>
          <option value="endswith"
            {{ request()->get('filterAction') == 'endswith' ? 'selected' : '' }}>Ends
            With</option>
        </select>
      </div>
      <div class="col-md-3">
        <input type="text" id="filterValue" name="filterValue" class="form-control"
          placeholder="Value" value="{{ request()->get('filterValue') }}">
      </div>
      <div class="col-md-3 d-flex align-items-end">
        <button type="submit" class="btn btn-primary me-2">Apply Filter</button>
        @if ($filtersApplied)
          <button type="button" id="resetFilters" class="btn text-danger btn-link">Reset
            Filters</button>
        @endif
      </div>
    </div>
  </form>
</div>
