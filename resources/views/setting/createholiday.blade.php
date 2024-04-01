@extends('layouts.app')

@section('content')


<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Create New Holiday</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('holidays.store') }}">
                        @csrf

                        <div class="form-group">
                            <label for="date">Date of Holiday:</label>
                            <input type="date" id="date" name="date" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="holiday_name">Holiday Name:</label>
                            <input type="text" id="holiday_name" name="holiday_name" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="description">Description:</label>
                            <input type="text" id="description" name="description" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="halfday">Halfday:</label>
                            <select id="halfday" name="halfday" class="form-control" required>
                                <option value="0">No</option>
                                <option value="1">Yes</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">Create Holiday</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection