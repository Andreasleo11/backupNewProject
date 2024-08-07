@extends('layouts.app')

@section('content')

<div class="container">
    <h2>Select Master Inventory</h2>
    <form action="{{ route('submit.master.inventory') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="masterSelect">Select Master Inventory:</label>
            <select class="form-control" id="masterSelect" name="master_id" required>
                <option value="" disabled selected>Select a master inventory</option>
                @foreach($masters as $master)
                    <option value="{{ $master->id }}">
                        {{ $master->username }} - {{ $master->ip_address }}
                    </option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>

@endsection