@extends('new.layouts.app')

@section('content')
    <div class="container py-3">

        {{-- HEADER --}}
        <section class="mb-3">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
                <div>
                    <h1 class="h4 mb-1">Supplier Evaluation</h1>
                    <p class="text-muted small mb-0">
                        Pilih supplier dan periode penilaian untuk menghitung hasil evaluasi, lalu lihat riwayat hasil yang sudah pernah dihitung.
                    </p>
                </div>
            </div>
        </section>

        {{-- FORM FILTER SUPPLIER & PERIODE --}}
        <section class="mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header">
                    <span class="fw-semibold">Filter Supplier & Periode Evaluasi</span>
                </div>

                <div class="card-body">
                    <form action="{{ route('purchasing.evaluationsupplier.calculate') }}"
                          method="POST"
                          class="row g-3 align-items-end"
                          id="evaluation-form">
                        @csrf

                        {{-- SUPPLIER --}}
                        <div class="col-12 col-md-4">
                            <label for="supplier" class="form-label fw-semibold">Supplier</label>
                            <select name="supplier" id="supplier" class="form-select">
                                <option value="">-- Select Supplier --</option>
                                @foreach ($supplierData as $supplier => $years)
                                    <option value="{{ $supplier }}">{{ $supplier }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- TANGGAL MULAI --}}
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Start Period</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <select name="start_month" id="start_month" class="form-select">
                                        <option value="">Month</option>
                                        <option value="January">January</option>
                                        <option value="February">February</option>
                                        <option value="March">March</option>
                                        <option value="April">April</option>
                                        <option value="May">May</option>
                                        <option value="June">June</option>
                                        <option value="July">July</option>
                                        <option value="August">August</option>
                                        <option value="September">September</option>
                                        <option value="October">October</option>
                                        <option value="November">November</option>
                                        <option value="December">December</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <select name="start_year" id="start_year" class="form-select">
                                        <option value="">Year</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-text text-muted small">
                                Bulan & tahun awal periode evaluasi.
                            </div>
                        </div>

                        {{-- TANGGAL AKHIR --}}
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">End Period</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <select name="end_month" id="end_month" class="form-select">
                                        <option value="">Month</option>
                                        <option value="January">January</option>
                                        <option value="February">February</option>
                                        <option value="March">March</option>
                                        <option value="April">April</option>
                                        <option value="May">May</option>
                                        <option value="June">June</option>
                                        <option value="July">July</option>
                                        <option value="August">August</option>
                                        <option value="September">September</option>
                                        <option value="October">October</option>
                                        <option value="November">November</option>
                                        <option value="December">December</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <select name="end_year" id="end_year" class="form-select">
                                        <option value="">Year</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-text text-muted small">
                                Bulan & tahun akhir periode evaluasi.
                            </div>
                        </div>

                        {{-- BUTTON SUBMIT --}}
                        <div class="col-12">
                            <button type="submit" class="btn btn-success w-100 w-md-auto">
                                Calculate Evaluation
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        {{-- LINK KRITERIA --}}
        <section class="mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header">
                    <span class="fw-semibold">Kriteria Penilaian</span>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-12 col-md-4 col-lg-4">
                            <a href="{{ route('kriteria1') }}" class="btn btn-outline-primary w-100">
                                Kualitas Barang dan Kemasan
                            </a>
                        </div>
                        <div class="col-12 col-md-4 col-lg-4">
                            <a href="{{ route('kriteria2') }}" class="btn btn-outline-primary w-100">
                                Ketepatan Kuantitas Barang
                            </a>
                        </div>
                        <div class="col-12 col-md-4 col-lg-4">
                            <a href="{{ route('kriteria3') }}" class="btn btn-outline-primary w-100">
                                Ketepatan Waktu Pengiriman
                            </a>
                        </div>
                        <div class="col-12 col-md-4 col-lg-4">
                            <a href="{{ route('kriteria4') }}" class="btn btn-outline-primary w-100">
                                Kerjasama Permintaan Mendadak
                            </a>
                        </div>
                        <div class="col-12 col-md-4 col-lg-4">
                            <a href="{{ route('kriteria5') }}" class="btn btn-outline-primary w-100">
                                Respon Klaim
                            </a>
                        </div>
                        <div class="col-12 col-md-4 col-lg-4">
                            <a href="{{ route('kriteria6') }}" class="btn btn-outline-primary w-100">
                                Sertifikasi
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- TABEL HEADER EVALUASI --}}
        <section class="mb-4">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <h2 class="h5 mb-0">Supplier Evaluations</h2>
            </div>

            @if ($header->isEmpty())
                <div class="alert alert-secondary small mb-0">
                    Belum ada data evaluasi supplier yang tersimpan.
                </div>
            @else
                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-nowrap">ID</th>
                                        <th class="text-nowrap">Vendor Code</th>
                                        <th class="text-nowrap">Vendor Name</th>
                                        <th class="text-nowrap">Period</th>
                                        <th class="text-nowrap">Grade</th>
                                        <th class="text-nowrap">Status</th>
                                        <th class="text-nowrap text-center">Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($header as $head)
                                        <tr>
                                            <td>{{ $head->id }}</td>
                                            <td>{{ $head->vendor_code }}</td>
                                            <td>{{ $head->vendor_name }}</td>
                                            <td>{{ $head->period ?? $head->year }}</td>
                                            <td>{{ $head->grade }}</td>
                                            <td>{{ $head->status }}</td>
                                            <td class="text-center">
                                                <a href="{{ route('purchasing.evaluationsupplier.details', ['id' => $head->id]) }}"
                                                   target="_blank"
                                                   class="btn btn-sm btn-outline-primary">
                                                    View Details
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </section>
    </div>
