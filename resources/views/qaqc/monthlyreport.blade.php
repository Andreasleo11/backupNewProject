@extends('new.layouts.app')

@section('content')
    <div class="container py-4">

        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h1 class="h3 mb-1">Monthly Report VQC</h1>
                <p class="mb-0 text-muted small">
                    Ringkasan total NG (cannot use), quantity, dan estimasi kerugian per customer tiap bulan.
                </p>
            </div>
        </div>

        {{-- Filters & Actions --}}
        <div class="row g-3 align-items-end mb-3">
            <div class="col-md-3">
                <form action="{{ route('monthlyreport.details') }}" method="POST" target="_blank">
                    @csrf
                    <label for="monthFilter" class="form-label">Filter by Month</label>
                    <select id="monthFilter" name="monthData" class="form-select">
                        <option value="all">All Months</option>
                        @foreach (array_keys($result) as $month)
                            <option value="{{ $month }}">{{ $month }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm mt-2 w-100">
                        View Detail
                    </button>
                </form>
            </div>

            <div class="col-md-3 offset-md-6 text-md-end">
                <form id="exportForm" action="{{ route('monthlyreport.export') }}" method="POST">
                    @csrf
                    <input type="hidden" name="monthData" id="exportMonthData">
                    <label class="form-label d-none d-md-block">&nbsp;</label>
                    <button type="submit" class="btn btn-success w-100">
                        Export to Excel
                    </button>
                </form>
            </div>
        </div>

        {{-- Monthly Tables --}}
        @forelse ($result as $month => $customers)
            @php
                $monthCantUse = collect($customers)->sum('cant_use');
                $monthRecQty = collect($customers)->sum('total_rec_quantity');
                $monthTotalPrice = collect($customers)->sum('total_price');
            @endphp

            <div class="month-table mb-4" id="table-{{ $month }}">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <span class="fw-semibold">{{ $month }}</span>
                            <span class="badge bg-light text-muted ms-2">
                                {{ count($customers) }} customer
                            </span>
                        </div>
                        <div class="text-end small text-muted">
                            <div>Total Cannot Use: <strong>{{ $monthCantUse }}</strong></div>
                            <div>Total Rec Qty: <strong>{{ $monthRecQty }}</strong></div>
                            <div>Total Loss: <strong>{{ 'IDR ' . number_format($monthTotalPrice, 0, ',', '.') }}</strong></div>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">Customer</th>
                                        <th class="text-end">Total Cannot Use</th>
                                        <th class="text-end">Total Rec Quantity</th>
                                        <th class="text-end">Total Price (IDR)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($customers as $customerId => $customerData)
                                        <tr>
                                            <td class="ps-3">
                                                {{ $customerId }}
                                            </td>
                                            <td class="text-end">
                                                {{ $customerData['cant_use'] }}
                                            </td>
                                            <td class="text-end">
                                                {{ $customerData['total_rec_quantity'] }}
                                            </td>
                                            <td class="text-end">
                                                {{ 'IDR ' . number_format($customerData['total_price'], 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Optional: footer kecil --}}
                    {{-- <div class="card-footer small text-muted">
                        * Angka di atas merupakan akumulasi dari seluruh laporan verifikasi bulan {{ $month }}.
                    </div> --}}
                </div>
            </div>
        @empty
            <div class="card shadow-sm">
                <div class="card-body text-center py-5 text-muted">
                    <i class='bx bx-file-blank fs-1 d-block mb-2'></i>
                    <div class="fw-semibold">Belum ada data Monthly Report</div>
                    <div class="small">Silakan buat report VQC terlebih dahulu, lalu coba buka halaman ini lagi.</div>
                </div>
            </div>
        @endforelse
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const monthFilter = document.getElementById('monthFilter');
                const exportMonthData = document.getElementById('exportMonthData');
                const tables = document.querySelectorAll('.month-table');

                function applyFilter() {
                    const selectedMonth = monthFilter.value;
                    exportMonthData.value = selectedMonth;

                    if (selectedMonth === 'all') {
                        tables.forEach(table => {
                            table.classList.remove('d-none');
                        });
                    } else {
                        tables.forEach(table => {
                            const id = table.id.replace('table-', '');
                            table.classList.toggle('d-none', id !== selectedMonth);
                        });
                    }
                }

                monthFilter.addEventListener('change', applyFilter);

                // Trigger on load
                applyFilter();
            });
        </script>
    @endpush
@endsection
