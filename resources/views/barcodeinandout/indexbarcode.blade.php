@extends('layouts.app')

@section('content')
<title>Input Form</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-beta.1/css/select2.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-beta.1/js/select2.min.js"></script>

<div class="container">
    <h1>Input Form</h1>
    <form action="{{ route('generateBarcode') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="partNo">Part No</label>
            <select class="form-control" id="partNo" name="partNo" required>
                <option value="" disabled selected>Select Part No</option>
                @foreach($datas as $data)
                    <option value="{{ $data->name }}">{{ $data->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="startNumber">Start Number Label</label>
            <input type="number" class="form-control" id="startNumber" name="startNumber" required>
        </div>
        <div class="form-group">
            <label for="quantity">End Number Label</label>
            <input type="number" class="form-control" id="quantity" name="quantity" required>
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>

<script>
    $(document).ready(function() {
        $('#partNo').select2({
            placeholder: 'Select Part No',
            allowClear: true
        });
    });
</script>

@endsection
