@extends('layouts.app')

@section('content')
  <div class="container mt-5">
    <div class="card shadow-sm">
      <div class="card-header bg-warning text-dark">
        <h3>Edit Purchase Order</h3>
      </div>
      <div class="card-body">
        <form action="{{ route('waiting_purchase_orders.update', $waitingPurchaseOrder->id) }}"
          method="POST" enctype="multipart/form-data">
          @csrf
          @method('PUT')
          <div class="mb-3">
            <label for="mold_name" class="form-label">Mold Name</label>
            <input type="text" name="mold_name" id="mold_name" class="form-control"
              value="{{ $waitingPurchaseOrder->mold_name }}" required>
          </div>
          <div class="mb-3">
            <label for="capture_photo_path" class="form-label">Capture Photo</label>
            <input type="file" name="capture_photo_path" id="capture_photo_path"
              class="form-control">
            <small class="text-muted">Current photo:</small>
            <img src="{{ asset($waitingPurchaseOrder->capture_photo_path) }}" alt="Capture Photo"
              class="img-thumbnail mt-2" width="200">
          </div>
          <div class="mb-3">
            <label for="process" class="form-label">Process</label>
            <input type="text" name="process" id="process" class="form-control"
              value="{{ $waitingPurchaseOrder->process }}" required>
          </div>
          <div class="mb-3">
            <label for="price" class="form-label">Price</label>
            <input type="number" name="price" id="price" class="form-control"
              value="{{ $waitingPurchaseOrder->price }}" required>
          </div>
          <div class="mb-3">
            <label for="quotation_number" class="form-label">Quotation Number</label>
            <input type="text" name="quotation_number" id="quotation_number" class="form-control"
              value="{{ $waitingPurchaseOrder->quotation_number }}" required>
          </div>
          <div class="mb-3">
            <label for="remark" class="form-label">Remark</label>
            <textarea name="remark" id="remark" class="form-control" rows="3">{{ $waitingPurchaseOrder->remark }}</textarea>
          </div>
          <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <input type="number" name="status" id="status" class="form-control"
              value="{{ $waitingPurchaseOrder->status }}" required>
          </div>
          <button type="submit" class="btn btn-primary">Update</button>
          <a href="{{ route('waiting_purchase_orders.index') }}" class="btn btn-secondary">Back</a>
        </form>
      </div>
    </div>
  </div>
@endsection
