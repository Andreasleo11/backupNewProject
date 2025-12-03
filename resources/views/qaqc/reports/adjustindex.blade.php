@extends('new.layouts.app')

@section('content')
    <div class="container py-3 py-lg-4">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <div class="row g-0">
                            {{-- Header --}}
                            <div class="col-12 border-bottom px-4 pt-4 pb-3">
                                <div class="d-flex flex-column">
                                    <span class="h4 mb-1">Add Part Defects</span>
                                    <p class="text-muted mb-0">
                                        Tambahkan raw material yang bisa di-adjust untuk setiap FG di bawah.
                                    </p>
                                </div>
                            </div>

                            @php
                                $activeId = session('active_tab') ?? optional($datas->details->first())->id;
                            @endphp

                            {{-- Sidebar: list part detail --}}
                            <div class="col-12 col-lg-3 border-end px-4 py-4">
                                <h6 class="text-muted text-uppercase fw-semibold mb-2" style="letter-spacing: .05em;">
                                    1. Select Part Detail
                                </h6>
                                <div class="list-group small" id="part-detail-list" role="tablist">
                                    @foreach ($datas->details as $detail)
                                        <a class="list-group-item list-group-item-action d-flex align-items-center justify-content-between @if ($activeId == $detail->id) active @endif"
                                            id="list-detail-{{ $detail->id }}-list" data-bs-toggle="list"
                                            href="#list-detail{{ $detail->id }}" role="tab"
                                            aria-controls="list-detail{{ $detail->id }}">
                                            <span class="text-truncate">{{ $detail->part_name }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Main content: tab panes --}}
                            <div class="col-12 col-lg-9 px-4 py-4">
                                <div class="tab-content" id="nav-tabContent">
                                    @foreach ($datas->details as $detail)
                                        <div class="tab-pane fade @if ($activeId == $detail->id) show active @endif"
                                            id="list-detail{{ $detail->id }}" role="tabpanel"
                                            aria-labelledby="list-detail-{{ $detail->id }}-list">

                                            {{-- Section header --}}
                                            <div
                                                class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-2">
                                                <div>
                                                    <h6 class="mb-1">
                                                        2. Add Raw Material Adjust for
                                                        <span class="fw-semibold">{{ $detail->part_name }}</span>
                                                    </h6>
                                                    <small class="text-muted">
                                                        Pilih / tambah raw material yang akan digunakan sebagai penyesuaian
                                                        untuk FG ini.
                                                    </small>
                                                </div>

                                                <div class="d-flex flex-wrap gap-2">
                                                    <div class="modal fade" id="add-fgwarehouse-modal-{{ $detail->id }}"
                                                        tabindex="-1"
                                                        aria-labelledby="fgWarehouseLabel-{{ $detail->id }}"
                                                        aria-hidden="true">
                                                        <div class="modal-dialog modal-dialog-centered modal-sm">
                                                            <div class="modal-content border-0 shadow-lg">

                                                                <div class="modal-header border-bottom-0 pb-0">
                                                                    <div>
                                                                        <h5 class="modal-title fw-semibold"
                                                                            id="fgWarehouseLabel-{{ $detail->id }}">
                                                                            Add FG Warehouse
                                                                        </h5>
                                                                        <p class="mb-0 mt-1 small text-muted">
                                                                            Pilih lokasi warehouse untuk FG
                                                                            <span
                                                                                class="fw-semibold">{{ $detail->part_name }}</span>.
                                                                        </p>
                                                                    </div>
                                                                    <button type="button" class="btn-close"
                                                                        data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>

                                                                <form method="POST"
                                                                    action="{{ route('fgwarehousesave') }}">
                                                                    @csrf
                                                                    <input type="hidden" name="detail_id"
                                                                        value="{{ $detail->id }}">

                                                                    <div class="modal-body">
                                                                        @php
                                                                            $warehouses = [
                                                                                '01',
                                                                                'CFC',
                                                                                'CMS',
                                                                                'CMSO',
                                                                                'FFA',
                                                                                'FFI',
                                                                                'FFM',
                                                                                'FFS',
                                                                                'FG',
                                                                                'FT',
                                                                                'IN6',
                                                                                'IND',
                                                                                'KRCMS',
                                                                                'KRFG',
                                                                                'KRRJCT',
                                                                                'KRRM',
                                                                                'KRWIP',
                                                                                'MLD',
                                                                                'MLDCPG',
                                                                                'QCFT',
                                                                                'QCRM',
                                                                                'RFA',
                                                                                'RFI',
                                                                                'RFM',
                                                                                'RFS',
                                                                                'RJCT',
                                                                                'RM',
                                                                                'RMC',
                                                                                'RYCL',
                                                                                'SMP',
                                                                                'SUB-F',
                                                                                'SUB-W',
                                                                                'WFA',
                                                                                'WFI',
                                                                                'WFM',
                                                                                'WFS',
                                                                                'WIP',
                                                                                'WOS',
                                                                            ];

                                                                            $selectedWarehouse = old(
                                                                                'fg_warehouse',
                                                                                $detail->fg_warehouse_name,
                                                                            );
                                                                        @endphp

                                                                        <div class="mb-3">
                                                                            <label for="fg_warehouse_{{ $detail->id }}"
                                                                                class="form-label small fw-semibold">
                                                                                FG Warehouse
                                                                            </label>
                                                                            <select id="fg_warehouse_{{ $detail->id }}"
                                                                                name="fg_warehouse"
                                                                                class="form-select form-select-sm">
                                                                                <option value="" disabled
                                                                                    {{ $selectedWarehouse ? '' : 'selected' }}>
                                                                                    -- Pilih warehouse --
                                                                                </option>

                                                                                @foreach ($warehouses as $warehouse)
                                                                                    <option value="{{ $warehouse }}"
                                                                                        {{ $selectedWarehouse === $warehouse ? 'selected' : '' }}>
                                                                                        {{ $warehouse }}
                                                                                    </option>
                                                                                @endforeach
                                                                            </select>
                                                                        </div>

                                                                        <p class="small text-muted mb-0">
                                                                            Kode mengikuti kode warehouse di sistem
                                                                            (SAP/WMS)
                                                                            .
                                                                        </p>
                                                                    </div>

                                                                    <div class="modal-footer border-top-0 pt-0">
                                                                        <button type="button" class="btn btn-light"
                                                                            data-bs-dismiss="modal">
                                                                            Cancel
                                                                        </button>
                                                                        <button type="submit" class="btn btn-primary">
                                                                            Save
                                                                        </button>
                                                                    </div>
                                                                </form>

                                                            </div>
                                                        </div>
                                                    </div>
                                                    <button type="button" class="btn btn-outline-primary btn-sm"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#add-fgwarehouse-modal-{{ $detail->id }}">
                                                        + FG Warehouse
                                                    </button>
                                                </div>
                                            </div>

                                            {{-- Table --}}
                                            <div class="table-responsive">
                                                <table
                                                    class="table table-sm table-striped table-bordered align-middle mb-0">
                                                    <thead class="table-light text-center">
                                                        <tr>
                                                            <th style="width: 40px;">#</th>
                                                            <th style="min-width: 140px;">Raw Material Code</th>
                                                            <th style="min-width: 220px;">Raw Material Description</th>
                                                            <th style="min-width: 120px;">Quantity</th>
                                                            <th style="min-width: 80px;">Measure</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse ($detail->adjustdetail as $adjust)
                                                            <tr class="text-center">
                                                                <td>{{ $loop->iteration }}</td>
                                                                <td class="text-nowrap">{{ $adjust->rm_code }}</td>
                                                                <td class="text-start">{{ $adjust->rm_description }}</td>
                                                                <td>{{ number_format($adjust->rm_quantity, 5) }}</td>
                                                                <td>{{ $adjust->rm_measure }}</td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="5" class="text-center text-muted py-3">
                                                                    No raw material adjust data yet.
                                                                </td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Footer actions --}}
                            <div
                                class="d-flex justify-content-between align-items-center border-top px-4 py-3 bg-light mt-2">
                                <a href="{{ route('qaqc.report.detail', ['id' => $datas->id]) }}"
                                    class="btn btn-outline-secondary">
                                    Back
                                </a>

                                <form action="{{ route('adjustview') }}" method="get" class="mb-0">
                                    <input type="hidden" name="report_id" value="{{ $datas->id }}">
                                    <button type="submit" class="btn btn-success" id="finishBtn">
                                        Finish &amp; View Adjust Form
                                    </button>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
