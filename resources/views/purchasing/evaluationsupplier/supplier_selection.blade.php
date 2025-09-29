@extends('layouts.app')

@section('content')

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <h1>Select Supplier and Period</h1>

    <form action="{{ route('purchasing.evaluationsupplier.calculate') }}" method="POST" class="form-container">
        @csrf

        <!-- Supplier Dropdown -->
        <label for="supplier">Select Supplier:</label>
        <select name="supplier" id="supplier" class="form-select">
            <option value="">-- Select Supplier --</option>
            @foreach ($supplierData as $supplier => $years)
                <option value="{{ $supplier }}">{{ $supplier }}</option>
            @endforeach
        </select>

        <!-- Date Range Section -->
        <div class="date-range-section">
            <h3>Evaluation Period</h3>

            <!-- Start Date Row -->
            <div class="date-row">
                <div class="date-group">
                    <label for="start_month">Start Month:</label>
                    <select name="start_month" id="start_month" class="form-select">
                        <option value="">-- Select Month --</option>
                        <option value="January">January</option>
                        <option value="February">February</option>
                        <option value="March">March</option>
                        <option value="April">April</option>
                        <option value="May">May</option>
                        <option value="June">June</option>
                        <option value="July">July</option>
                        <option value="August">August</option>
                        <option value="September">September</option>
                        <option value="October">October</option>
                        <option value="November">November</option>
                        <option value="December">December</option>
                    </select>
                </div>

                <div class="date-group">
                    <label for="start_year">Start Year:</label>
                    <select name="start_year" id="start_year" class="form-select">
                        <option value="">-- Select Year --</option>
                    </select>
                </div>
            </div>

            <!-- End Date Row -->
            <div class="date-row">
                <div class="date-group">
                    <label for="end_month">End Month:</label>
                    <select name="end_month" id="end_month" class="form-select">
                        <option value="">-- Select Month --</option>
                        <option value="January">January</option>
                        <option value="February">February</option>
                        <option value="March">March</option>
                        <option value="April">April</option>
                        <option value="May">May</option>
                        <option value="June">June</option>
                        <option value="July">July</option>
                        <option value="August">August</option>
                        <option value="September">September</option>
                        <option value="October">October</option>
                        <option value="November">November</option>
                        <option value="December">December</option>
                    </select>
                </div>

                <div class="date-group">
                    <label for="end_year">End Year:</label>
                    <select name="end_year" id="end_year" class="form-select">
                        <option value="">-- Select Year --</option>
                    </select>
                </div>
            </div>
        </div>

        <button type="submit" class="btn submit-btn">Calculate Evaluation</button>
    </form>

    <div class="link-buttons">
        <a href="{{ route('kriteria1') }}" class="btn">Kualitas Barang dan Kemasan</a>
        <a href="{{ route('kriteria2') }}" class="btn">Ketepatan Kuantitas Barang </a>
        <a href="{{ route('kriteria3') }}" class="btn">Ketepatan Waktu Pengiriman</a>
        <a href="{{ route('kriteria4') }}" class="btn">Kerjasama dalam permintaan mendadak</a>
        <a href="{{ route('kriteria5') }}" class="btn">Respon klaim</a>
        <a href="{{ route('kriteria6') }}" class="btn">Sertifikasi</a>
    </div>

    <h1>Supplier Evaluations</h1>

    @if ($header->isEmpty())
        <p>No headers found.</p>
    @else
        <h2>Headers List</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Vendor Code</th>
                    <th>Vendor Name</th>
                    <th>Period</th>
                    <th>Grade</th>
                    <th>Status</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($header as $head)
                    <tr>
                        <td>{{ $head->id }}</td>
                        <td>{{ $head->vendor_code }}</td>
                        <td>{{ $head->vendor_name }}</td>
                        <td>{{ $head->period ?? $head->year }}</td>
                        <td>{{ $head->grade }}</td>
                        <td>{{ $head->status }}</td>
                        <td>
                            <a href="{{ route('purchasing.evaluationsupplier.details', ['id' => $head->id]) }}"
                                target="_blank" class="btn detail-link">View Details</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <script>
        // Store supplier data in a JavaScript object for easy lookup
        var supplierYears = @json($supplierData);

        // Handle supplier dropdown change
        $('#supplier').on('change', function() {
            var supplier = $(this).val(); // Get selected supplier
            var startYearDropdown = $('#start_year');
            var endYearDropdown = $('#end_year');

            // Clear previous options
            startYearDropdown.empty();
            endYearDropdown.empty();
            startYearDropdown.append('<option value="">-- Select Year --</option>');
            endYearDropdown.append('<option value="">-- Select Year --</option>');

            // If a supplier is selected, populate the years
            if (supplier && supplierYears[supplier]) {
                var years = supplierYears[supplier];

                // Populate both start and end year dropdowns with available years
                years.forEach(function(year) {
                    startYearDropdown.append('<option value="' + year + '">' + year + '</option>');
                    endYearDropdown.append('<option value="' + year + '">' + year + '</option>');
                });
            }
        });

        // Validate date range
        function validateDateRange() {
            var startMonth = parseInt($('#start_month').val());
            var startYear = parseInt($('#start_year').val());
            var endMonth = parseInt($('#end_month').val());
            var endYear = parseInt($('#end_year').val());

            if (startMonth && startYear && endMonth && endYear) {
                var startDate = new Date(startYear, startMonth - 1, 1);
                var endDate = new Date(endYear, endMonth - 1, 1);

                if (startDate > endDate) {
                    alert('Start date cannot be later than end date!');
                    return false;
                }
            }
            return true;
        }

        // Add validation on change
        $('#start_month, #start_year, #end_month, #end_year').on('change', validateDateRange);

        // Form submission validation
        $('form').on('submit', function(e) {
            if (!validateDateRange()) {
                e.preventDefault();
                return false;
            }
        });
    </script>

