@extends('layouts.app')

@section('content')
    @include('partials.alert-success-error')
    <div class="container mt-5">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3>Waiting Purchase Orders</h3>
                <a href="{{ route('waiting_purchase_orders.create') }}" class="btn btn-success btn-sm">Create New Order</a>
            </div>
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <table class="table table-bordered table-striped">
                    <thead class="">
                        <tr>
                            <th>ID</th>
                            <th>Mold Name</th>
                            <th>Process</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                            <tr>
                                <td>{{ $order->id }}</td>
                                <td>{{ $order->mold_name }}</td>
                                <td>{{ $order->process }}</td>
                                <td>${{ number_format($order->price, 2) }}</td>
                                <td>
                                    <span
                                        class="badge
                        {{ $order->status == 1 ? 'bg-success' : 'bg-danger' }}">
                                        {{ $order->status == 1 ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="d-flex">
                                    <a href="{{ route('waiting_purchase_orders.show', $order->id) }}"
                                        class="btn btn-info btn-sm me-2">View</a>
                                    <a href="{{ route('waiting_purchase_orders.edit', $order->id) }}"
                                        class="btn btn-warning btn-sm me-2">Edit</a>
                                    <form action="{{ route('waiting_purchase_orders.destroy', $order->id) }}"
                                        method="POST" onsubmit="return confirm('Are you sure you want to delete this?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No purchase orders found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
