@extends('layouts.app')

@section('content')
<title>Input Form</title>
<link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>
<style>
    .form-group {
        margin-bottom: 20px;
    }
    .btn-primary {
        margin-top: 20px;
        float: right;
        font-size: 18px;
        padding: 10px 20px;
    }
    .tooltip {
        position: relative;
        display: inline-block;
    }
    .tooltip .tooltiptext {
        visibility: hidden;
        width: 220px;
        background-color: black;
        color: #fff;
        text-align: center;
        border-radius: 6px;
        padding: 5px;
        position: absolute;
        z-index: 1;
        bottom: 100%;
        left: 50%;
        margin-left: -110px;
        opacity: 0;
        transition: opacity 0.3s;
    }
    .tooltip:hover .tooltiptext {
        visibility: visible;
        opacity: 1;
    }
</style>

<div class="container">
    <h1>Input Missing Barcode Form</h1>
    <form action="{{ route('generateBarcodeMissing') }}" method="POST">
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
            <label for="missingnumber" class="tooltip">Input Missing Label Numbers:
                <span class="tooltiptext">Enter the missing label numbers separated by commas (e.g., 4, 7, 90, 10A)</span>
            </label>
            <input type="text" id="missingnumber" name="missingnumber" required>
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

        new TomSelect('#missingnumber', {
            persist: false,
            create: function(input) {
                // Allow alphanumeric inputs
                if (/^[a-zA-Z0-9]+$/.test(input)) {
                    return {
                        value: input,
                        text: input
                    };
                }
                return false;
            },
            delimiter: ',',
            maxItems: null,
            plugins: ['remove_button']
        });
    });
</script>

@endsection
