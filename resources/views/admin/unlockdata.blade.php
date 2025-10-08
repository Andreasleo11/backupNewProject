@extends('layouts.app')

@section('content')
    <h2>Unlock Data</h2>

    @if ($datas->isEmpty())
        <p>No data available.</p>
    @else
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>NIK</th>
                    <th>Nama</th>
                    <th>Month</th>
                    <th>Total</th>
                    <th>Is Lock</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($datas as $data)
                    <tr>
                        <td>{{ $data->NIK }}</td>
                        <td>{{ $data->karyawan->Nama }}</td>
                        <td>{{ \Carbon\Carbon::parse($data->Month)->format('F Y') }}</td>
                        <td>{{ $data->total }}</td>
                        <td>{{ $data->is_lock ? 'Locked' : 'Unlocked' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

@endsection
