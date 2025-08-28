@extends('layouts.app')

@section('content')
  <div class="container mt-5">
    <div class="card shadow-sm">
      <div class="card-header bg-info text-white">
        <h3>Purchase Order Details</h3>
      </div>
      <div class="card-body">
        <p><strong>ID:</strong> {{ $waitingPurchaseOrder->id }}</p>
        <p><strong>Mold Name:</strong> {{ $waitingPurchaseOrder->mold_name }}</p>
        <p><strong>Process:</strong> {{ $waitingPurchaseOrder->process }}</p>
        <p><strong>Price:</strong> ${{ number_format($waitingPurchaseOrder->price, 2) }}</p>
        <p><strong>Quotation Number:</strong> {{ $waitingPurchaseOrder->quotation_number }}</p>
        <p><strong>Remark:</strong> {{ $waitingPurchaseOrder->remark }}</p>
        <p><strong>Status:</strong> {{ $waitingPurchaseOrder->status }}</p>
        <p><strong>Capture Photo:</strong></p>
        <img src="{{ asset($waitingPurchaseOrder->capture_photo_path) }}" alt="Capture Photo"
          class="img-thumbnail mt-2" width="300">
        <a href="{{ route('waiting_purchase_orders.index') }}" class="btn btn-secondary mt-3">Back</a>
      </div>
    </div>
  </div>
@endsection
