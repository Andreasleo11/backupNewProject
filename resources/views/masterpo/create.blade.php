@extends('layouts.app')

@section('content')
    @include('partials.alert-success-error')

    <div class="container">
        <h1>Create Purchase Orders</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('po.index') }}">Purchase Orders</a></li>
                <li class="breadcrumb-item active">Create</li>
            </ol>
        </nav>

        <div class="card shadow-sm p-4">
            <form action="{{ route('po.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- PO Number Input -->
                <div class="form-group mb-3">
                    <label for="po_number" class="form-label">PO Number</label>
                    <input type="number" name="po_number" id="po_number" class="form-control" required>
                </div>

                <!-- Vendor Name Input -->
                <div class="form-group mb-3">
                    <label for="vendor_name" class="form-label">Vendor Name</label>
                    <input type="text" name="vendor_name" id="vendor_name" class="form-control" required>
                </div>

                <!-- PO Date Input -->
                <div class="form-group mb-3">
                    <label for="po_date" class="form-label">PO Date</label>
                    <input type="date" name="po_date" id="po_date" class="form-control" required>
                </div>

                <!-- Total Input -->
                <div class="form-group mb-3">
                    <label for="total" class="form-label">Total</label>
                    <div class="input-group">
                        <!-- Currency Select (smaller) -->
                        <div class="col-auto">
                            <select name="currency" id="currency" class="form-select" required>
                                <option value="IDR">Rp</option>
                                <option value="YUAN">¥</option>
                                <option value="USD">$</option>
                            </select>
                        </div>

                        <!-- Main Input -->
                        <div class="col ms-1">
                            <input type="text" name="total" id="total" class="form-control" required>
                        </div>
                    </div>
                </div>

                <!-- PDF File Input -->
                <div class="form-group mb-3">
                    <label for="pdf_file" class="form-label">Choose PDF File</label>
                    <input type="file" name="pdf_file" id="pdf_file" accept="application/pdf" class="form-control"
                        required>
                </div>

                <button type="submit" class="btn btn-primary mt-3">Create</button>
            </form>
        </div>
    </div>


    <script>
        document.getElementById('total').addEventListener('input', function(e) {
            // Get the raw input value and remove all commas
            let value = e.target.value.replace(/,/g, '');

            // Allow only one dot for decimals
            const parts = value.split('.');
            if (parts.length > 2) {
                // If there's more than one dot, ignore additional dots
                parts.splice(2);
            }

            // Format the integer part with commas as thousand separators
            parts[0] = parts[0].replace(/\D/g, ''); // Remove any non-numeric characters from the integer part
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');

            // Rejoin the integer and decimal parts with a single dot
            e.target.value = parts.join('.');
        });
    </script>
@endsection
