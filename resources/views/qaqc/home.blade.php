@extends('new.layouts.app')

@section('page-title', 'QAQC Dashboard')

@section('content')
    <div class="max-w-7xl mx-auto space-y-8">
        {{-- Stats Cards --}}
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
            <a href="{{ route('qaqc.report.index', ['status' => 'approved']) }}" class="block">
                <x-card title="Approved" :content="$approvedDoc" color="green" titleColor="text-green-600"
                    icon="<box-icon name='check' color='gray' size='lg'></box-icon>" />
            </a>

            <a href="{{ route('qaqc.report.index', ['status' => 'waitingSignature']) }}" class="block">
                <x-card title="Waiting Signature" :content="$waitingSignatureDoc" color="gray" titleColor="text-slate-600"
                    icon="<box-icon name='time' color='gray' size='lg'></box-icon>" />
            </a>

            <a href="{{ route('qaqc.report.index', ['status' => 'waitingApproval']) }}" class="block">
                <x-card title="Waiting Approval" :content="$waitingApprovalDoc" color="orange" titleColor="text-amber-600"
                    icon="<box-icon name='time' color='gray' size='lg'></box-icon>" />
            </a>

            <a href="{{ route('qaqc.report.index', ['status' => 'rejected']) }}" class="block">
                <x-card title="Rejected" :content="$rejectedDoc" color="red" titleColor="text-red-600"
                    contentColor="text-slate-600" icon="<box-icon name='x-circle' color='gray' size='lg'></box-icon>" />
            </a>
        </div>

        {{-- Monthly Chart Section --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
            <h3 class="text-lg font-semibold text-slate-900 mb-4">Monthly Chart</h3>
            <div class="mb-4">
                <select name="month" id="monthSelect"
                    class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    <option value="" disabled selected>--Select the month--</option>
                    @for ($month = 1; $month <= 12; $month++)
                        <option value="{{ $month }}">{{ date('F', mktime(0, 0, 0, $month, 1)) }}</option>
                    @endfor
                </select>
            </div>
            <div class="w-full">
                <canvas id="myChart" aria-label="myChart" role="img"></canvas>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endsection

@push('extraJs')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const ctx = document.getElementById('myChart');
            let labels = [];
            let datas = [];

            const data = {
                labels: labels,
                datasets: [{
                    label: 'My First Dataset',
                    data: datas,
                    fill: false,
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1,
                }]
            };

            const config = {
                type: 'line',
                data: data,
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            min: 0,
                            max: 100
                        }
                    }
                }
            };

            const myChart = new Chart(ctx, config);

            const monthSelect = document.getElementById('monthSelect');

            // Set the monthSelect value to the current month
            const currentMonth = new Date().getMonth() + 1;
            monthSelect.value = currentMonth;

            // Function to update the chart based on the selected month
            function updateChart() {
                const monthIndex = parseInt(monthSelect.value) - 1; // Month index starts from 0
                const year = new Date().getFullYear(); // Get the current year

                // Get the first and last day of the selected month
                const firstDayOfMonth = new Date(year, monthIndex, 1);
                const lastDayOfMonth = new Date(year, monthIndex + 1, 0);

                // Calculate the number of weeks
                const numWeeks = Math.ceil((lastDayOfMonth.getDate() - firstDayOfMonth.getDate() + 1) /
                    7);

                // Generate labels for each week starting from Sunday
                labels = [];
                let currentDate = new Date(firstDayOfMonth);
                for (let i = 0; i < numWeeks; i++) {
                    const weekStart = new Date(currentDate);
                    const weekEnd = new Date(currentDate.setDate(currentDate.getDate() + 6));
                    labels.push(
                        `Week ${i + 1}: ${weekStart.toLocaleDateString()} - ${weekEnd.toLocaleDateString()}`
                    );
                    currentDate.setDate(currentDate.getDate() + 1); // Move to the next week
                }

                // Update chart data
                datas = Array.from({
                    length: numWeeks
                }, () => Math.floor(Math.random() * 100)); // Generate random data for each week
                myChart.data.labels = labels;
                myChart.data.datasets[0].data = datas;

                // Update the chart
                myChart.update();
            }

            // Call the updateChart function when the page loads and when the monthSelect value changes
            updateChart();
            monthSelect.addEventListener('change', updateChart);

            // Update chart when the window is resized
            window.addEventListener('resize', updateChart);
        });
    </script>
@endpush
