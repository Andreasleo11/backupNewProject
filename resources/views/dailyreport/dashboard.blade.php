<!-- resources/views/daily_reports/dashboard.blade.php -->
<!DOCTYPE html>
<html>

<head>
    <title>Daily Reports</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Daily Reports</h1>
        <form action="{{ route('employee.logout') }}" method="POST">
            @csrf
            <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Logout</button>
        </form>
    </div>

    {{-- Filter Tanggal --}}
    <div class="max-w-7xl mx-auto mb-4">
        <form method="GET" action="{{ route('daily-reports.index') }}" class="flex items-center gap-4">
            <label for="filter_date" class="text-sm font-medium text-gray-700">Filter Tanggal:</label>
            <input type="date" id="filter_date" name="filter_date" value="{{ request('filter_date') }}"
                class="border border-gray-300 rounded px-3 py-2">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Filter
            </button>
            @if (request('filter_date'))
                <a href="{{ route('daily-reports.index') }}" class="text-sm text-red-600 hover:underline">Reset</a>
            @endif
        </form>
    </div>

    <div class="max-w-7xl mx-auto bg-white shadow-md rounded p-6">
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200">
                <thead>
                    <tr class="bg-gray-100 text-left text-sm font-medium text-gray-700">
                        <th class="px-4 py-2 border">#</th>
                        <th class="px-4 py-2 border">Timestamp</th>
                        <th class="px-4 py-2 border">Nama</th>
                        <th class="px-4 py-2 border">Tanggal Kerja</th>
                        <th class="px-4 py-2 border">Jam Kerja</th>
                        <th class="px-4 py-2 border">Deskripsi</th>
                        <th class="px-4 py-2 border">Bukti</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-gray-800">
                    @forelse ($reports as $index => $report)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 border">{{ $index + 1 }}</td>
                            <td class="px-4 py-2 border">{{ $report->submitted_at }}</td>
                            <td class="px-4 py-2 border">{{ $report->employee_name }}</td>
                            <td class="px-4 py-2 border">
                                {{ \Carbon\Carbon::parse($report->work_date)->format('d/m/Y') }}</td>
                            <td class="px-4 py-2 border">{{ $report->work_time }}</td>
                            <td class="px-4 py-2 border">{{ $report->work_description }}</td>
                            <td class="px-4 py-2 border">
                                @if ($report->proof_url)
                                    <a href="{{ $report->proof_url }}" target="_blank"
                                        class="text-blue-600 hover:underline">Lihat Bukti</a>
                                @else
                                    <span class="text-gray-500">Tidak ada</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-2 text-center text-gray-500">Tidak ada data laporan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>
