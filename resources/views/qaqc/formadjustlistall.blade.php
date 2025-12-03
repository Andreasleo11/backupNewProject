@extends('new.layouts.app')

@section('content')
    <div class="container py-4">

        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h1 class="h3 mb-1">Form Adjust</h1>
                <p class="mb-0 text-muted small">
                    Daftar Form Adjust hasil verifikasi QC/QA.
                </p>
            </div>

            @if ($datas->count())
                <span class="badge rounded-pill text-bg-light">
                    Total: {{ $datas->count() }}
                </span>
            @endif
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr class="small text-muted">
                                <th class="ps-3">#</th>
                                <th>Report</th>
                                <th>Customer</th>
                                <th>Invoice</th>
                                <th class="text-center">Status</th>
                                <th class="text-end pe-3">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($datas as $index => $report)
                                <tr>
                                    {{-- Row number --}}
                                    <td class="ps-3">
                                        {{ $index + 1 }}
                                    </td>

                                    {{-- Report / IDs --}}
                                    <td>
                                        <div class="fw-semibold">
                                            Report #{{ $report->report_id }}
                                        </div>
                                        <div class="text-muted small">
                                            Form Adjust ID: {{ $report->id }}
                                        </div>
                                    </td>

                                    {{-- Customer --}}
                                    <td>
                                        {{ $report->report->customer }}
                                    </td>

                                    {{-- Invoice --}}
                                    <td>
                                        {{ $report->report->invoice_no }}
                                    </td>

                                    {{-- Status --}}
                                    <td class="text-center">
                                        @php
                                            $allSignedExceptDirector =
                                                is_null($report->autograph_7) &&
                                                !is_null($report->autograph_6) &&
                                                !is_null($report->autograph_5) &&
                                                !is_null($report->autograph_4) &&
                                                !is_null($report->autograph_3) &&
                                                !is_null($report->autograph_2) &&
                                                !is_null($report->autograph_1);
                                        @endphp

                                        @if ($report->autograph_7)
                                            <span class="badge rounded-pill text-bg-success px-3 py-2">
                                                APPROVED
                                            </span>
                                        @elseif ($allSignedExceptDirector)
                                            <span class="badge rounded-pill text-bg-warning text-dark px-3 py-2">
                                                Waiting for Director
                                            </span>
                                        @else
                                            <span class="badge rounded-pill text-bg-secondary px-3 py-2">
                                                Waiting Signatures
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Action --}}
                                    <td class="text-end pe-3">
                                        <a href="{{ route('adjustview', ['report_id' => $report->report_id]) }}"
                                            class="btn btn-sm btn-outline-secondary">
                                            <i class='bx bx-info-circle'></i>
                                            <span class="d-none d-sm-inline ms-1">Detail</span>
                                        </a>

                                        {{-- DEV ONLY
                                        <a href="{{ route('qaqc.report.preview', $report->id) }}"
                                           class="btn btn-sm btn-outline-primary ms-1">
                                            Preview
                                        </a>
                                        --}}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-5 text-center text-muted">
                                        <i class='bx bx-file-blank fs-2 d-block mb-2'></i>
                                        <div class="fw-semibold">Belum ada Form Adjust</div>
                                        <div class="small">
                                            Form Adjust akan muncul di sini setelah proses verifikasi selesai.
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if (method_exists($datas, 'links'))
                <div class="card-footer border-top-0 bg-white py-3">
                    {{ $datas->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
