@php
    $sections = [
        'Detail Inspection' => $detailData,
        'First Data' => $firstData,
        'Measurement Data' => $measurementData,
        'Second Data' => $secondData,
        'Sampling Data' => $samplingData,
        'Packaging Data' => $packagingData,
        'Judgement Data' => $judgementData,
        'Problem Data' => $problemData,
        'Quantity Data' => $quantityData,
    ];

    /** The “theme” you want each section to live under */
    $groups = [
        'Initial Inspection' => ['Detail Inspection', 'First Data', 'Measurement Data'],
        'Second Inspection' => ['Second Data', 'Sampling Data', 'Packaging Data'],
        'Results & Counts' => ['Judgement Data', 'Quantity Data'],
        'Problems / Downtime' => ['Problem Data'],
    ];

    // rainbow-ish colour helpers  — tweak to taste
    $badge = fn($word) => in_array(strtolower($word), ['ok', 'pass'])
        ? 'success'
        : (in_array(strtolower($word), ['ng', 'fail', 'reject'])
            ? 'danger'
            : 'secondary');

    /** -------------------------------------------------
     *  Which header fields live under which heading?   */
    $headerGroups = [
        'Document Info' => ['document_number', 'inspection_date', 'shift', 'operator'],
        'Customer' => ['customer'],
        'Part Details' => [
            'part_number',
            'part_name',
            'material',
            'color',
            'weight',
            'weight_uom',
            'tool_number_or_cav_number',
        ],
        'Machine' => ['machine_number'],
    ];

    /** Human-readable labels for each field (optional) */
    $label = fn($key) => ucwords(str_replace(['tool_number_or_cav_number', '_'], ['tool / cav number', ' '], $key));
    $haveData = [];
@endphp

