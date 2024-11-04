<!-- resources/views/viewpo.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container my-5">
    <h1 class="text-3xl font-bold mb-4">View and Sign PO File</h1>

    <div class="card shadow-sm p-4 mb-4">
        <!-- PDF Display -->
        <iframe src="{{ asset('storage/pdfs/' . $filename) }}" width="100%" height="600px"></iframe>
    </div>

    <!-- Save Signature Button -->
    <button id="saveSignature" class="btn btn-primary mt-4">Sign PDF</button>
    <button id="rejectPO" class="btn btn-danger mt-4">Reject PO</button>
    <a href="{{ route('pdf.download', ['filename' => $filename]) }}" class="btn btn-secondary mt-4">Download PDF</a>
</div>

<script>
    // Save Signature to PDF
    document.getElementById('saveSignature').addEventListener('click', function () {
        // Send the filename to the server to add the stored signature
        fetch('{{ route("pdf.sign") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                filename: '{{ $filename }}',
                id: '{{ $id }}'
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
     document.getElementById('rejectPO').addEventListener('click', function () {
        // Prompt the user to enter a rejection reason
        let reason = prompt("Please enter the reason for rejection:");

        if (reason) {
            fetch('{{ route("pdf.reject") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    filename: '{{ $filename }}',
                    id: '{{ $id }}',
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
