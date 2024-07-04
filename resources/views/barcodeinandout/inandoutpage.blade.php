@extends('layouts.app')

@section('content')

<div class="container">
    <h1>Insert Warehouse Barcode Form</h1>
    <form action="{{ route('process.in.and.out') }}" method="POST">
        @csrf
        
        <div class="form-group">
            <label for="warehouseType">Warehouse Type</label>
            <select class="form-control" id="warehouseType" name="warehouseType" required>
                <option value="" disabled selected>Select Warehouse Type</option>
                <option value="in">In</option>
                <option value="out">Out</option>
            </select>
        </div>
        <div class="form-group">
            <label for="location">Location</label>
            <select class="form-control" id="location" name="location" required>
                <option value="" disabled selected>Select Location</option>
                <option value="jakarta">Jakarta</option>
                <option value="karawang">Karawang</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>

@endsection

<style>
    .container {
        margin-top: 20px;
    }
    .form-group {
        margin-bottom: 15px;
    }
    .form-control {
        width: 100%;
        padding: 10px;
        font-size: 16px;
    }
    .btn {
        padding: 10px 20px;
        font-size: 16px;
    }
</style>
