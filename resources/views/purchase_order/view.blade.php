@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="">Detail Purchase Order</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('po.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('po.index') }}">List</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $purchaseOrder->po_number }}</li>
            </ol>
        </nav>
        <a href="{{ route('po.export.pdf', $purchaseOrder->id) }}" class="btn btn-sm btn-outline-success mb-3">
            Export PDF
        </a>
    </div>

    <div class="container border p-4 bg-white col-md-6">
        <div class="row mb-1 d-flex justify-content-center align-items-center">
            <div class="col-auto">
                <h6 class="fw-bold text-uppercase fs-5">{{ $purchaseOrder->vendor_name }}</h6>
            </div>
            <div class="col text-center fs-5">
                <span class="fw-bold">{{ $purchaseOrder->vendor_code }}</span>
            </div>
            <div class="col-auto">
                <table class="table table-borderless text-start">
                    <tr>
                        <td class="fw-bold">Date</td>
                        <td>{{ \Carbon\Carbon::parse($purchaseOrder->posting_date)->format('d.m.y') }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">To</td>
                        <td>{{ $purchaseOrder->contact_person_name }}</td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="row mb-1">
            <div class="col-6">
                <table class="table table-borderless">
                    <tr>
                        <td class="text-nowrap">Bill To</td>
                        <td>{{ $purchaseOrder->bill_to }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="row mb-5">
            <h5 class="fw-bold mb-1">PT Daijo Industrial</h5>
            <div class="col-6">
                <table class="table table-borderless">
                    <tr>
                        <td class="text-nowrap">Ship To</td>
                        <td>{{ $purchaseOrder->ship_to }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <h5 class="text-center fw-bold fs-4">
            PURCHASE ORDER {{ $purchaseOrder->po_number }}
        </h5>
        <div class="text-center mb-3">
            @include('partials.po-status', ['po' => $purchaseOrder])
        </div>

        <div class="table-responsive mb-5">
            <table class="table table-bordered table-sm align-middle text-center">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Item No.</th>
                        <th>Description</th>
                        <th>UoM</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $total = 0;
                    @endphp
                    @forelse ($purchaseOrder->items as $index => $item)
                        @php
                            $total += $item->quantity * $item->price;
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->code }}</td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->uom }}</td>
                            <td>{{ number_format($item->quantity) }}</td>
                            <td>IDR {{ number_format($item->price) }}</td>
                            <td>IDR {{ number_format($item->quantity * $item->price) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">No Items Found.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="border-0">
                    <tr class="border-0">
                        <td colspan="6" class="text-end fw-bold border-0 pe-3">Total</td>
                        <td class="fw-bold border border-bottom text-center">IDR {{ number_format($total) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="row mt-5 pt-5 pb-5">
            <div class="col-auto">
                <table class="table table-borderless">
                    <tr>
                        <td class="text-nowrap"><strong>PO No</strong></td>
                        <td>{{ $purchaseOrder->po_number }}</td>
                    </tr>
                    <tr>
                        <td class="text-nowrap"><strong>Delivery Date</strong></td>
                        <td>{{ $purchaseOrder->delivery_date }}</td>
                    </tr>
                    <tr>
                        <td class="text-nowrap"><strong>Purchaser</strong></td>
                        <td>{{ $purchaseOrder->sales_employee_name }}</td>
                    </tr>
                    <tr>
                        <td class="text-nowrap"><strong>Payment Terms</strong></td>
                        <td>{{ $purchaseOrder->payment_terms }}</td>
                    </tr>
                    <tr>
                        <td class="text-nowrap"><strong>Origin</strong></td>
                        <td>{{ $purchaseOrder->remarks }}</td>
                    </tr>
                </table>
            </div>
            <div class="col"></div>
            <div class="col-auto">
                <table class="table table-borderless">
                    <tr>
                        <td class="text-nowrap"><strong>Total Bef. Tax</strong></td>
                        <td>IDR {{ number_format($total) }}</td>
                    </tr>
                    <tr>
                        <td class="text-nowrap"><strong>Total Tax</strong></td>
                        <td>IDR {{ number_format($purchaseOrder->total_tax) }}</td>
                    </tr>
                    <tr>
                        <td class="text-nowrap"><strong>Grand Total</strong></td>
                        <td><strong>IDR {{ number_format($purchaseOrder->total) }}</strong></td>
                    </tr>
                </table>
            </div>
        </div>

        @if ($purchaseOrder->is_need_sign)
            <div class="row">
                <div class="col-6">
                    <p class="mb-0"><strong>Disetujui oleh</strong>: </p>
                    <p class="mb-0"><strong>Approved By</strong>: </p>
                    @if ($purchaseOrder->approved_image)
                        <img src="{{ asset('autographs/' . $purchaseOrder->approved_image) }}" alt="approved_image"
                            srcset="">
                    @else
                        <p class="mt-5">__________________________</p>
                    @endif
                </div>
            </div>
        @endif
    </div>
@endsection
