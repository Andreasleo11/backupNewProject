@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <div class="card shadow-sm">
            <div class="card-header">
                <h3>Create New Purchase Order</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('waiting_purchase_orders.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="mold_name" class="form-label">Mold Name</label>
                        <input type="text" name="mold_name" id="mold_name" class="form-control"
                            value="{{ old('mold_name') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="capture_photo_path" class="form-label">Capture Photo</label>
                        <input type="file" name="capture_photo_path" id="capture_photo_path" class="form-control"
                            required>
                    </div>
                    <div class="mb-3">
                        <label for="process" class="form-label">Process</label>
                        <input type="text" name="process" id="process" class="form-control"
                            value="{{ old('process') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="price" class="form-label">Price</label>
                        <input type="number" name="price" id="price" class="form-control" value="{{ old('price') }}"
                            required>
                    </div>
                    <div class="mb-3">
                        <label for="quotation_number" class="form-label">Quotation Number</label>
                        <input type="text" name="quotation_number" id="quotation_number" class="form-control"
                            value="{{ old('quotation_number') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="remark" class="form-label">Remark</label>
                        <textarea name="remark" id="remark" class="form-control" rows="3">{{ old('remark') }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <input type="number" name="status" id="status" class="form-control" value="{{ old('status') }}"               
                            required>
                    </div>
                    <button type="submit" class="btn btn-success">Create</button>
                    <a href="{{ route('waiting_purchase_orders.index') }}" class="btn btn-secondary">Back</a>
                </form>
            </div>
        </div>
    </div>
@endsection
