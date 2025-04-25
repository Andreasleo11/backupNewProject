@extends('layouts.app')

@section('content')
    @include('partials.alert-success-error')

    <div class="container col-md-7">
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

                <div class="row">
                    <div class="col">
                        <!-- PO Number Input -->
                        <div class="form-group mb-3">
                            <label for="po_number" class="form-label">PO Number</label>
                            <input type="number" name="po_number" id="po_number" class="form-control"
                                value="{{ old('po_number', $po->po_number) }}" disabled>
                        </div>
                    </div>
                    <div class="col">
                        <!-- Vendor Name Input -->
                        <div class="form-group mb-3">
                            <label for="vendor_code" class="form-label">Vendor Code</label>
                            <input type="text" name="vendor_code" id="vendor_code" class="form-control"
                                value="{{ old('vendor_code', $po->vendor_code) }}" disabled>
                        </div>
                    </div>
                    <div class="col">
                        <!-- Vendor Name Input -->
                        <div class="form-group mb-3">
                            <label for="vendor_name" class="form-label">Vendor Name</label>
                            <input type="text" name="vendor_name" id="vendor_name" class="form-control"
                                value="{{ old('vendor_name', $po->vendor_name) }}" disabled>
                        </div>
                    </div>
                </div>

                <!-- Tanggal Pembayaran Input -->
                <div class="form-group mb-3">
                    <label for="tanggal_pembayaran" class="form-label">Tanggal Pembayaran</label>
                    <input type="date" name="tanggal_pembayaran" id="tanggal_pembayaran"
                        class="form-control @error('tanggal_pembayaran') is-invalid @enderror"
                        value="{{ old('tanggal_pembayaran', $po->tanggal_pembayaran) }}" required>
                    @error('tanggal_pembayaran')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Purchase Order Category Input -->
                <div class="form-group mb-3">
                    <label for="category" class="form-label">Category</label>
                    <select name="category" id="category" class="form-select @error('category') is-invalid @enderror">
                        <option value="" {{ old('category') == '' ? 'selected' : '' }}>--Select
                            Category--</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category }}"
                                {{ old('category', $po->category) == $category ? 'selected' : '' }}>
                                {{ $category }}
                            </option>
                        @endforeach
                    </select>
                    @error('category')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Total Input -->
                <div class="form-group mb-4">
                    <label for="total" class="form-label">Total</label>
                    <div class="input-group">
                        <div class="col-auto">
                            <select name="currency" id="currency" class="form-select @error('total') is-invalid @enderror"
                                required>
                                <option value="IDR" {{ old('currency', $po->currency) == 'IDR' ? 'selected' : '' }}>IDR
                                </option>
                                <option value="YUAN" {{ old('currency', $po->currency) == 'YUAN' ? 'selected' : '' }}>
                                    YUAN
                                </option>
                                <option value="USD" {{ old('currency', $po->currency) == 'USD' ? 'selected' : '' }}>USD
                                </option>
                            </select>
                        </div>
                        <div class="col ms-1">
                            <input type="text" name="total" id="total" class="form-control"
                                value="{{ old('total', $po->total) }}" required>
                        </div>
                    </div>
                    @error('total')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group mb-3">
                    <label for="is_need_sign" class="form-label">Need to be sign?</label>
                    <div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="is_need_sign" id="yes_option"
                                value="1" {{ old('is_need_sign', $po->is_need_sign) == 1 ? 'checked' : '' }}>
                            <label class="form-check-label" for="yes_option">Yes</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="is_need_sign" id="no_option" value="0"
                                {{ old('is_need_sign', $po->is_need_sign) == 0 ? 'checked' : '' }}>
                            <label class="form-check-label" for="no_option">No</label>
                        </div>
                    </div>
                    @error('is_need_sign')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
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
