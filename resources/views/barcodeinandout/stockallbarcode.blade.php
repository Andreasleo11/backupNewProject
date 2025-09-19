@extends('layouts.app')

@section('content')
  <!DOCTYPE html>
  <html>

  <head>
    <title>Stock Balances for {{ ucfirst($location) }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
  </head>

  <body>
    <div class="container">
      <h1>Stock Balances for {{ ucfirst($location) }}</h1>

      <!-- Links to switch between Jakarta and Karawang -->
      <div class="btn-group" role="group" aria-label="Location Switcher">
        <a href="{{ route('stockallbarcode', ['location' => 'Jakarta']) }}"
          class="btn btn-primary @if ($location == 'Jakarta') active @endif">Jakarta</a>
        <a href="{{ route('stockallbarcode', ['location' => 'Karawang']) }}"
          class="btn btn-primary @if ($location == 'Karawang') active @endif">Karawang</a>
      </div>

      <table class="table table-bordered mt-4">
        <thead>
          <tr>
            <th>Part No</th>
            <th>Description</th>
            <th>Balance</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($balances as $balance)
            <tr>
              <td>{{ $balance['partNo'] }}</td>
              <td>{{ $balance['description'] }}</td>
              <td>{{ $balance['balance'] }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <script src="{{ asset('js/app.js') }}"></script>
  </body>

  </html>
@endsection
