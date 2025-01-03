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
                @if ($parentPONumber)
                    <div class="form-group mb-3">
                        <label for="parent_po_number" class="form-label">Parent PO Number</label>
                        <input class="form-control bg-secondary-subtle" type="text" id="parent_po_number"
                            name="parent_po_number" value="{{ old('parent_po_number', $parentPONumber) }}" readonly>
                    </div>
                @endif
                <!-- PO Number Input -->
                <div class="form-group mb-3">
                    <label for="po_number" class="form-label">PO Number</label>
                    <input type="number" name="po_number" id="po_number" class="form-control"
                        value="{{ old('po_number') }}" placeholder="2556622" required>
                </div>

                <!-- Vendor Name Input -->
                <div class="form-group mb-3">
                    <label for="vendor_name" class="form-label">Vendor Name</label>
                    <input type="text" name="vendor_name" id="vendor_name" class="form-control"
                        value="{{ old('vendor_name') }}" placeholder="PT. MAJU TERUS" required>
                </div>

                <!-- Invoice Date Input -->
                <div class="form-group mb-3">
                    <label for="invoice_date" class="form-label">Invoice Date</label>
                    <input type="text" name="invoice_date" id="invoice_date" class="form-control"
                        value="{{ old('invoice_date') }}" placeholder="18.11.24" required aria-describedby="poDateHelp">
                    <div id="poDateHelp" class="form-text">Invoice Date must use dd.mm.yy format.</div>
                </div>

                <!-- Invoice Number Input -->
                <div class="form-group mb-3">
                    <label for="invoice_number" class="form-label">Invoice Number</label>
                    <input type="text" name="invoice_number" id="invoice_number" class="form-control"
                        value="{{ old('invoice_number') }}" placeholder="98/MT/223/03" required>
                </div>

                <!-- Tanggal Pembayaran Input -->
                <div class="form-group mb-3">
                    <label for="tanggal_pembayaran" class="form-label">Tanggal Pembayaran</label>
                    <input type="date" name="tanggal_pembayaran" id="tanggal_pembayaran" class="form-control"
                        value="{{ old('tanggal_pembayaran') }}" required>
                </div>

                <!-- Purchase Order Category Input -->
                <div class="form-group mb-3">
                    <label for="purchase_order_category_id" class="form-label">Category</label>
                    <select name="purchase_order_category_id" id="purchase_order_category_id" class="form-select">
                        <option value="" {{ old('purchase_order_category_id') == '' ? 'selected' : '' }}>--Select
                            Category--</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}"
                                {{ old('purchase_order_category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Total Input -->
                <div class="form-group mb-3">
                    <label for="total" class="form-label">Total</label>
                    <div class="input-group">
                        <div class="col-auto">
                            <select name="currency" id="currency" class="form-select" required>
                                <option value="IDR" {{ old('currency') == 'IDR' ? 'selected' : '' }}>Rp</option>
                                <option value="YUAN" {{ old('currency') == 'YUAN' ? 'selected' : '' }}>¥</option>
                                <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>$</option>
                            </select>
                        </div>
                        <div class="col ms-1">
                            <input type="text" name="total" id="total" class="form-control"
                                value="{{ old('total') }}" placeholder="1,498,000" required>
                        </div>
                    </div>
                </div>

                <!-- PDF File Input -->
                <div class="form-group mb-3">
                    <label for="pdf_file" class="form-label">Choose PDF File</label>
                    <input type="file" name="pdf_file" id="pdf_file" accept="application/pdf" class="form-control"
                        aria-describedby="pdfFileHelp">
                    <div id="pdfFileHelp" class="form-text">Maximum file size is 2 MB.</div>
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
