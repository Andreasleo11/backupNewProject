@extends('layouts.guest')

@section('content')
    @php
        $h = $inspectionReport; // shorthand
        dd($h);
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
                        ['Inspection&nbsp;Date', $r->inspection_date, 'calendar-event'],
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

            {{-- part details accordion --------------------------------------- --}}
            <div class="accordion" id="partAccordion">
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingPart">
                        <button class="accordion-button collapsed fw-bold" data-bs-toggle="collapse"
                            data-bs-target="#collapsePart" aria-expanded="false">
                            Part Details
                        </button>
                    </h2>
                    <div id="collapsePart" class="accordion-collapse collapse" data-bs-parent="#partAccordion">
                        <div class="accordion-body">
                            <dl class="row mb-0">
                                <dt class="col-sm-3">Number</dt>
                                <dd class="col-sm-9">{{ $r->part_number }}</dd>
                                <dt class="col-sm-3">Name</dt>
                                <dd class="col-sm-9">{{ $r->part_name }}</dd>
                                <dt class="col-sm-3">Material</dt>
                                <dd class="col-sm-9">{{ $r->material }}</dd>
                                <dt class="col-sm-3">Color</dt>
                                <dd class="col-sm-9">{{ $r->color }}</dd>
                                <dt class="col-sm-3">Weight</dt>
                                <dd class="col-sm-9">{{ $r->weight }} {{ $r->weight_uom }}</dd>
                                <dt class="col-sm-3">Tool / Cavity</dt>
                                <dd class="col-sm-9">{{ $r->tool_number_or_cav_number }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            {{-- quarter pills -------------------------------------------------- --}}
            <ul class="nav nav-pills my-4" id="qTab" role="tablist">
                @foreach ($quarters as $i => $q)
                    <li class="nav-item">
                        <button class="nav-link border border-primary me-2 {{ $i === 0 ? 'active' : '' }}"
                            id="q{{ $q }}-tab" data-bs-toggle="tab" data-bs-target="#q{{ $q }}-pane"
                            role="tab">
                            Quarter {{ $q }}
                        </button>
                    </li>
                @endforeach
            </ul>

            {{-- quarter panes -------------------------------------------------- --}}
            <div class="tab-content" id="qTabContent">
                @foreach ($quarters as $i => $q)
                    @php $d = $r->detailInspectionReports->firstWhere('quarter',$q); @endphp
                    <div class="tab-pane fade {{ $i === 0 ? 'show active' : '' }}" id="q{{ $q }}-pane"
                        role="tabpanel">
                        {{-- timeline badge --}}
                        <p class="text-muted mb-4">
                            <i class="bi bi-clock me-1"></i>
                            {{ $d->start_datetime }} – {{ $d->end_datetime }}
                        </p>

                        {{-- accordion per dataset -------------------------------- --}}
                        <div class="accordion" id="accordionQ{{ $q }}">

                            {{-- First Inspection --}}
                            <x-inspection-section parent="accordionQ{{ $q }}" id="first{{ $q }}"
                                title="First Inspection">
                                @include('inspection.partials.first-inspection-table', [
                                    'rows' => $d->firstInspections,
                                ])
                            </x-inspection-section>

                            {{-- Measurement Data --}}
                            <x-inspection-section parent="accordionQ{{ $q }}" id="measure{{ $q }}"
                                title="Measurement Data">
                                @include('inspection.partials.measurement-table', [
                                    'rows' => $h->measurementData,
                                ])
                            </x-inspection-section>

                            {{-- Second Inspection (and its children) --}}
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

                            {{-- Problems --}}
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
