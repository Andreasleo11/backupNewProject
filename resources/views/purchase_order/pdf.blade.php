<!DOCTYPE html>
<html lang="en">

<head>
    <title>DISS | Export to PDF</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 20px;
        }

        .container {
            width: 100%;
            margin: 0 auto;
        }

        .row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -5px;
            position: relative;
        }

        .col {
            flex: 1;
            padding: 0 5px;
        }

        .col-6 {
            flex: 0 0 50%;
            max-width: 50%;
            padding: 0 5px;
            display: inline-block;
        }

        .col-auto {
            flex-shrink: 1;
            min-width: 0;
        }

        .col-12 {
            flex: 0 0 100%;
            max-width: 100%;
            padding: 0 5px;
        }

        .text-start {
            text-align: left;
        }

        .text-center {
            text-align: center;
        }

        .text-end {
            text-align: right;
        }

        .fw-bold {
            font-weight: bold;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }

        .table th,
        .table td {
            padding: 8px;
            border: 1px solid #000;
            vertical-align: middle;
        }

        .table-borderless th,
        .table-borderless td {
            border: none !important;
        }

        .table-sm th,
        .table-sm td {
            padding: 5px;
        }

        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }

        .text-nowrap {
            white-space: nowrap;
        }

        .align-middle {
            vertical-align: middle;
        }

        .border-0 {
            border: 0 !important;
        }

        .border {
            border: 1px solid #000;
        }

        .border-bottom {
            border-bottom: 1px solid #000;
        }

        .mb-0 {
            margin-bottom: 0;
        }

        .mb-1 {
            margin-bottom: 0.25rem;
        }

        .mb-5 {
            margin-bottom: 3rem;
        }

        .mt-5 {
            margin-top: 3rem;
        }

        .pt-5 {
            padding-top: 3rem;
        }

        .pb-5 {
            padding-bottom: 3rem;
        }

        .pe-3 {
            padding-right: 1rem;
        }

        .my-3 {
            margin-top: 1rem;
            margin-bottom: 1rem;
        }

        img {
            max-width: 100%;
            height: auto;
        }

        .fs-5 {
            font-size: 1.25rem;
        }

        .fs-4 {
            font-size: 1.5rem;
        }
    </style>

</head>

<body>
    <div class="container">
        <div style="display: flex; justify-content: flex-end; width: 100%;">
            <table class="table table-borderless text-start mb-0"
                style="width: auto; table-layout: auto; max-width: 100%;">
                <tr>
                    <td class="fw-bold text-nowrap">Date</td>
                    <td class="text-nowrap">
                        {{ \Carbon\Carbon::parse($purchaseOrder->posting_date)->format('d.m.y') }}</td>
                </tr>
                <tr>
                    <td class="fw-bold text-nowrap">To</td>
                    <td class="text-nowrap">{{ $purchaseOrder->contact_person_name }}</td>
                </tr>
            </table>
        </div>
        <div class="row mb-1"
            style="display: flex; flex-wrap: nowrap; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <div class="col-auto text-start" style="flex: 0 0 auto; min-width: 0;">
                <h6 class="fw-bold fs-5 mb-0" style="display:inline-block; max-width: 100%;">
                    {{ $purchaseOrder->vendor_name }}
                    <span>({{ $purchaseOrder->vendor_code }})</span>
                </h6>
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
            <h6 class="fw-bold mb-1 fs-5">PT Daijo Industrial</h6>
            <div class="col-6">
                <table class="table table-borderless">
                    <tr>
                        <td class="text-nowrap">Ship To</td>
                        <td>{{ $purchaseOrder->ship_to }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <h5 class="text-center fw-bold my-3 fs-4">
            PURCHASE ORDER {{ $purchaseOrder->po_number }}
        </h5>

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
            <div class="col-12">
                <table class="table table-borderless" style="width: 100%;">
                    <tbody>
                        <tr>
                            <td class="text-nowrap"><strong>PO No</strong></td>
                            <td class="text-nowrap">{{ $purchaseOrder->po_number }}</td>
                            <td class="text-nowrap"><strong>Total Bef. Tax</strong></td>
                            <td class="text-nowrap">IDR {{ number_format($total) }}</td>
                        </tr>
                        <tr>
                            <td class="text-nowrap"><strong>Delivery Date</strong></td>
                            <td class="text-nowrap">{{ $purchaseOrder->delivery_date }}</td>
                            <td class="text-nowrap"><strong>Total Tax</strong></td>
                            <td class="text-nowrap">IDR {{ number_format($purchaseOrder->total_tax) }}</td>
                        </tr>
                        <tr>
                            <td class="text-nowrap"><strong>Purchaser</strong></td>
                            <td class="text-nowrap">{{ $purchaseOrder->sales_employee_name }}</td>
                            <td class="text-nowrap"><strong>Grand Total</strong></td>
                            <td class="text-nowrap"><strong>IDR {{ number_format($purchaseOrder->total) }}</strong>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-nowrap"><strong>Payment Terms</strong></td>
                            <td class="text-nowrap">{{ $purchaseOrder->payment_terms }}</td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td class="text-nowrap"><strong>Origin</strong></td>
                            <td class="text-nowrap">{{ $purchaseOrder->remark }}</td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>

        @if ($purchaseOrder->approved_image)
            <div class="row">
                <div class="col-6">
                    <p class="mb-0"><strong>Disetujui oleh</strong>: </p>
                    <p class="mb-0"><strong>Approved By</strong>: </p>
                    <img src="{{ public_path('autographs/' . $purchaseOrder->approved_image) }}" alt="approved_image"
                        srcset="">
                </div>
            </div>
        @endif
    </div>
</body>

</html>