<div class="container-xl">

    <!-- ►► Header ◄◄ ------------------------------------------------------ -->
    <h3 class="fw-bold mb-3 d-flex align-items-center">
        <i class="bi bi-eye-fill me-2"></i> Final Preview @if ($headerData)
            Shift {{ $headerData['shift'] }}
        @endif
    </h3>

    @if ($headerData)
        {{-- ░░ Missing-data alert ░░ ------------------------------------------- --}}
        @if (!empty($holeReport))
            @php
                /* readable label helper */
                $pretty = fn($k) => $sectionLabels[$k] ?? ucwords(str_replace('_', ' ', $k));
            @endphp

            <div class="alert alert-warning border-warning px-4 py-3" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-exclamation-triangle-fill fs-3 text-warning me-2"></i>
                    <div>
                        <h6 class="mb-0 fw-bold">Data incomplete</h6>
                        <small class="text-body-secondary">Fill the missing periods before submitting</small>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:30%">Section</th>
                            @foreach (range(1, 4) as $n)
                                <th class="text-center">P{{ $n }}</th>
                            @endforeach
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($holeReport as $backendKey => $periods)
                            <tr>
                                <td class="fw-semibold">{{ $pretty($backendKey) }}</td>

                                @foreach (range(1, 4) as $n)
                                    @php $isMissing = in_array($n, $periods); @endphp
                                    <td class="text-center">
                                        <span
                                            class="badge rounded-pill
                               {{ $isMissing ? 'text-danger-emphasis bg-danger-subtle' : 'text-success-emphasis bg-success-subtle' }}">
                                            <i class="bi {{ $isMissing ? 'bi-x-lg' : 'bi-check-lg' }}"></i>
                                        </span>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
        {{-- ===== Grouped Header ===== --}}
        @foreach ($headerGroups as $groupTitle => $fields)
            {{-- Skip a group if *all* its fields are missing --}}
            @php
                $hasAny = collect($fields)->contains(fn($f) => isset($headerData[$f]));
            @endphp
            @continue(!$hasAny)

            <h5 class="fw-bold text-primary-emphasis mb-2 mt-4">
                {{ $groupTitle }}
            </h5>

            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3 mb-2">
                @foreach ($fields as $f)
                    @continue(!isset($headerData[$f])) {{-- field absent? skip --}}

                    <div class="col">
                        <div class="card h-100 shadow-sm border">
                            <div class="card-body py-3">
                                <small class="text-muted text-uppercase">
                                    {{ $label($f) }}
                                </small>
                                <div class="fw-semibold fs-6 text-break">
                                    {{ $headerData[$f] }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach

        @php
            /* ① find periods that DO have data (same as before) */
            $periodSet = [];
            foreach ($sections as $sectionData) {
                foreach ($sectionData as $key => $val) {
                    if (preg_match('/^p([1-4])$/', $key, $m)) {
                        $periodSet[(int) $m[1]] = true; // p1…p4 → 1…4
                    }
                }
            }
            $haveData = array_keys($periodSet); // e.g. [1,3]
        @endphp

        <!-- ►► Period Pils ◄◄ ------------------------------------------------ -->
        <ul class="nav nav-pills my-4" id="periodTab" role="tablist">
            @foreach (range(1, 4) as $p)
                @php  $hasData = in_array($p, $haveData);  @endphp
                <li class="nav-item" role="presentation">
                    <button
                        class="nav-link me-2
                       border {{ $hasData ? 'border-primary' : 'border-secondary text-muted opacity-50' }}
                       @if ($loop->first) active @endif"
                        id="p{{ $p }}-tab" data-bs-toggle="tab" data-bs-target="#p{{ $p }}-pane"
                        type="button" role="tab" @if (!$hasData) aria-disabled="true" @endif>
                        Period {{ $p }}
                    </button>
                </li>
            @endforeach
        </ul>
        <div class="tab-content" id="periodTabContent">
            @foreach (range(1, 4) as $p)
                @php  $hasData = in_array($p, $haveData);  @endphp
                <div class="tab-pane fade @if ($loop->first) show active @endif"
                    id="p{{ $p }}-pane" role="tabpanel" tabindex="0">

                    @if ($hasData)
                        @foreach ($groups as $groupTitle => $groupSections)
                            @php
                                $hasAnyData = collect($groupSections)
                                    ->filter(fn($s) => isset($sections[$s]["p$p"]))
                                    ->isNotEmpty();
                            @endphp

                            @continue(!$hasAnyData)

                            <h5 class="fw-bold text-primary-emphasis mb-3 mt-4">
                                {{ $groupTitle }}
                            </h5>

                            @foreach ($groupSections as $title)
                                @php $sectionData = $sections[$title] ?? []; @endphp
                                @continue(!isset($sectionData["p$p"]))

                                <div class="card shadow-sm mb-4">
                                    <div class="card-header bg-light d-flex align-items-center">
                                        <h6 class="mb-0 flex-grow-1">{{ $title }}</h6>
                                        <span
                                            class="badge bg-primary-subtle text-primary-emphasis">P{{ $p }}</span>
                                    </div>

                                    <div class="card-body px-2 py-3">
                                        {{-- LIST style --}}
                                        @if (is_array($sectionData["p$p"]) && !array_is_list($sectionData["p$p"]))
                                            <dl class="row mb-0">
                                                @foreach ($sectionData["p$p"] as $k => $v)
                                                    <dt class="col-sm-4 col-lg-3 text-capitalize">
                                                        {{ str_replace('_', ' ', $k) }}</dt>
                                                    <dd class="col-sm-8 col-lg-9">
                                                        @if (in_array($k, ['judgement', 'appearance', 'pass_quantity', 'reject_quantity']))
                                                            <span
                                                                class="badge bg-{{ $badge($v) }} bg-opacity-75">{{ $v }}</span>
                                                        @else
                                                            {{ $v }}
                                                        @endif
                                                    </dd>
                                                @endforeach
                                            </dl>
                                            {{-- TABLE style --}}
                                        @elseif(array_is_list($sectionData["p$p"]))
                                            <div class="table-responsive-md">
                                                <table
                                                    class="table table-striped table-bordered small align-middle mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            @foreach (array_keys($sectionData["p$p"][0]) as $col)
                                                                <th class="text-capitalize">
                                                                    {{ str_replace('_', ' ', $col) }}
                                                                </th>
                                                            @endforeach
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($sectionData["p$p"] as $row)
                                                            <tr>
                                                                @foreach ($row as $k => $v)
                                                                    <td>
                                                                        @if (in_array($k, ['judgement', 'appearance']))
                                                                            <span
                                                                                class="badge bg-{{ $badge($v) }}">{{ $v }}</span>
                                                                        @else
                                                                            {{ $v }}
                                                                        @endif
                                                                    </td>
                                                                @endforeach
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @endforeach {{-- /groups --}}
                    @else
                        {{-- ▒▒ Placeholder when no data ▒▒ --}}
                        <div class="alert alert-secondary my-4" role="alert">
                            <i class="bi bi-info-circle me-1"></i>
                            No data entered for Period {{ $p }} yet.
                        </div>
                    @endif
                </div>
            @endforeach {{-- /periods --}}
        </div>
    @else
        <div>No data yet</div>
    @endif

    <!-- ►► Sticky Footer Bar ◄◄ ------------------------------------------- -->
    <div class="position-sticky bottom-0 bg-body pt-3 pb-2" style="z-index: 10;">
        <div class="d-flex justify-content-end border-top pt-3">
            <button class="btn btn-lg btn-success shadow-sm" @if (!$headerData) disabled @endif
                wire:click="submit">
                <i class="bi bi-check-circle-fill me-2"></i> Submit Final Report
            </button>
        </div>
    </div>
</div>
