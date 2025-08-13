<div class="container py-4">

    <div class="d-flex align-items-center mb-3">
        <h1 class="h4 mb-0">Laporan Harian Karyawan</h1>
        <span class="badge bg-light text-dark ms-2">Departemen Anda</span>
    </div>

    <!-- Filter card -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <div class="row g-3 align-items-end">

                <div class="col-12 col-md-4">
                    <label class="form-label">Cari (NIK / Nama)</label>
                    <input type="text" class="form-control" placeholder="Ketik untuk mencari…"
                        wire:model.live.debounce.400ms="search">
                </div>

                <div class="col-12 col-sm-6 col-md-3">
                    <label class="form-label">Karyawan</label>
                    <select class="form-select" wire:model.live="employeeId">
                        <option value="">— Semua —</option>
                        @foreach ($this->employeesDropdown as $emp)
                            <option value="{{ $emp['employee_id'] }}">{{ $emp['employee_name'] }}</option>
                        @endforeach
                    </select>
                </div>

                @if ($canPickDept)
                    <div class="col-6 col-md-2">
                        <label class="form-label">Dept No</label>
                        <select class="form-select" wire:model.live="departmentNo">
                            <option value="">— Semua —</option>
                            @foreach ($this->departmentNos as $d)
                                <option value="{{ $d['dept_no'] }}">{{ $d['dept_no'] }} — {{ $d['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="col-6 col-md-3">
                    <label class="form-label">Jabatan</label>
                    <select class="form-select" wire:model.live="jabatan">
                        <option value="">— Semua —</option>
                        @foreach ($this->positions as $pos)
                            <option value="{{ $pos }}">{{ $pos }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label">Dari Tanggal</label>
                    <input type="date" class="form-control" wire:model.live="dateFrom">
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label">Sampai</label>
                    <input type="date" class="form-control" wire:model.live="dateTo">
                </div>

                <div class="col-12 col-md-auto ms-auto">
                    <button type="button" class="btn btn-outline-secondary" wire:click="resetFilters">
                        Reset
                    </button>
                </div>
            </div>
        </div>
        <div class="card-footer bg-transparent py-2">
            <div wire:loading>
                <span class="small text-muted">Memuat data…</span>
            </div>
        </div>
    </div>

    @if ($employees->isEmpty())
        <div class="alert alert-warning shadow-sm" role="alert">
            Tidak ada laporan yang ditemukan untuk filter saat ini.
        </div>
    @else
        <div class="table-responsive bg-white rounded shadow-sm">
            <table class="table table-hover table-bordered align-middle mb-0">
                <thead class="table-light" style="position: sticky; top: 0; z-index: 1;">
                    <tr>
                        <th style="width: 120px;">NIK</th>
                        <th style="width: 110px;">Dept No</th>
                        <th>Nama Karyawan</th>
                        <th style="width: 220px;">Jabatan</th>
                        <th style="width: 240px;">Terakhir Update</th>
                        <th style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($employees as $employee)
                        @php
                            $latest = $employee->latest_dt ? \Carbon\Carbon::parse($employee->latest_dt) : null;
                        @endphp
                        <tr>
                            <td class="text-monospace">{{ $employee->employee_id }}</td>
                            <td class="text-monospace">{{ $employee->departement_id }}</td>
                            <td class="fw-semibold">{{ $employee->employee_name }}</td>
                            <td>
                                @if (!empty($employee->jabatan))
                                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">
                                        {{ $employee->jabatan }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if ($latest)
                                    <div class="d-flex flex-column">
                                        <span>{{ $latest->translatedFormat('d M Y') }} •
                                            {{ $latest->format('H:i') }}</span>
                                        <small class="text-muted">{{ $latest->diffForHumans() }}</small>
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('reports.depthead.show', $employee->employee_id) }}"
                                    class="btn btn-sm btn-outline-primary">
                                    Lihat Detail
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
            <div class="text-muted small">
                Menampilkan {{ $employees->firstItem() }}–{{ $employees->lastItem() }} dari {{ $employees->total() }}
                karyawan
            </div>
            <div>
            </div>
        </div>
        {{ $employees->links() }}
    @endif
</div>
