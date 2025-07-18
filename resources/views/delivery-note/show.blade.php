@extends('layouts.app')

@section('content')
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
                        <td>{{ $deliveryNote->ritasi }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Delivery Note Date</th>
                        <td>{{ \Carbon\Carbon::parse($deliveryNote->delivery_note_date)->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Departure Time</th>
                        <td>{{ \Carbon\Carbon::parse($deliveryNote->departure_time)->format('H:i') }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Return Time</th>
                        <td>{{ \Carbon\Carbon::parse($deliveryNote->return_time)->format('H:i') }}</td>
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

        {{-- Destination List --}}
        <h5 class="fw-semibold">📦 Destinations</h5>
        <table class="table table-bordered table-hover mt-3">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Destination</th>
                    <th>Delivery Order Number</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($deliveryNote->destinations as $i => $d)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $d->destination }}</td>
                        <td>{{ $d->delivery_order_number }}</td>
                        <td>{{ $d->remarks }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted">No destinations found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    </div>
@endsection
