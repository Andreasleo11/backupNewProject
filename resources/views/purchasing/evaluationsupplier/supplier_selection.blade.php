@extends('layouts.app')

@section('content')

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <h1>Select Supplier and Year</h1>

  <form action="{{ route('purchasing.evaluationsupplier.calculate') }}" method="POST"
    class="form-container">
    @csrf

    <!-- Supplier Dropdown -->
    <label for="supplier">Select Supplier:</label>
    <select name="supplier" id="supplier" class="form-select">
      <option value="">-- Select Supplier --</option>
      @foreach ($supplierData as $supplier => $years)
        <option value="{{ $supplier }}">{{ $supplier }}</option>
      @endforeach
    </select>

    <!-- Year Dropdown (will be dynamically populated) -->
    <label for="year">Select Year:</label>
    <select name="year" id="year" class="form-select">
      <option value="">-- Select Year --</option>
    </select>

    <button type="submit" class="btn submit-btn">Submit</button>
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
          <th>Year</th>
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
            <td>{{ $head->year }}</td>
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
      var yearDropdown = $('#year'); // Year dropdown

      // Clear previous options
      yearDropdown.empty();
      yearDropdown.append('<option value="">-- Select Year --</option>');

      // If a supplier is selected, populate the years
      if (supplier && supplierYears[supplier]) {
        var years = supplierYears[supplier];

        // Populate year dropdown with available years
        years.forEach(function(year) {
          yearDropdown.append('<option value="' + year + '">' + year + '</option>');
        });
      }
    });
  </script>

@endsection

<style>
  /* Styling for the form and buttons */
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
  }

  .submit-btn:hover {
    background-color: #218838;
  }

  .link-buttons {
    margin: 20px 0;
    text-align: center;
    /* Center align the buttons */
  }

  .link-buttons a {
    display: inline-block;
    /* Ensure buttons are inline */
    padding: 12px 20px;
    /* Increase padding for better visibility */
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
    /* Slightly enlarge on hover */
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
</style>