@endsection

@push('scripts')

    <script>
        // Supplier -> Tahun
        const supplierYears = @json($supplierData);

        $('#supplier').on('change', function() {
            const supplier = $(this).val();
            const $startYear = $('#start_year');
            const $endYear   = $('#end_year');

            $startYear.empty().append('<option value="">Year</option>');
            $endYear.empty().append('<option value="">Year</option>');

            if (supplier && supplierYears[supplier]) {
                supplierYears[supplier].forEach(function(year) {
                    $startYear.append('<option value="' + year + '">' + year + '</option>');
                    $endYear.append('<option value="' + year + '">' + year + '</option>');
                });
            }
        });

        // Map nama bulan ke index untuk validasi
        const monthMap = {
            'January': 1,
            'February': 2,
            'March': 3,
            'April': 4,
            'May': 5,
            'June': 6,
            'July': 7,
            'August': 8,
            'September': 9,
            'October': 10,
            'November': 11,
            'December': 12
        };

        function validateDateRange() {
            const startMonthName = $('#start_month').val();
            const endMonthName   = $('#end_month').val();
            const startYear      = parseInt($('#start_year').val());
            const endYear        = parseInt($('#end_year').val());

            if (startMonthName && endMonthName && startYear && endYear) {
                const startMonth = monthMap[startMonthName];
                const endMonth   = monthMap[endMonthName];

                if (!startMonth || !endMonth) {
                    return true; // kalau mapping gagal, jangan blok form
                }

                const startDate = new Date(startYear, startMonth - 1, 1);
                const endDate   = new Date(endYear, endMonth - 1, 1);

                if (startDate > endDate) {
                    alert('Start period tidak boleh lebih besar dari end period.');
                    return false;
                }
            }
            return true;
        }

        $('#start_month, #start_year, #end_month, #end_year').on('change', validateDateRange);

        $('#evaluation-form').on('submit', function(e) {
            if (!validateDateRange()) {
                e.preventDefault();
            }
        });
    </script>
@endpush
