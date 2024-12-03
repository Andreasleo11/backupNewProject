<!-- resources/views/viewpo.blade.php -->
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
    </div>
    <div class="container my-5 border rounded-3 p-3">
        <div class="text-center mt-2 mb-5">
            <div class="mb-3">
                <h1 class="fs-2">Purchase Order </h1>
                <div>PO Number : <span class="text-secondary">{{ $purchaseOrder->po_number }}</span></div>
                <div>Uploaded at
                    <span class="text-secondary">{{ \Carbon\Carbon::parse($purchaseOrder->created_at)->format('d-m-Y') }} by
                        {{ $purchaseOrder->user->name }}</span>
                </div>
                <div>
                    @if ($purchaseOrder->approved_date)
                        Approved at <span
                            class="text-secondary">{{ $purchaseOrder->approved_date? \Carbon\Carbon::parse($purchaseOrder->approved_date)->setTimezone('Asia/Jakarta')->format('d-m-Y (h:m)'): '-' }}</span>
                    @elseif($purchaseOrder->reason)
                        Reject Reason : <span class="text-secondary">{{ $purchaseOrder->reason }}</span>
                    @endif
                </div>
                <div class="mt-2">
                    @include('partials.po-status', ['po' => $purchaseOrder])
                </div>
                <hr>
                <table class="table table-borderlesss mt-2">
                    <tbody>
                        <tr>
                            <th>Vendor Name</th>
                            <td>: {{ $purchaseOrder->vendor_name }}</td>
                            <th>Invoice Number</th>
                            <td>: {{ $purchaseOrder->invoice_number }}
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal Pembayaran</th>
                            <td>: {{ \Carbon\Carbon::parse($purchaseOrder->tanggal_pembayaran)->format('d-m-Y') }}</td>
                            <th>Total</th>
                            <td>: {{ $purchaseOrder->currency . ' ' . number_format($purchaseOrder->total, 2, '.', ',') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card shadow-sm p-4 mb-4">
            <iframe src="{{ asset('storage/pdfs/' . $purchaseOrder->filename) }}" width="100%" height="700px"></iframe>
        </div>
        <div>
            @php
                $director = auth()->user()->department->name === 'DIRECTOR';
            @endphp
            @if ($purchaseOrder->status === 1 && $director)
                <button id="saveSignature" class="btn btn-primary mt-4">Sign PDF</button>
                <button id="rejectPO" class="btn btn-danger mt-4">Reject PO</button>
            @endif
            <a href="{{ route('po.download', $purchaseOrder->id) }}" class="btn btn-secondary mt-4">Download PDF</a>
        </div>
    </div>

    @php
        $director = auth()->user()->department->name === 'DIRECTOR';
    @endphp

    @if (!$director)
        <div class="text-end container mb-5">
            @if ($user->id == $purchaseOrder->creator_id || $user->specification->name === 'PURCHASER' || $user->is_head === 1)
                <button class="btn btn-outline-primary" data-bs-target="#upload-files-modal" data-bs-toggle="modal">
                    <i class='bx bx-upload'></i> Upload
                </button>

                @include('partials.upload-files-modal', ['doc_id' => $purchaseOrder->po_number])
            @endif
        </div>

        <section aria-label="uploaded">
            @include('partials.uploaded-section', [
                'showDeleteButton' =>
                    $user->id === $purchaseOrder->creator_id || $user->specification->name === 'PURCHASER',
            ])
        </section>
    @endif

    <script>
        // Save Signature to PDF
        document.getElementById('saveSignature').addEventListener('click', function() {
            // Send the filename to the server to add the stored signature
            fetch('{{ route('po.sign') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        filename: '{{ $purchaseOrder->filename }}',
                        id: '{{ $purchaseOrder->id }}'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    window.location.reload(); // Reload the page to show the signed PDF
                })
                .catch(error => console.error('Error:', error));
        });

        // Reject PO with a reason
        document.getElementById('rejectPO').addEventListener('click', function() {
            // Prompt the user to enter a rejection reason
            let reason = prompt("Please enter the reason for rejection:");

            if (reason) {
                fetch('{{ route('po.reject') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            filename: '{{ $purchaseOrder->filename }}',
                            id: '{{ $purchaseOrder->id }}',
                            reason: reason
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                        window.location.reload(); // Reload the page to reflect the rejection
                    })
                    .catch(error => console.error('Error:', error));
            }
        });
    </script>
@endsection
