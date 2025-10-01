@extends('layouts.app')

@section('content')
    <div class="container mt-3">
        <div class="row justify-content-center">
            <div class="col">
                <div class="card">
                    <div class="card-body p-0">
                        <div class="container">
                            <div class="row justify-content-center">
                                <div class="col-flex-grow-1 border-bottom p-0">
                                    <div class="pt-4 px-4">
                                        <div class="mb-4">
                                            <span class="h3">Add Part Defects</span>
                                            <p class="text-secondary mt-2">Tambahkan Raw Material yang bisa diadjust untuk
                                                FG berikut</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-3 p-4 border-end">
                                    <h5 class="pb-2">1. Select Part Detail</h5>
                                    <div class="list-group" id="list-tab" role="tablist">
                                        @foreach ($datas->details as $detail)
                                            <a class="list-group-item list-group-item-action @if (session('active_tab') == $detail->id) active @endif"
                                                id="list-detail-{{ $detail->id }}-list" data-bs-toggle="list"
                                                href="#list-detail{{ $detail->id }}" role="tab"
                                                aria-controls="list-detail{{ $detail->id }}">{{ $detail->part_name }}</a>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="col-9 p-4">
                                    @foreach ($datas->details as $index => $detail)
                                        <div class="tab-content" id="nav-tabContent">
                                            <div class="tab-pane fade show @if (session('active_tab') == $detail->id) active @endif"
                                                id="list-detail{{ $detail->id }}" role="tabpanel">
                                                <div class="mb-3 row">
                                                    <div class="col">
                                                        <h5>2. Add Raw Material Adjust for <span
                                                                class="fw-semibold">{{ $detail->part_name }}</span> </h5>
                                                    </div>
                                                    <div class="col-auto">
                                                        @php
                                                            $ename = explode('/', $detail->part_name);
                                                            $final = $ename[0];
                                                        @endphp

                                                        @foreach ($masterDataCollection as $masterData)
                                                            @if ($masterData->fg_code == $final)
                                                            @endif
                                                        @endforeach
                                                        @include('partials.add_fgwarehouse_modal')
                                                        <button class="btn btn-outline-primary btn-sm"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#add-fgwarehouse-modal-{{ $detail->id }}">
                                                            + Add FG Warehouse
                                                        </button>
                                                        @include('partials.add_rawmaterial_modal')
                                                        <button class="btn btn-outline-primary btn-sm"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#add-rawmaterial-modal-{{ $detail->id }}">
                                                            + Add Raw Material
                                                        </button>

                                                    </div>
                                                </div>

                                                <div class="table-responsive">
                                                    <table class="table table-striped table-bordered table-sm">
                                                        <thead class="text-center align-middle">
                                                            <tr>
                                                                <th class="py-3">#</th>
                                                                <th>Raw Material Code</th>
                                                                <th>Raw Material Description</th>
                                                                <th>Quantity</th>
                                                                <th>Measure</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @forelse($detail->adjustdetail as $adjust)
                                                                <tr class="text-center align-middle">
                                                                    <td>{{ $loop->iteration }}</td>
                                                                    <td>{{ $adjust->rm_code }}</td>
                                                                    <td>{{ $adjust->rm_description }}
                                                                    </td>
                                                                    <td>{{ number_format($adjust->rm_quantity, 5) }}
                                                                    </td>
                                                                    <td>{{ $adjust->rm_measure }}</td>
                                                                </tr>
                                                            @empty
                                                                <tr>
                                                                    <td colspan="5" class="text-center">No Data</td>
                                                                </tr>
                                                            @endforelse
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="d-flex justify-content-between border-top p-3">
                                    <div class="">
                                        <a href="{{ route('qaqc.report.detail', ['id' => $datas->id]) }}"
                                            class="btn btn-secondary">Back</a>
                                    </div>

                                    <div class="d-flex">
                                        <form action="{{ route('adjustview') }}" method="get">
                                            <input type="hidden" name="report_id" value="{{ $datas->id }}">
                                            <button type="submit" class="btn btn-success" id="finishBtn">Finish</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
