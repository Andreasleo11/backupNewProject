@extends('layouts.guest')

@section('content')
    @php
        $h = $inspectionReport; // shorthand
    @endphp

    <div class="container py-4">

        {{-- =============================================================== --}}
        {{--  Inspection Report – Show                                       --}}
        {{-- =============================================================== --}}
        @php
            $r = $inspectionReport; // shorthand
            $quarters = $r->detailInspectionReports->pluck('quarter')->sort(); // [1,2,3,4]
        @endphp

        <div class="container-xl py-4">

            {{-- back link ---------------------------------------------------- --}}
            <a href="{{ route('inspection-report.index') }}" class="text-decoration-none small">
                <i class="bi bi-arrow-left-circle me-1"></i> Back to list
            </a>

            {{-- title --------------------------------------------------------- --}}
            <h3 class="mt-2 d-flex align-items-center">
                <i class="bi bi-file-earmark-text me-2"></i> Inspection Report
                <span class="badge bg-primary ms-2">{{ $r->document_number }}</span>
            </h3>

            {{-- ===== headline cards ===== ----------------------------------- --}}
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-3 my-4">
                @php
                    $cards = [
                        ['Inspection Date', $r->inspection_date, 'calendar-event'],
                        ['Shift', $r->shift, 'sun'],
                        ['Customer', $r->customer, 'person-badge'],
                        ['Machine', $r->machine_number, 'cpu'],
                    ];
                @endphp
                @foreach ($cards as [$label, $value, $icon])
                    <div class="col">
                        <div class="card shadow-sm h-100 border-0">
                            <div class="card-body py-3">
                                <small class="text-muted text-uppercase">
                                    <i class="bi bi-{{ $icon }} me-1"></i>{{ $label }}
                                </small>
                                <div class="fw-semibold fs-6 text-break">{{ $value }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- part details card -------------------------------------------------- --}}
            <div class="card shadow-sm my-4">
                <div class="card-header bg-primary-subtle text-dark fw-bold py-3 fs-5">
                    <i class="bi bi-box me-1"></i> Part Details
                </div>

                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-sm-3 text-muted">Number</dt>
                        <dd class="col-sm-9">{{ $r->part_number }}</dd>

                        <dt class="col-sm-3 text-muted">Name</dt>
                        <dd class="col-sm-9">{{ $r->part_name }}</dd>

                        <dt class="col-sm-3 text-muted">Material</dt>
                        <dd class="col-sm-9">{{ $r->material }}</dd>

                        <dt class="col-sm-3 text-muted">Color</dt>
                        <dd class="col-sm-9">{{ $r->color }}</dd>

                        <dt class="col-sm-3 text-muted">Weight</dt>
                        <dd class="col-sm-9">
                            {{ $r->weight }}&nbsp;<span class="text-muted">{{ $r->weight_uom }}</span>
                        </dd>

                        <dt class="col-sm-3 text-muted">Tool&nbsp;/ Cavity</dt>
                        <dd class="col-sm-9">{{ $r->tool_number_or_cav_number }}</dd>
                    </dl>
                </div>
            </div>


            {{-- quarter pills ---------------------------------------------------- --}}
            @php
                /** quarters that actually have detail rows */
                $filledQuarters = $r->detailInspectionReports->pluck('quarter')->all(); // e.g. [1,3]
            @endphp

            <ul class="nav nav-pills my-4" id="qTab" role="tablist">
                @foreach (range(1, 4) as $q)
                    @php $hasData = in_array($q, $filledQuarters); @endphp

                    <li class="nav-item">
                        <button
                            class="nav-link me-2
                           border {{ $hasData ? 'border-primary' : 'border-secondary text-muted opacity-50' }}
                           {{ $loop->first ? 'active' : '' }}"
                            id="q{{ $q }}-tab" data-bs-toggle="tab" data-bs-target="#q{{ $q }}-pane"
                            type="button" role="tab" @if (!$hasData) aria-disabled="true" @endif>
                            Quarter {{ $q }}
                        </button>
                    </li>
                @endforeach
            </ul>


            {{-- quarter panes ---------------------------------------------------- --}}
            <div class="tab-content" id="qTabContent">
                @foreach (range(1, 4) as $q)
                    @php
                        $d = $r->detailInspectionReports->firstWhere('quarter', $q); // may be null
                        $hasData = !is_null($d);
                    @endphp

                    <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="q{{ $q }}-pane"
                        role="tabpanel">

                        @if (!$hasData)
                            <div class="alert alert-secondary my-4" role="alert">
                                <i class="bi bi-info-circle me-1"></i>
                                No data entered for Quarter {{ $q }} yet.
                            </div>
                            @continue
                        @endif

                        {{-- timeline badge ------------------------------------------------ --}}
                        @php
                            $start = Carbon\Carbon::parse($d->start_datetime);
                            $end = Carbon\Carbon::parse($d->end_datetime);

                            // duration
                            $hours = $start->diffInHours($end);
                            $mins = $start->diffInMinutes($end) % 60;
                            $dur = ($hours ? $hours . ' h ' : '') . $mins . ' m';

                            // same day?
                            $sameDay = $start->isSameDay($end);
                        @endphp

                        <p class="mb-4">

                            {{-- ▼ Date(s) ---------------------------------------------------- --}}
                            @if ($sameDay)
                                <span class="text-muted">
                                    <i class="bi bi-calendar-event me-1"></i>
                                    {{ $start->format('d M Y') }}
                                </span>
                            @else
                                <span class="text-muted">
                                    <i class="bi bi-calendar-event me-1"></i>
                                    {{ $start->format('d M Y') }}
                                </span>
                                <span class="text-muted ms-2">
                                    <i class="bi bi-arrow-right"></i>
                                </span>
                                <span class="text-muted ms-2">
                                    <i class="bi bi-calendar-event me-1"></i>
                                    {{ $end->format('d M Y') }}
                                </span>
                            @endif

                            {{-- ▼ Time range ------------------------------------------------ --}}
                            <span class="badge bg-light text-dark ms-3">
                                <i class="bi bi-clock me-1"></i>
                                {{ $start->format('H:i') }} &rarr; {{ $end->format('H:i') }}
                            </span>

                            {{-- ▼ Duration --------------------------------------------------- --}}
                            <span class="badge bg-secondary ms-1">
                                <i class="bi bi-hourglass-split me-1"></i>{{ $dur }}
                            </span>
                        </p>



                        {{-- accordion per dataset (unchanged) ----------------------------- --}}
                        <div class="accordion" id="accordionQ{{ $q }}">
                            {{-- First Inspection --}}
                            <x-inspection-section parent="accordionQ{{ $q }}" id="first{{ $q }}"
                                title="First Inspection">
                                @include('inspection.partials.first-inspection-table', [
                                    'rows' => $d->firstInspections,
                                ])
                            </x-inspection-section>

                            {{-- Measurement Data (optional) --}}
                            <x-inspection-section parent="accordionQ{{ $q }}" id="measure{{ $q }}"
                                title="Measurement Data">
                                @include('inspection.partials.measurement-table', [
                                    'rows' => $h->measurementData,
                                ])
                            </x-inspection-section>

                            {{-- Second Inspection & children --}}
                            <x-inspection-section parent="accordionQ{{ $q }}" id="second{{ $q }}"
                                title="Second Inspection">
                                @include('inspection.partials.second-inspection', [
                                    'second' => $d->secondInspections,
                                ])
                            </x-inspection-section>

                            {{-- Judgement & Quantity --}}
                            <x-inspection-section parent="accordionQ{{ $q }}" id="result{{ $q }}"
                                title="Results & Quantity">
                                @include('inspection.partials.results', [
                                    'judgement' => $d->judgementData,
                                    'quantity' => $h->quantityData,
                                ])
                            </x-inspection-section>

                            {{-- Problems / Downtime --}}
                            <x-inspection-section parent="accordionQ{{ $q }}" id="problem{{ $q }}"
                                title="Problems / Downtime">
                                @include('inspection.partials.problems', ['rows' => $h->problemData])
                            </x-inspection-section>
                        </div>
                    </div>
                @endforeach
            </div>

        </div>
    @endsection
