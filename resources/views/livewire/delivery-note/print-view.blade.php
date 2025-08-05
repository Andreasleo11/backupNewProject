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
                margin-bottom: 8px;
            }

            .badge {
                font-size: 9px;
                padding: 2px 4px;
            }

            .table th,
            .table td {
                padding: 0.25rem 0.4rem;
                font-size: 10px;
            }

            .signature-box {
                height: 50px;
                border-bottom: 1px solid #000;
                margin: 0 auto;
                width: 80%;
            }

            .signature-name-line {
                display: inline-block;
                width: 60%;
                max-width: 250px;
                border-bottom: 1px dotted #000;
                height: 1.5em;
            }

            .table-sm th,
            .table-sm td {
                padding: 0.25rem;
            }

            @media print {
                .no-print {
                    display: none !important;
                }

                @page {
                    size: A5 landscape;
                    margin: 8mm;
                }

                html,
                body {
                    width: 100%;
                    height: 100%;
                    margin: 0;
                    padding: 0;
                    font-size: 10px;
                    overflow: hidden;
                }

                .print-section {
                    transform: scale(1);
                    transform-origin: top left;
                    max-height: 100%;
                    page-break-inside: avoid;
                }

                /* Auto-scale if content is too long (approximate) */
                .print-section.long-content {
                    transform: scale(0.9);
                }

                .print-section.very-long-content {
                    transform: scale(0.8);
                }

                /* Optional: Force font reduction based on body height (approx) */
                body.long-content {
                    font-size: 9px;
                }

                body.very-long-content {
                    font-size: 8px;
                }
            }

            .wrap-remark {
                white-space: normal;
                word-wrap: break-word;
                max-width: 20%;
                /* optional: adjust if you want limit */
            }
        </style>
    @endpush

    <div>
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="fw-bold">📄 Delivery Note #{{ $deliveryNote->id }} (Surat Jalan Ritasi)</h3>
            <button onclick="window.print()" class="btn btn-primary no-print">🖨️ Print</button>
        </div>

        <div class="row">
            <div class="col-6">
                <table class="table table-sm table-borderless mb-2">
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
                </table>
            </div>
            <div class="col-6">
                <table class="table table-sm table-borderless mb-2">
                    <tr>
                        <th>Departure:</th>
                        <td>{{ $deliveryNote->formatted_departure_time }}</td>
                    </tr>
                    <tr>
                        <th>Vehicle:</th>
                        <td>{{ $deliveryNote->vehicle->plate_number ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Driver:</th>
                        <td>{{ $deliveryNote->vehicle->driver_name ?? '-' }}</td>
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
                        <td class="wrap-remark">{{ $d->remarks ?: '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="row mt-3 text-center justify-content-around">
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const section = document.querySelector('.print-section');
        const body = document.body;
        const height = section.scrollHeight;

        // Thresholds based on trial-and-error for A5 landscape
        if (height > 600) {
            section.classList.add('long-content');
            body.classList.add('long-content');
        }

        if (height > 700) {
            section.classList.remove('long-content');
            section.classList.add('very-long-content');
            body.classList.remove('long-content');
            body.classList.add('very-long-content');
        }
    });
</script>
