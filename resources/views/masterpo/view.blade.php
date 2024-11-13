<!-- resources/views/viewpo.blade.php -->
@extends('layouts.app')

@section('content')
    {{-- @section('content')
    <div class="container my-5">
        <h1 class="text-3xl font-bold mb-4">View and Sign PO File</h1>

        <div class="card shadow-sm p-4 mb-4">
            <!-- PDF Display -->
            <iframe src="{{ asset('storage/pdfs/' . $filename) }}" width="100%" height="600px"></iframe>
        </div>

        <!-- Signature Canvas -->
        <div class="mt-4">
            <p>Draw your signature below:</p>
            <canvas id="signatureCanvas" width="500" height="200" style="border: 1px solid #ccc;"></canvas>
            <button id="clearCanvas" class="btn btn-danger mt-2">Clear Signature</button>
        </div>

        <!-- Save Signature Button -->
        <button id="saveSignature" class="btn btn-primary mt-4">Save Signature to PDF</button>
    </div>

    <script>
        // Set up the canvas
        const canvas = document.getElementById('signatureCanvas');
        const ctx = canvas.getContext('2d');
        let isDrawing = false;

        // Set up event listeners for drawing
        canvas.addEventListener('mousedown', (event) => {
            isDrawing = true;
            ctx.beginPath();
            ctx.moveTo(event.offsetX, event.offsetY);
        });

        canvas.addEventListener('mousemove', (event) => {
            if (isDrawing) {
                ctx.lineTo(event.offsetX, event.offsetY);
                ctx.stroke();
            }
        });

        canvas.addEventListener('mouseup', () => {
            isDrawing = false;
        });

        canvas.addEventListener('mouseleave', () => {
            isDrawing = false;
        });

        // Clear the canvas
        document.getElementById('clearCanvas').addEventListener('click', () => {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
        });

        // Save Signature to PDF
        document.getElementById('saveSignature').addEventListener('click', function () {
            const dataUrl = canvas.toDataURL('image/png');

            // Send the signature data and PDF filename to the server
            fetch('{{ route("pdf.sign") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    signature: dataUrl,
                    filename: '{{ $filename }}'
                })
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                window.location.reload(); // Reload the page to show the signed PDF
            })
            .catch(error => console.error('Error:', error));
        });
    </script>
    @endsection --}}

    <div class="container">
        <h1 class="">Detail Purchase Order</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('po.index') }}">Purchase Orders</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $purchaseOrder->po_number }}</li>
            </ol>
        </nav>
    </div>
    <div class="container my-5 border rounded-3 p-3">
        <div class="text-center mt-2 mb-5">
            <div class="mb-3">
                <h1 class="fs-2">Purchase Order </h1>
                <div class="mb-1">PO Number: <span class="text-secondary">{{ $purchaseOrder->po_number }}</span></div>
                <div class="mb-1">
                    @if ($purchaseOrder->approved_date)
                        Approved date: <span
                            class="text-secondary">{{ $purchaseOrder->approved_date? \Carbon\Carbon::parse($purchaseOrder->approved_date)->setTimezone('Asia/Jakarta')->format('d-m-Y (h:m)'): '-' }}</span>
                    @elseif($purchaseOrder->reason)
                        Reject Reason : <span class="text-secondary">{{ $purchaseOrder->reason }}</span>
                    @endif
                </div>
                @include('partials.po-status', ['po' => $purchaseOrder])
            </div>
        </div>
        <div class="card shadow-sm p-4 mb-4">
            <!-- PDF Display -->
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
        @if ($user->id == $purchaseOrder->creator_id||$user->specification->name === 'PURCHASER' || $user->is_head === 1)
            <button class="btn btn-outline-primary" data-bs-target="#upload-files-modal" data-bs-toggle="modal">
                <i class='bx bx-upload'></i> Upload
            </button>

            @include('partials.upload-files-modal', ['doc_id' => $purchaseOrder->po_number])
        @endif
    </div>


    <section aria-label="uploaded">
        @include('partials.uploaded-section', [
            'showDeleteButton' =>
                ($user->id === $purchaseOrder->creator_id) ||
                ($user->specification->name === 'PURCHASER'),
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
