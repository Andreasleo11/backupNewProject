@extends('layouts.app')

@section('content')
    <div class="container mx-auto mt-8">
        <h2 class="text-2xl font-semibold">Export Yayasan Data</h2>

        <!-- Form for selecting month and year -->
        <form action="{{ route('exportyayasan.summary') }}" method="GET" class="mt-6">
            @csrf
            <div class="flex space-x-4">
                <!-- Month Selection -->
                <div class="flex flex-col">
                    <label for="month" class="mb-2 font-medium">Month</label>
                    <select name="month" id="month" class="border border-gray-300 rounded p-2">
                        <option value="">Select Month</option>
                        @foreach (range(1, 12) as $month)
                            <option value="{{ $month }}" {{ old('month') == $month ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($month)->format('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Year Selection -->
                <div class="flex flex-col">
                    <label for="year" class="mb-2 font-medium">Year</label>
                    <select name="year" id="year" class="border border-gray-300 rounded p-2">
                        <option value="">Select Year</option>
                        @foreach (range(date('Y'), 2000) as $year)
                            <option value="{{ $year }}" {{ old('year') == $year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Submit Button -->
                <div class="flex flex-col justify-center">
                    <button type="submit"
                        class="btn btn-primary px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-700">
                        Export
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
