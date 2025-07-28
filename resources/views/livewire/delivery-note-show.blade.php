<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0">📄 Delivery Note #{{ $deliveryNote->id }}</h3>
        <a href="{{ route('delivery-notes.index') }}" class="btn btn-outline-secondary">← Back to List</a>
    </div>

    {{-- Basic Info --}}
    <div class="row mb-4">
        <div class="col-md-6">
            <table class="table table-borderless">
                <tr>
                    <th class="text-muted">Branch</th>
                    <td>{{ $deliveryNote->branch }}</td>
                </tr>
                <tr>
                    <th class="text-muted">Ritasi</th>
                    <td>{{ $deliveryNote->ritasi_label }}</td>
                </tr>
                <tr>
                    <th class="text-muted">Delivery Note Date</th>
                    <td>{{ $deliveryNote->formatted_delivery_note_date }}</td>
                </tr>
                <tr>
                    <th class="text-muted">Departure Time</th>
                    <td>{{ $deliveryNote->formatted_departure_time }}</td>
                </tr>
                <tr>
                    <th class="text-muted">Return Time</th>
                    <td>{{ $deliveryNote->formatted_return_time }}</td>
                </tr>
            </table>
        </div>

        <div class="col-md-6">
            <table class="table table-borderless">
                <tr>
                    <th class="text-muted">Vehicle Number</th>
                    <td>{{ $deliveryNote->vehicle_number }}</td>
                </tr>
                <tr>
                    <th class="text-muted">Driver Name</th>
                    <td>{{ $deliveryNote->driver_name }}</td>
                </tr>
                <tr>
                    <th class="text-muted">Approval Flow</th>
                    <td>{{ $deliveryNote->approvalFlow->name ?? '-' }}</td>
                </tr>
                <tr>
                    <th class="text-muted">Status</th>
                    <td>
                        @if ($deliveryNote->status === 'draft')
                            <span class="badge bg-warning text-dark">Draft</span>
                        @else
                            <span class="badge bg-success">Submitted</span>
                        @endif
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <h5 class="fw-semibold">📦 Destinations</h5>

    <table class="table table-bordered mt-3">
        <thead class="table-light">
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
            @forelse ($deliveryNote->destinations as $i => $d)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $d->destination }}</td>

                    <td>
                        @if ($d->deliveryOrders->isNotEmpty())
                            @foreach ($d->deliveryOrders as $order)
                                <span class="badge bg-secondary me-1">
                                    {{ $order->delivery_order_number }}
                                </span>
                            @endforeach
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>

                    <td>{{ $d->remarks ?: '—' }}</td>
                    <td>
                        {{ $d->driver_cost_currency }} {{ number_format($d->driver_cost ?? 0, 2, '.', ',') }}
                    </td>
                    <td>
                        {{ $d->kenek_cost_currency }} {{ number_format($d->kenek_cost ?? 0, 2, '.', ',') }}
                    </td>
                    <td>
                        {{ $d->balikan_cost_currency }} {{ number_format($d->balikan_cost ?? 0, 2, '.', ',') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted">No destinations found.</td>
                </tr>
            @endforelse
            @php
                $driverCurrencies = $deliveryNote->destinations->pluck('driver_cost_currency')->unique();
                $kenekCurrencies = $deliveryNote->destinations->pluck('kenek_cost_currency')->unique();
                $balikanCurrencies = $deliveryNote->destinations->pluck('balikan_cost_currency')->unique();

                $canSumDriver = $driverCurrencies->count() === 1 && $driverCurrencies->first();
                $canSumKenek = $kenekCurrencies->count() === 1 && $kenekCurrencies->first();
                $canSumBalikan = $balikanCurrencies->count() === 1 && $balikanCurrencies->first();

                $totalDriverCost = $canSumDriver ? $deliveryNote->destinations->sum('driver_cost') : null;
                $totalKenekCost = $canSumKenek ? $deliveryNote->destinations->sum('kenek_cost') : null;
                $totalBalikanCost = $canSumBalikan ? $deliveryNote->destinations->sum('balikan_cost') : null;
            @endphp

            <tr class="table-light fw-bold">
                <td colspan="4" class="text-end">Total</td>
                <td>
                    @if ($canSumDriver)
                        {{ $driverCurrencies->first() }} {{ number_format($totalDriverCost, 2, '.', ',') }}
                    @else
                        <span class="text-danger">Cannot sum (mixed currency)</span>
                    @endif
                </td>
                <td>
                    @if ($canSumKenek)
                        {{ $kenekCurrencies->first() }} {{ number_format($totalKenekCost, 2, '.', ',') }}
                    @else
                        <span class="text-danger">Cannot sum (mixed currency)</span>
                    @endif
                </td>
                <td>
                    @if ($canSumBalikan)
                        {{ $balikanCurrencies->first() }} {{ number_format($totalBalikanCost, 2, '.', ',') }}
                    @else
                        <span class="text-danger">Cannot sum (mixed currency)</span>
                    @endif
                </td>
            </tr>
        </tbody>
    </table>


</div>
