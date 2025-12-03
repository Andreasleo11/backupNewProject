@extends('new.layouts.app')

@section('content')
    <div class="px-4 py-4 md:px-6 md:py-6">
        {{-- Header --}}
        <header class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-xl md:text-2xl font-semibold tracking-tight text-slate-900">
                    DELIVERY SCHEDULE
                </h1>
                <p class="mt-1 text-sm text-slate-500">
                    Monitoring dan update delivery schedule berdasarkan data SAP.
                </p>
            </div>

            <div class="flex flex-wrap gap-2 justify-start md:justify-end">
                <a href="{{ route('delsched.averagemonth') }}"
                    class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium
                          text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2
                          focus:ring-slate-300 focus:ring-offset-1">
                    Average Per Month
                </a>

                <a href="{{ route('deslsched.step1') }}"
                    class="inline-flex items-center rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white
                          shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2
                          focus:ring-indigo-500 focus:ring-offset-1">
                    Update
                </a>
            </div>
        </header>

        {{-- Realtime Progress Section --}}
        <section class="mt-6">
            <div class="flex items-center justify-between mb-2">
                <h2 class="text-sm font-semibold tracking-wide text-slate-700 uppercase">
                    Update Progress
                </h2>
            </div>

            {{-- Step list --}}
            <div class="space-y-2">
                @foreach ([\App\Jobs\Step1DeliverySchedule::class, \App\Jobs\Step2DeliverySchedule::class, \App\Jobs\Step3DeliverySchedule::class, \App\Jobs\Step4DeliverySchedule::class] as $stepClass)
                    @php
                        $stepName = class_basename($stepClass);
                    @endphp
                    <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-white px-3 py-2">
                        <div class="flex items-center gap-2">
                            <div class="h-2 w-2 rounded-full bg-slate-300" id="{{ $stepClass }}-dot"></div>
                            <p class="text-sm font-medium text-slate-800">
                                {{ $stepName }}
                            </p>
                        </div>

                        <span id="{{ $stepClass }}-status"
                            class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
                            Pending
                        </span>
                    </div>
                @endforeach
            </div>

            {{-- Global progress bar --}}
            <div class="mt-4">
                <div class="flex items-center justify-between mb-1">
                    <p class="text-xs font-medium text-slate-500">
                        Overall progress
                    </p>
                    <span id="progress-label" class="text-xs font-semibold text-slate-500">
                        0%
                    </span>
                </div>
                <div class="h-2.5 w-full rounded-full bg-slate-100 overflow-hidden">
                    <div id="progress-bar" class="h-2.5 rounded-full bg-indigo-500 transition-all duration-300"
                        style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                    </div>
                </div>
            </div>
        </section>

        {{-- Main Content --}}
        <section class="mt-6">
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="p-3 md:p-4 overflow-x-auto">
                    {{ $dataTable->table() }}
                </div>
            </div>

            <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:justify-between">
                <a href="{{ route('indexfinalwip') }}"
                    class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-3 py-2
                          text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none
                          focus:ring-2 focus:ring-slate-300 focus:ring-offset-1">
                    Delivery Schedule (WIP)
                </a>

                <a href="{{ route('rawdelsched') }}"
                    class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-3 py-2
                          text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none
                          focus:ring-2 focus:ring-slate-300 focus:ring-offset-1">
                    Delivery Schedule (RAW)
                </a>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    {{ $dataTable->scripts() }}

    <script type="module">
        const steps = [
            @json(\App\Jobs\ProcessDeliveryScheduleStep1::class),
            @json(\App\Jobs\ProcessDeliveryScheduleStep2::class),
            @json(\App\Jobs\ProcessDeliveryScheduleStep3::class),
            @json(\App\Jobs\ProcessDeliveryScheduleStep4::class),
        ];

        let completedSteps = 0;

        Echo.private('delivery-schedule-progress')
            .listen('.step.progressed', (e) => {
                console.log('Step update:', e);

                const statusEl = document.getElementById(`${e.stepClass}-status`);
                const dotEl = document.getElementById(`${e.stepClass}-dot`);

                if (!statusEl) return;

                if (e.status === 'completed') {
                    statusEl.className =
                        'inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700';
                    statusEl.textContent = 'Completed';

                    if (dotEl) {
                        dotEl.className = 'h-2 w-2 rounded-full bg-emerald-500';
                    }

                    completedSteps = Math.min(steps.length, completedSteps + 1);
                } else if (e.status === 'failed') {
                    statusEl.className =
                        'inline-flex items-center rounded-full bg-red-50 px-3 py-1 text-xs font-medium text-red-700';
                    statusEl.textContent = 'Failed';

                    if (dotEl) {
                        dotEl.className = 'h-2 w-2 rounded-full bg-red-500';
                    }
                } else if (e.status === 'running') {
                    statusEl.className =
                        'inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700';
                    statusEl.textContent = 'Running';

                    if (dotEl) {
                        dotEl.className = 'h-2 w-2 rounded-full bg-indigo-500';
                    }
                }

                const percent = Math.round((completedSteps / steps.length) * 100);
                const progressBar = document.getElementById('progress-bar');
                const progressLabel = document.getElementById('progress-label');

                if (progressBar) {
                    progressBar.style.width = percent + '%';
                    progressBar.setAttribute('aria-valuenow', percent);
                }

                if (progressLabel) {
                    progressLabel.textContent = percent + '%';
                }
            });
    </script>
@endpush
