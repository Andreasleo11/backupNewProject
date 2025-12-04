@extends('new.layouts.app')

@section('content')
    <div class="container py-3">

        {{-- Header --}}
        <section class="mb-3">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
                <div>
                    <h1 class="h4 mb-1">Purchase Requisition List</h1>
                    <p class="text-muted small mb-0">
                        Daftar purchase requisition yang sudah <strong>approved</strong>.
                    </p>
                </div>

                {{-- Kalau nanti perlu tombol / filter tambahan, taruh di sini --}}
                {{-- <div class="text-md-end">
                    <button class="btn btn-sm btn-outline-secondary">
                        Export
                    </button>
                </div> --}}
            </div>
        </section>

        {{-- Alert (jika ada) --}}
        @includeWhen(View::exists('partials.alert-success-error'), 'partials.alert-success-error')

        {{-- Content --}}
        <section class="content">
            <div class="card shadow-sm border-0">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <span class="fw-semibold small text-uppercase text-muted">
                        Purchase Requisitions
                    </span>

                    {{-- OPTIONAL: Filter status (UI saja, logika filter bisa kamu sambung ke DataTable) --}}
                    {{-- 
                    <div class="d-flex align-items-center gap-2">
                        <label for="status-filter" class="form-label mb-0 small text-muted">
                            Status
                        </label>
                        <select name="filter_status" id="status-filter" class="form-select form-select-sm">
                            <option value="" selected>All</option>
                            <option value="3">Waiting</option>
                            <option value="4">Approved</option>
                            <option value="5">Rejected</option>
                        </select>
                    </div>
                    --}}
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        {{ $dataTable->table(['class' => 'table table-sm table-hover align-middle mb-0']) }}
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    {{ $dataTable->scripts() }}

    {{-- Contoh cara sambung filter status ke DataTable (sesuaikan ID table-nya) --}}
    {{--
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const statusFilter = document.getElementById('status-filter');

            if (!statusFilter || !window.LaravelDataTables) return;

            // Ganti 'purchase-requisitions-table' dengan ID table kamu
            const table = window.LaravelDataTables['purchase-requisitions-table'];

            statusFilter.addEventListener('change', function () {
                const val = this.value || '';

                // Kirim parameter tambahan ke server (sesuaikan dengan DataTable builder di backend)
                table.ajax.params().filter_status = val;
                table.ajax.reload();
            });
        });
    </script>
    --}}
@endpush
