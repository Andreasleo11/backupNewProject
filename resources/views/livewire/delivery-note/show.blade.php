<div class="container py-4">

    {{-- Header with status badge --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0">
            📄 Delivery Note #{{ $deliveryNote->id }}
            @if ($deliveryNote->status === 'draft')
                <span class="badge bg-warning text-dark ms-2">Draft</span>
            @else
                <span class="badge bg-success ms-2">Submitted</span>
            @endif
        </h3>
        <div class="d-flex gap-2">
            <a href="{{ route('delivery-notes.print', $deliveryNote->id) }}" target="_blank"
                class="btn btn-outline-success">
                🖨️ Print
            </a>
            @if (auth()->check() || $deliveryNote->is_latest)
                <a href="{{ route('delivery-notes.edit', $deliveryNote->id) }}" class="btn btn-outline-primary">
                    ✏️ Edit
                </a>
            @endif
            <a href="{{ route('delivery-notes.index') }}" class="btn btn-outline-secondary">← Back to List</a>
        </div>
    </div>

    {{-- Basic Info Card --}}
    <div class="card mb-4">
        <div class="card-body row">
            <div class="col-md-6 mb-3">
                <h6 class="text-muted">🔁 Ritasi</h6>
                <p class="mb-3">{{ $deliveryNote->ritasi_label }}</p>

                <h6 class="text-muted">📅 Delivery Note Date</h6>
                <p class="mb-3">{{ $deliveryNote->formatted_delivery_note_date }}</p>

                <h6 class="text-muted">⏰ Departure Time</h6>
                <p class="mb-3">{{ $deliveryNote->formatted_departure_time }}</p>

                <h6 class="text-muted">⏳ Return Time</h6>
                <p class="mb-0">{{ $deliveryNote->formatted_return_time }}</p>
            </div>

            <div class="col-md-6 mb-3">
                <h6 class="text-muted">🏢 Branch</h6>
                <p class="mb-3">{{ $deliveryNote->branch }}</p>

                <h6 class="text-muted">🚚 Vehicle Number</h6>
                <p class="mb-3">{{ $deliveryNote->vehicle->plate_number ?? '-' }}</p>

                <h6 class="text-muted">👨‍✈️ Driver Name</h6>
                <p class="mb-0">{{ $deliveryNote->vehicle->driver_name ?? '-' }}</p>
            </div>
        </div>
    </div>

    {{-- Destinations --}}
    <h5 class="fw-semibold">📦 Destinations</h5>

    <div class="table-responsive">
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
                                    <span class="badge bg-secondary me-1"
                                        title="Order #{{ $order->delivery_order_number }}">
                                        {{ $order->delivery_order_number }}
                                    </span>
                                @endforeach
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if ($d->remarks)
                                <span
                                    title="{{ $d->remarks }}">{{ \Illuminate\Support\Str::limit($d->remarks, 40) }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
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
                        <td colspan="7" class="text-center text-muted">No destinations found.</td>
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
                            <span class="text-danger" title="Multiple currencies detected, cannot calculate total">Mixed
                                currency</span>
                        @endif
                    </td>
                    <td>
                        @if ($canSumKenek)
                            {{ $kenekCurrencies->first() }} {{ number_format($totalKenekCost, 2, '.', ',') }}
                        @else
                            <span class="text-danger" title="Multiple currencies detected, cannot calculate total">Mixed
                                currency</span>
                        @endif
                    </td>
                    <td>
                        @if ($canSumBalikan)
                            {{ $balikanCurrencies->first() }} {{ number_format($totalBalikanCost, 2, '.', ',') }}
                        @else
                            <span class="text-danger" title="Multiple currencies detected, cannot calculate total">Mixed
                                currency</span>
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

</div>
