@extends('layouts.app')

@section('content')
    @include('partials.alert-success-error')

    <div class="container">
        <h1>Edit Purchase Order</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('po.index') }}">Purchase Orders</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>

        <div class="card shadow-sm p-4">
            <form action="{{ route('po.update', $po->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- PO Number Input -->
                <div class="form-group mb-3">
                    <label for="po_number" class="form-label">PO Number</label>
                    <input type="number" name="po_number" id="po_number" class="form-control"
                        value="{{ old('po_number', $po->po_number) }}" required>
                </div>

                <!-- Vendor Name Input -->
                <div class="form-group mb-3">
                    <label for="vendor_name" class="form-label">Vendor Name</label>
                    <input type="text" name="vendor_name" id="vendor_name" class="form-control"
                        value="{{ old('vendor_name', $po->vendor_name) }}" required>
                </div>

                <!-- PO Date Input -->
                <div class="form-group mb-3">
                    <label for="po_date" class="form-label">PO Date</label>
                    <input type="text" name="po_date" id="po_date" class="form-control"
                        value="{{ old('po_date', $po->po_date) }}" required aria-describedby="poDateHelp">
                    <div id="poDateHelp" class="form-text">PO Date must use dd.mm.yy format.</div>
                </div>

                <!-- Tanggal Pembayaran Input -->
                <div class="form-group mb-3">
                    <label for="tanggal_pembayaran" class="form-label">Tanggal Pembayaran</label>
                    <input type="date" name="tanggal_pembayaran" id="tanggal_pembayaran" class="form-control"
                        value="{{ old('tanggal_pembayaran', $po->tanggal_pembayaran) }}" required>
                </div>

                <!-- Total Input -->
                <div class="form-group mb-3">
                    <label for="total" class="form-label">Total</label>
                    <div class="input-group">
                        <div class="col-auto">
                            <select name="currency" id="currency" class="form-select" required>
                                <option value="IDR" {{ old('currency', $po->currency) == 'IDR' ? 'selected' : '' }}>Rp
                                </option>
                                <option value="YUAN" {{ old('currency', $po->currency) == 'YUAN' ? 'selected' : '' }}>¥
                                </option>
                                <option value="USD" {{ old('currency', $po->currency) == 'USD' ? 'selected' : '' }}>$
                                </option>
                            </select>
                        </div>
                        <div class="col ms-1">
                            <input type="text" name="total" id="total" class="form-control"
                                value="{{ old('total', $po->total) }}" required>
                        </div>
                    </div>
                </div>

                <!-- PDF File Input -->
                <div class="form-group mb-3">
                    <label for="pdf_file" class="form-label">Choose PDF File</label>
                    <input type="file" name="pdf_file" id="pdf_file" accept="application/pdf" class="form-control"
                        aria-describedby="pdfFileHelp">
                    <div id="pdfFileHelp" class="form-text">Maximum file size is 2 MB. Leave blank if no changes.</div>
                </div>

                <button type="submit" class="btn btn-primary mt-3">Update</button>
            </form>
        </div>
    </div>

    <script>
        // Function to format the total input value
        function formatTotalInput(value) {
            // Remove all commas
            value = value.replace(/,/g, '');
            // Split into integer and decimal parts
            const parts = value.split('.');
            if (parts.length > 2) {
                parts.splice(2);
            }
            // Format integer part with commas as thousand separators
            parts[0] = parts[0].replace(/\D/g, ''); // Remove non-numeric characters
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            return parts.join('.');
        }

        // Format the total input on page load
        window.addEventListener('load', function() {
            const totalInput = document.getElementById('total');
            if (totalInput.value) {
                totalInput.value = formatTotalInput(totalInput.value);
            }
        });

        // Add event listener to format total input dynamically
        document.getElementById('total').addEventListener('input', function(e) {
            e.target.value = formatTotalInput(e.target.value);
        });
    </script>
@endsection
