@extends('layouts.app')

@section('content')
    <div class="container">
        <h3>Evaluation Format Request</h3>

        <form action="{{ route('get.format.magang') }}" method="POST">
            @csrf

            <!-- Status Dropdown -->
            <!-- <div class="form-group">
                    <label for="status">Select Status:</label>
                    <select id="status" name="status" class="form-control">
                        @foreach ($statuses as $status)
    <option value="{{ $status }}">{{ $status }}</option>
    @endforeach
                    </select>
                </div> -->

            <div class="form-group">
                <label for="dept">Select dept:</label>
                <select id="dept" name="dept" class="form-control">
                    @foreach ($departments as $department)
                        <option value="{{ $department->dept_no }}">{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Month Selection -->
            <!-- <div class="form-group">
                    <label for="month">Select Month:</label>
                    <select id="month" name="month" class="form-control">
                        @foreach (range(1, 12) as $m)
    <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}">
                                {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                            </option>
    @endforeach
                    </select>
                </div> -->

            <!-- Year Selection -->
            <div class="form-group">
                <label for="year">Select Year:</label>
                <select id="year" name="year" class="form-control">
                    @foreach (range(date('Y') - 5, date('Y')) as $y)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-primary mt-3">Submit</button>
        </form>
    </div>
@endsection
