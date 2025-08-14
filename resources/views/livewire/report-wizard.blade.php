<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-body">
                    <div x-data="{ step: @entangle('step') }" class="mb-4 d-flex align-items-center justify-content-between">
                        @php
                            $steps = [1 => 'Header', 2 => 'Details', 3 => 'Defects', 4 => 'Preview'];
                            $totalSteps = count($steps);
                        @endphp

                        @foreach ($steps as $s => $label)
                            {{-- Step Circle --}}
                            <div class="text-center" style="min-width: 80px;">
                                <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center"
                                    style="width: 40px; height: 40px; font-weight: bold; transition: all 0.7s ease;"
                                    :class="{
                                        'bg-primary text-white border-primary': step >= {{ $s }},
                                        'bg-transparent text-primary border border-primary': step < {{ $s }}
                                    }">
                                    {{ $s }}
                                </div>
                                <small class="d-block mt-1">{{ $label }}</small>
                            </div>

                            {{-- Progress Bar Between Circles --}}
                            @if ($s < $totalSteps)
                                <div class="flex-grow-1 mx-2">
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated"
                                            :class="step > {{ $s }} ? 'bg-primary' : 'bg-light'"
                                            :style="'width: ' + (step > {{ $s }} ? '100%' : '0%') +
                                            '; transition: width 0.7s ease;'">
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                    <hr>
                    @if (session()->has('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if (session()->has('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @if ($step === 1)
                        @livewire('report-wizard-header', ['reportId' => $reportId])
                    @elseif ($step === 2)
                        @livewire('report-wizard-details', ['reportId' => $reportId])
                    @elseif ($step === 3)
                        @livewire('report-wizard-defects', ['reportId' => $reportId])
                    @elseif ($step === 4)
                        @livewire('report-wizard-preview', ['reportId' => $reportId])
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
