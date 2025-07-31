<div class="print-section">
    @push('styles')
        <style>
            body {
                font-size: 11px;
                padding: 15px;
            }

            h3,
            h5 {
                font-size: 14px;
                margin-bottom: 10px;
            }

            table {
                margin-bottom: 12px;
            }

            th {
                width: 110px;
            }

            .badge {
                font-size: 10px;
                padding: 2px 4px;
            }

            @media print {
                .no-print {
                    display: none !important;
                }

                @page {
                    size: A4 portrait;
                    margin: 10mm;
                }

                body {
                    margin: 0;
                    padding: 0;
                    font-size: 10px;
                }

                .print-section {
                    page-break-inside: avoid;
                    margin-bottom: 12mm;
                }

                .print-section+.print-section {
                    border-top: 1px dashed #ccc;
                    padding-top: 10px;
                }
            }

            .signature-box {
                height: 70px;
                border-bottom: 1px solid #000;
                margin: 0 auto;
                width: 80%;
            }

            .signature-name-line {
                display: inline-block;
                width: 60%;
                max-width: 250px;
                border-bottom: 1px dotted #000;
                height: 2em;
            }
        </style>
    @endpush
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="fw-bold">📄 Delivery Note #{{ $deliveryNote->id }}</h3>
            <button onclick="window.print()" class="btn btn-primary no-print">🖨️ Print</button>
        </div>

        <div class="row">
            <div class="col-6">
                <table class="table table-sm table-borderless mb-4">
                    <tr>
                        <th>Branch:</th>
                        <td>{{ $deliveryNote->branch }}</td>
                    </tr>
                    <tr>
                        <th>Ritasi:</th>
                        <td>{{ $deliveryNote->ritasi_label }}</td>
                    </tr>
                    <tr>
                        <th>Date:</th>
                        <td>{{ $deliveryNote->formatted_delivery_note_date }}</td>
                    </tr>
                    <tr>
                        <th>Departure:</th>
                        <td>{{ $deliveryNote->formatted_departure_time }}</td>
                    </tr>
                </table>
            </div>

            <div class="col-6">
                <table class="table table-sm table-borderless mb-4">
                    <tr>
                        <th>Return:</th>
                        <td>{{ $deliveryNote->formatted_return_time }}</td>
                    </tr>
                    <tr>
                        <th>Vehicle:</th>
                        <td>{{ $deliveryNote->vehicle->plate_number ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Driver:</th>
                        <td>{{ $deliveryNote->vehicle->driver_name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td>{{ ucfirst($deliveryNote->status) }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <h5>📦 Destinations</h5>
        <table class="table table-bordered table-sm">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Destination</th>
                    <th>Delivery Orders</th>
                    <th>Remarks</th>
                    <th>Driver Cost</th>
                    <th>Kenek Cost</th>
                    <th>Balikan Cost</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($deliveryNote->destinations as $i => $d)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $d->destination }}</td>
                        <td>
                            @foreach ($d->deliveryOrders as $order)
                                <span class="badge bg-secondary">{{ $order->delivery_order_number }}</span>
                            @endforeach
                        </td>
                        <td>{{ $d->remarks ?: '—' }}</td>
                        <td>{{ $d->driver_cost_currency }} {{ number_format($d->driver_cost, 2) }}</td>
                        <td>{{ $d->kenek_cost_currency }} {{ number_format($d->kenek_cost, 2) }}</td>
                        <td>{{ $d->balikan_cost_currency }} {{ number_format($d->balikan_cost, 2) }}</td>
                    </tr>
                @endforeach
                @php
                    $groupedTotals = $deliveryNote->destinations->reduce(
                        function ($carry, $d) {
                            $carry['driver'][$d->driver_cost_currency] =
                                ($carry['driver'][$d->driver_cost_currency] ?? 0) + ($d->driver_cost ?? 0);
                            $carry['kenek'][$d->kenek_cost_currency] =
                                ($carry['kenek'][$d->kenek_cost_currency] ?? 0) + ($d->kenek_cost ?? 0);
                            $carry['balikan'][$d->balikan_cost_currency] =
                                ($carry['balikan'][$d->balikan_cost_currency] ?? 0) + ($d->balikan_cost ?? 0);
                            return $carry;
                        },
                        ['driver' => [], 'kenek' => [], 'balikan' => []],
                    );
                @endphp
                <tr class="table-light fw-bold">
                    <td colspan="4" class="text-end">Total</td>
                    <td>
                        @foreach ($groupedTotals['driver'] as $currency => $amount)
                            <div>{{ $currency }} {{ number_format($amount, 2) }}</div>
                        @endforeach
                    </td>
                    <td>
                        @foreach ($groupedTotals['kenek'] as $currency => $amount)
                            <div>{{ $currency }} {{ number_format($amount, 2) }}</div>
                        @endforeach
                    </td>
                    <td>
                        @foreach ($groupedTotals['balikan'] as $currency => $amount)
                            <div>{{ $currency }} {{ number_format($amount, 2) }}</div>
                        @endforeach
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="row mt-5 text-center justify-content-around">
            <div class="col-4">
                <p><strong>PIC</strong></p>
                <div class="signature-box"></div>
                (<span class="signature-name-line"></span>)
            </div>
            <div class="col-4">
                <p><strong>Security</strong></p>
                <div class="signature-box"></div>
                (<span class="signature-name-line"></span>)
            </div>
        </div>
    </div>
</div>
