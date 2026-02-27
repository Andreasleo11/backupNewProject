@extends('new.layouts.app')

@section('content')
<div class="container-fluid py-4">

    <div class="mb-4">
        <h4 class="fw-bold mb-0">Evaluation Data Management</h4>
        <small class="text-muted">Import, filter, and manage monthly evaluation records</small>
    </div>

    {{-- Flash messages --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bx bx-error-circle me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="bx bx-error me-1"></i>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-3 mb-4">

        {{-- Import Card --}}
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bx bx-upload me-1"></i> Import Evaluation Data</h6>
                </div>
                <div class="card-body">
                    <form method="POST"
                          action="{{ route('UpdateEvaluation') }}"
                          enctype="multipart/form-data"
                          id="import-form">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Upload Excel Files</label>
                            <input type="file"
                                   name="excel_files[]"
                                   id="excel_files"
                                   class="form-control @error('excel_files') is-invalid @enderror"
                                   accept=".xlsx,.xls,.csv"
                                   multiple
                                   onchange="displaySelectedFiles(this)">
                            @error('excel_files')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div id="file-list" class="mt-2 small text-muted"></div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-import me-1"></i> Import
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Delete Card --}}
        <div class="col-md-6">
            <div class="card h-100 border-danger">
                <div class="card-header bg-danger bg-opacity-10">
                    <h6 class="mb-0 text-danger"><i class="bx bx-trash me-1"></i> Delete Data by Month</h6>
                </div>
                <div class="card-body">
                    <form method="POST"
                          action="{{ route('DeleteEvaluation') }}"
                          id="delete-form"
                          onsubmit="return confirmDelete()">
                        @csrf
                        @method('DELETE')
                        <div class="row g-2 mb-3">
                            <div class="col">
                                <label class="form-label fw-semibold">Month</label>
                                <select name="filter_month"
                                        id="filter_month"
                                        class="form-select @error('filter_month') is-invalid @enderror">
                                    <option value="01" {{ old('filter_month') == '01' ? 'selected' : '' }}>January</option>
                                    <option value="02" {{ old('filter_month') == '02' ? 'selected' : '' }}>February</option>
                                    <option value="03" {{ old('filter_month') == '03' ? 'selected' : '' }}>March</option>
                                    <option value="04" {{ old('filter_month') == '04' ? 'selected' : '' }}>April</option>
                                    <option value="05" {{ old('filter_month') == '05' ? 'selected' : '' }}>May</option>
                                    <option value="06" {{ old('filter_month') == '06' ? 'selected' : '' }}>June</option>
                                    <option value="07" {{ old('filter_month') == '07' ? 'selected' : '' }}>July</option>
                                    <option value="08" {{ old('filter_month') == '08' ? 'selected' : '' }}>August</option>
                                    <option value="09" {{ old('filter_month') == '09' ? 'selected' : '' }}>September</option>
                                    <option value="10" {{ old('filter_month') == '10' ? 'selected' : '' }}>October</option>
                                    <option value="11" {{ old('filter_month') == '11' ? 'selected' : '' }}>November</option>
                                    <option value="12" {{ old('filter_month') == '12' ? 'selected' : '' }}>December</option>
                                </select>
                                @error('filter_month')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col">
                                <label class="form-label fw-semibold">Year</label>
                                <select name="filter_year"
                                        id="filter_year"
                                        class="form-select @error('filter_year') is-invalid @enderror">
                                    @for ($y = date('Y'); $y >= date('Y') - 4; $y--)
                                        <option value="{{ $y }}" {{ old('filter_year', date('Y')) == $y ? 'selected' : '' }}>
                                            {{ $y }}
                                        </option>
                                    @endfor
                                </select>
                                @error('filter_year')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <button type="submit" class="btn btn-danger">
                            <i class="bx bx-trash me-1"></i> Delete Data
                        </button>
                        <small class="d-block mt-2 text-muted">
                            This deletes <strong>all</strong> evaluation records for the selected month &amp; year.
                        </small>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- DataTable with filters --}}
    <div class="card">
        <div class="card-header d-flex align-items-center gap-3 flex-wrap">
            <h6 class="mb-0 me-auto"><i class="bx bx-table me-1"></i> Evaluation Records</h6>

            {{-- Month filter (real-time DataTable column search) --}}
            <div class="d-flex align-items-center gap-2">
                <label class="form-label mb-0 text-nowrap">Filter Month</label>
                <select id="dt-month-filter" class="form-select form-select-sm" style="width:130px">
                    <option value="">All Months</option>
                    <option value="01">January</option>
                    <option value="02">February</option>
                    <option value="03">March</option>
                    <option value="04">April</option>
                    <option value="05">May</option>
                    <option value="06">June</option>
                    <option value="07">July</option>
                    <option value="08">August</option>
                    <option value="09">September</option>
                    <option value="10">October</option>
                    <option value="11">November</option>
                    <option value="12">December</option>
                </select>
            </div>

            {{-- Year filter (real-time DataTable column search) --}}
            <div class="d-flex align-items-center gap-2">
                <label class="form-label mb-0 text-nowrap">Filter Year</label>
                <select id="dt-year-filter" class="form-select form-select-sm" style="width:100px">
                    <option value="">All Years</option>
                    @for ($y = date('Y'); $y >= date('Y') - 4; $y--)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endfor
                </select>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                {{ $dataTable->table() }}
            </div>
        </div>
    </div>

</div>

{{ $dataTable->scripts() }}

<script type="module">
    // ── DataTable real-time filters ──────────────────────────
    $(function () {
        const table = window.LaravelDataTables['evaluationdata-table'];

        function applyFilters() {
            const month = $('#dt-month-filter').val();
            const year  = $('#dt-year-filter').val();

            // Month column (index 4) stores dates like "2024-06-01"
            // We build a regex to match the month and/or year portion
            let pattern = year || '';
            if (month) {
                pattern = pattern ? pattern + '-' + month + '-' : '-' + month + '-';
            }

            table.column(4).search(pattern, true, false).draw();
        }

        $('#dt-month-filter, #dt-year-filter').on('change', applyFilters);
    });

    // ── File list display ────────────────────────────────────
    window.displaySelectedFiles = function (input) {
        const list = document.getElementById('file-list');
        if (input.files.length === 0) {
            list.innerHTML = '';
            return;
        }
        const items = Array.from(input.files)
            .map(f => `<span class="badge bg-secondary me-1">${f.name}</span>`)
            .join('');
        list.innerHTML = `<strong>${input.files.length} file(s):</strong> ` + items;
    };

    // ── Delete confirmation ──────────────────────────────────
    window.confirmDelete = function () {
        const month = document.getElementById('filter_month').options[
            document.getElementById('filter_month').selectedIndex
        ].text;
        const year = document.getElementById('filter_year').value;

        return confirm(
            `⚠️ Delete ALL evaluation data for ${month} ${year}?\n\nThis cannot be undone.`
        );
    };
</script>
@endsection