@endsection

<style>
    /* Original styling plus new date range styles */
    .form-container {
        margin: 20px 0;
        padding: 20px;
        border: 1px solid #ddd;
        border-radius: 8px;
        background-color: #f9f9f9;
    }

    .form-container label {
        display: block;
        margin-bottom: 8px;
        font-weight: bold;
    }

    .form-select {
        display: block;
        width: 100%;
        padding: 10px;
        margin-bottom: 15px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    /* New date range styles */
    .date-range-section {
        border: 1px solid #ddd;
        padding: 15px;
        margin: 20px 0;
        border-radius: 6px;
        background-color: #fff;
    }

    .date-range-section h3 {
        margin: 0 0 15px 0;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
        color: #333;
        font-size: 18px;
    }

    .date-row {
        display: flex;
        gap: 20px;
        margin-bottom: 15px;
        align-items: flex-end;
    }

    .date-group {
        flex: 1;
    }

    .date-group label {
        margin-bottom: 5px;
        font-size: 14px;
    }

    .btn {
        display: inline-block;
        padding: 10px 15px;
        margin: 5px;
        font-size: 16px;
        color: #fff;
        background-color: #007bff;
        text-align: center;
        text-decoration: none;
        border-radius: 4px;
        border: none;
        cursor: pointer;
        transition: background-color 0.3s, box-shadow 0.3s;
    }

    .btn:hover {
        background-color: #0056b3;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .submit-btn {
        background-color: #28a745;
        width: 100%;
        margin-top: 10px;
    }

    .submit-btn:hover {
        background-color: #218838;
    }

    .link-buttons {
        margin: 20px 0;
        text-align: center;
    }

    .link-buttons a {
        display: inline-block;
        padding: 12px 20px;
        font-size: 16px;
        color: #fff;
        background-color: #007bff;
        border-radius: 8px;
        border: none;
        text-decoration: none;
        margin: 5px;
        transition: background-color 0.3s, transform 0.3s;
    }

    .link-buttons a:hover {
        background-color: #0056b3;
        transform: scale(1.05);
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    .data-table th,
    .data-table td {
        padding: 12px;
        text-align: left;
        border: 1px solid #ddd;
    }

    .data-table th {
        background-color: #f4f4f4;
    }

    .detail-link {
        color: #007bff;
        text-decoration: none;
    }

    .detail-link:hover {
        text-decoration: underline;
    }

    h1,
    h2 {
        color: #333;
    }

    /* Responsive design for smaller screens */
    @media (max-width: 768px) {
        .date-row {
            flex-direction: column;
            gap: 10px;
        }

        .link-buttons a {
            display: block;
            margin: 5px 0;
        }
    }
</style>
