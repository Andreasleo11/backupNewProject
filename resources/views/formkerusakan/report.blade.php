@extends('layouts.app')

@section('content')
  <div class="container">
    <!-- Header Section -->
    <div class="text-center mb-4">
      <h2>PT DAIJO INDUSTRIAL</h2>
      <h4>LAPORAN KERUSAKAN DAN PERMINTAAN PERBAIKAN BARANG MILIK PELANGGAN / PENYEDIA EKSTERNAL</h4>
    </div>

    <!-- Customer and Release Date Information -->
    <div class="mb-4">
      <p><strong>Customer:</strong> {{ $customer }}</p>
      <p><strong>Release Date:</strong> {{ $release_date }}</p>
      <p><strong>Doc Num :</strong> {{ $doc_num }}</p>
    </div>

    <!-- Report Table -->
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>No</th>
          <th>Nama Barang</th>
          <th>Proses</th>
          <th>Masalah</th>
          <th>Sebab</th>
          <th>Penanggulangan</th>
          <th>PIC</th>
          <th>Target</th>
          <th>Keterangan</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($reports as $index => $report)
          <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $report->nama_barang }}</td>
            <td>{{ $report->proses }}</td>
            <td>{{ $report->masalah }}</td>
            <td>{{ $report->sebab }}</td>
            <td>{{ $report->penanggulangan }}</td>
            <td>{{ $report->pic }}</td>
            <td>{{ $report->target }}</td>
            <td>{{ $report->keterangan }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
@endsection
