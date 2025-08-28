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
      $periods = $r->detailInspectionReports->pluck('period')->sort(); // [1,2,3,4]
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

      {{-- period pills ---------------------------------------------------- --}}
      @php
        /** periods that actually have detail rows */
        $filledPeriods = $r->detailInspectionReports->pluck('period')->all(); // e.g. [1,3]
      @endphp

      <ul class="nav nav-pills my-4" id="qTab" role="tablist">
        @foreach (range(1, 4) as $p)
          @php $hasData = in_array($p, $filledPeriods); @endphp

          <li class="nav-item">
            <button
              class="nav-link me-2
                           border {{ $hasData ? 'border-primary' : 'border-secondary text-muted opacity-50' }}
                           {{ $loop->first ? 'active' : '' }}"
              id="q{{ $p }}-tab" data-bs-toggle="tab"
              data-bs-target="#q{{ $p }}-pane" type="button" role="tab"
              @if (!$hasData) aria-disabled="true" @endif>
              Period {{ $p }}
            </button>
          </li>
        @endforeach
      </ul>

      {{-- period panes ---------------------------------------------------- --}}
      <div class="tab-content" id="pTabContent">
        @foreach (range(1, 4) as $p)
          @php
            $d = $r->detailInspectionReports->firstWhere('period', $p); // may be null
            $hasData = !is_null($d);
          @endphp

          <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
            id="q{{ $p }}-pane" role="tabpanel">

            @if (!$hasData)
              <div class="alert alert-secondary my-4" role="alert">
                <i class="bi bi-info-circle me-1"></i>
                No data entered for Period {{ $p }} yet.
              </div>
            @else
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
              <div class="accordion" id="accordionQ{{ $p }}">
                {{-- First Inspection --}}
                <x-inspection-section parent="accordionQ{{ $p }}"
                  id="first{{ $p }}" title="First Inspection">
                  @include('inspection.partials.first-inspection-table', [
                      'rows' => $d->firstInspections,
                  ])
                </x-inspection-section>

                {{-- Dimension Data (optional) --}}
                <x-inspection-section parent="accordionQ{{ $p }}"
                  id="measure{{ $p }}" title="Dimension Data">
                  @include('inspection.partials.dimension-table', [
                      'rows' => $h->dimensionData,
                  ])
                </x-inspection-section>

                {{-- Second Inspection & children --}}
                <x-inspection-section parent="accordionQ{{ $p }}"
                  id="second{{ $p }}" title="Second Inspection">
                  @include('inspection.partials.second-inspection', [
                      'second' => $d->secondInspections,
                  ])
                </x-inspection-section>

                {{-- Judgement --}}
                <x-inspection-section parent="accordionQ{{ $p }}"
                  id="result{{ $p }}" title="Judgement">
                  @include('inspection.partials.judgement', [
                      'judgement' => $d->judgementData,
                  ])
                </x-inspection-section>
              </div>
            @endif
          </div>
        @endforeach
      </div>

      {{-- =============================================================== --}}
      {{--  Problems  (per shift, not per period)               --}}
      {{-- =============================================================== --}}
      @php
        // Grab all problems tied to this inspection report / shift.
        // Adjust the property or relation name if it’s different in your model.
        $problemRows = $r->problemData ?? collect(); // collection or array
      @endphp

      <h4 class="fw-bold text-primary-emphasis mt-5 mb-3">
        <i class="bi bi-bug-fill me-1"></i> Problems
        <span class="badge bg-primary-subtle text-primary-emphasis ms-2">
          Shift {{ $r->shift }}
        </span>
      </h4>

      @include('inspection.partials.problems', ['rows' => $problemRows])

      {{-- =============================================================== --}}
      {{--  Quantities  (per shift, not per period)                        --}}
      {{-- =============================================================== --}}
      @php
        /** 1️⃣  Get the summary array you saved for this shift.
         *  If you loaded it in the controller as  $inspectionReport->quantityData
         *  leave the line below; otherwise change to your relation or accessor.
         */
        $qty = $r->quantityData ?? [];
        // dd($qty);

        /** 2️⃣  Nice labels for each metric */
        $labels = [
            'output_quantity' => 'Total Output',
            'pass_quantity' => 'Total Pass',
            'reject_quantity' => 'Total Reject',
            'sampling_quantity' => 'Total Sample',
            'ng_sample_quantity' => 'Total NG Sample',

            'pass_rate' => 'Pass Rate (%)',
            'reject_rate' => 'Reject Rate (%)',
            'ng_sample_rate' => 'NG Sample Rate (%)',
        ];
      @endphp

      <h4 class="fw-bold text-primary-emphasis mt-5 mb-3">
        <i class="bi bi-bar-chart-fill me-1"></i> Quantities
        <span class="badge bg-primary-subtle text-primary-emphasis ms-2">
          Shift {{ $r->shift }}
        </span>
      </h4>

      @if ($qty)
        @php
          /* colour helpers -------------------------------------------------*/
          $metricColor = [
              'output_quantity' => 'primary',
              'pass_quantity' => 'success',
              'reject_quantity' => 'danger',
              'sampling_quantity' => 'info',
              'ng_sample_quantity' => 'warning',
          ];
          $icon = [
              'output_quantity' => 'stack',
              'pass_quantity' => 'check-circle',
              'reject_quantity' => 'x-circle',
              'sampling_quantity' => 'clipboard-data',
              'ng_sample_quantity' => 'exclamation-circle',
          ];

          /* rates to one decimal so they fit nicely in badges */
          $rate = fn($k) => number_format($qty->$k, 1);
        @endphp

        <div class="card shadow-sm border-0 mb-5">
          <div class="card-body">

            {{-- ▶ Metric tiles ------------------------------------------------ --}}
            <div class="row row-cols-2 row-cols-md-3 row-cols-lg-5 g-4 text-center">
              @foreach (['output_quantity', 'pass_quantity', 'reject_quantity', 'sampling_quantity', 'ng_sample_quantity'] as $k)
                <div class="col">
                  <div class="card h-100 border-0 bg-{{ $metricColor[$k] }} bg-opacity-10">
                    <div class="card-body p-3">
                      <small class="text-muted text-uppercase d-block mb-1">
                        <i class="bi bi-{{ $icon[$k] }} me-1"></i>{{ $labels[$k] }}
                      </small>
                      <span class="display-6 fw-semibold text-{{ $metricColor[$k] }}">
                        {{ $qty->$k }}
                      </span>
                    </div>
                  </div>
                </div>
              @endforeach
            </div>

            {{-- ▶ Rate gauges ------------------------------------------------- --}}
            {{-- <h6 class="text-primary-emphasis fw-bold mt-4 mb-3">
                            Rates
                        </h6> --}}

            @php
              /* helper to format % to 1-decimal */
              $f = fn($v) => number_format($v, 1);
            @endphp

            <div class="row row-cols-1 row-cols-md-5 g-3 text-center">

              {{-- Spacer --}}
              <div class="col"></div>

              {{-- ───────── Pass Rate ───────── --}}
              <div class="col">
                <div class="card h-100 border-0">
                  <div class="card-body p-3">
                    <small class="text-muted d-block mb-1">Pass&nbsp;Rate</small>
                    @php $p = $qty->pass_rate; @endphp
                    <div class="progress position-relative " style="height:20px;">
                      <div class="progress-bar bg-success-subtle  "
                        style="width: {{ $p }}%; min-width: 5px;"></div>
                      <span
                        class="position-absolute top-50 start-50 translate-middle small fw-semibold text-dark">
                        {{ $f($p) }} %
                      </span>
                    </div>
                  </div>
                </div>
              </div>

              {{-- ───────── Reject Rate ───────── --}}
              <div class="col">
                <div class="card h-100 border-0">
                  <div class="card-body p-3">
                    <small class="text-muted d-block mb-1">Reject&nbsp;Rate</small>
                    @php $rj = $qty->reject_rate; @endphp
                    <div class="progress position-relative" style="height:20px;">
                      <div class="progress-bar bg-danger-subtle"
                        style="width: {{ $rj }}%; min-width: 5px;"></div>
                      <span
                        class="position-absolute top-50 start-50 translate-middle small fw-semibold text-dark">
                        {{ $f($rj) }} %
                      </span>
                    </div>
                  </div>
                </div>
              </div>

              {{-- Spacer --}}
              <div class="col"></div>

              {{-- ───────── NG-Sample Rate ─────── --}}
              <div class="col">
                <div class="card h-100 border-0">
                  <div class="card-body p-3">
                    <small class="text-muted d-block mb-1">NG&nbsp;Sample&nbsp;Rate</small>
                    @php $ng = $qty->ng_sample_rate; @endphp
                    <div class="progress position-relative" style="height:20px;">
                      <div class="progress-bar bg-warning-subtle"
                        style="width: {{ $ng }}%; min-width: 5px;"></div>
                      <span
                        class="position-absolute top-50 start-50 translate-middle small fw-semibold text-dark">
                        {{ $f($ng) }} %
                      </span>
                    </div>
                  </div>
                </div>
              </div>

            </div> {{-- /row rates --}}
          </div>
        </div>
      @else
        <div class="alert alert-secondary" role="alert">
          <i class="bi bi-info-circle me-1"></i>
          No quantity summary saved for this shift.
        </div>
      @endif
    </div>
  @endsection
