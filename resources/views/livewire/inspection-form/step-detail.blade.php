<div class="pb-4 ">
    @php
        $showDocumentInfo = false;
    @endphp
    <div class="card mb-4 @if (!$showDocumentInfo) d-none @endif">
        <div class="card-body">
            <h6 class="text-primary border-bottom pb-1 fw-bold mb-3">Document Information</h6>
            <div class="mb-3">
                <label class="form-label">Inspection Report Document Number <span class="text-danger">*</span></label>
                <input type="text" class="form-control bg-secondary-subtle"
                    wire:model.blur="inspection_report_document_number" readonly>
                @error('inspection_report_document_number')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Document Number <span class="text-danger">*</span></label>
                <input type="text" class="form-control bg-secondary-subtle" wire:model.blur="document_number"
                    readonly>
                @error('document_number')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>
    </div>
    <div class="row mb-4">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 col-lg-6">
                            <label class="col-form-label">Operator</label>
                        </div>
                        <div class="col">
                            <input type="text" class="form-control-plaintext text-secondary" wire:model="operator"
                                disabled>
                        </div>
                        <div class="col-12 col-lg-6">
                            <label class="col-form-label">Shift</label>
                        </div>
                        <div class="col">
                            <input type="text" class="form-control-plaintext text-secondary"
                                @if ($shift) wire:model="shift" @else value="Not Assigned" @endif
                                disabled>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            @php
                $locked = empty($shift) || empty($operator);
            @endphp

            <div class="card position-relative {{ $locked ? 'opacity-50' : '' }}">
                <div class="card-body {{ $locked ? 'pe-none' : '' }}"> {{-- pe-none = no pointer events --}}
                    <div class="row">

                        {{-- ───── Period selector ───── --}}
                        <div class="col-12 col-lg-6 mb-3">
                            <label class="form-label d-block mb-1">
                                Period <span class="text-danger">*</span>
                            </label>

                            <div class="btn-group w-100" role="group" aria-label="Period selector">
                                @foreach ([1, 2, 3, 4] as $p)
                                    <button type="button"
                                        class="btn {{ $period == $p ? 'btn-primary' : 'btn-outline-primary' }}"
                                        wire:click="selectPeriod({{ $p }})" @disabled($locked)>
                                        P{{ $p }}
                                    </button>
                                @endforeach
                            </div>

                            @error('period')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- ───── Start / End time ───── --}}
                        <div class="col">
                            <div class="row g-3">
                                <div class="col">
                                    <label class="form-label">Start Time <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control @error('start_time') is-invalid @enderror"
                                        wire:model.blur="start_time" @disabled($locked)>
                                    @error('start_time')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col">
                                    <label class="form-label">End Time <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control @error('end_time') is-invalid @enderror"
                                        wire:model.blur="end_time" @disabled($locked)>
                                    @error('end_time')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-end mt-3">
                        <button type="button" class="btn btn-outline-primary" wire:click='saveStep'>Change</button>
                        <button type="button" class="btn btn-outline-danger" wire:click='resetStep'>Reset All</button>
                    </div>
                </div>

                {{-- ───── Overlay when locked ───── --}}
                @if ($locked)
                    <div
                        class="position-absolute top-0 start-0 w-100 h-100 d-flex
                    flex-column justify-content-center align-items-center
                    bg-light bg-opacity-75 text-center rounded">
                        <i class="bi bi-lock-fill fs-1 mb-2"></i>
                        <span class="fw-semibold">
                            Assign <em>Operator</em>, <em>Shift</em> and <em>Period</em> first to edit this section.
                        </span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @php
        $locked = empty($shift) || empty($operator) || empty(session('stepDetailSaved'));
        $currentPeriod = session('stepDetailSaved.period');
        $overlay = $locked
            ? new Illuminate\Support\HtmlString(
                'Assign <em>Operator</em>, <em>Shift</em>' .
                    ($period ? '' : ' and choose a <em>Period</em>') .
                    ' first to edit this section.',
            )
            : "P$currentPeriod";
    @endphp

    <section>
        <x-lockable-card :locked="$locked" :overlay="$overlay" title="First Inspection">
            @livewire('inspection-form.step-first', ['detail_inspection_report_document_number' => $document_number], key('step-first-' . $reloadToken))
        </x-lockable-card>
    </section>

    <section class="mt-4">
        <x-lockable-card :locked="$locked" :overlay="$overlay"
            title='Measurements <span class="fw-normal text-secondary fs-6">(optional)</span>'>
            @livewire('inspection-form.step-measurement', ['inspection_report_document_number' => $inspection_report_document_number], key('step-measurement-' . $reloadToken))
        </x-lockable-card>
    </section>

    <section class="mt-5">
        <x-lockable-card :locked="$locked" :overlay="$overlay" title="Second Inspection">
            @livewire('inspection-form.step-second', ['detail_inspection_report_document_number' => $document_number], key('step-second-' . $reloadToken))
        </x-lockable-card>
    </section>

    <section class="mt-4">
        <x-lockable-card :locked="$locked" :overlay="$overlay" title="Over All Judgement">
            @livewire('inspection-form.step-judgement', ['detail_inspection_report_document_number' => $document_number], key('step-judgement-' . $reloadToken))
        </x-lockable-card>
    </section>

    {{-- <section class="mt-4">
        <x-lockable-card :locked="$locked" :overlay="$overlay" title="Problems">
            @livewire('inspection-form.step-problem', ['inspection_report_document_number' => $inspection_report_document_number], key('step-problem-' . $reloadToken))
        </x-lockable-card>
    </section> --}}

    {{-- <section class="mt-4">
        <x-lockable-card :locked="$locked" :overlay="$overlay" title="Quantities">
            @livewire('inspection-form.step-quantity', ['inspection_report_document_number' => $inspection_report_document_number], key('step-quantity-' . $reloadToken))
        </x-lockable-card>
    </section> --}}
</div>
