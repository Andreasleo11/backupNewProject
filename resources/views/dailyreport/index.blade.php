@extends('layouts.app') {{-- Ganti sesuai layoutmu --}}

@section('content')
<div class="p-4">
    <h1 class="text-2xl font-bold mb-4">Employee Daily Reports</h1>

    <table class="table-auto w-full border-collapse border border-gray-300">
        <thead>
            <tr class="bg-gray-100">
                <th class="border px-4 py-2">#</th>
                <th class="border px-4 py-2">Timestamp</th>
                <th class="border px-4 py-2">Nama</th>
                <th class="border px-4 py-2">Tanggal Kerja</th>
                <th class="border px-4 py-2">Jam Kerja</th>
                <th class="border px-4 py-2">Deskripsi</th>
                <th class="border px-4 py-2">Bukti</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($reports as $index => $report)
                <tr>
                    <td class="border px-4 py-2">{{ $index + 1 }}</td>
                    <td class="border px-4 py-2">{{ $report->submitted_at }}</td>
                    <td class="border px-4 py-2">{{ $report->employee_name }}</td>
                    <td class="border px-4 py-2">{{ \Carbon\Carbon::parse($report->work_date)->format('d/m/Y') }}</td>
                    <td class="px-4 py-2 border">
                                    {{ $report->work_time }} 
                            </td>
                    <td class="border px-4 py-2">{{ $report->work_description }}</td>
                    <td class="border px-4 py-2">
                        @if($report->proof_url)
                            <a href="{{ $report->proof_url }}" target="_blank" class="text-blue-600 underline">Lihat Bukti</a>
                        @else
                            Tidak ada
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
