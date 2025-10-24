<div class="container py-4" x-data="{ step: @entangle('step') }">
    {{-- Top toolbar --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="mb-0 fw-bold">
                {{ $report?->id ? "Edit Verification Report #{$report->id}" : 'New Verification Report' }}</h3>
            @if (!$report?->id)
                <p class="text-muted">
                    This report will be auto-saved as a draft. You can close the form and continue editing it later.
                </p>
            @else
                @if ($report?->document_number)
                    <div class="small text-muted">Doc#: <span class="fw-semibold">{{ $report->document_number }}</span>
                    </div>
                @endif
            @endif
            <div class="d-flex justify-content-between align-items-center small text-muted mb-3">
                <span>
                    @if ($lastAutosaveAt)
                        Autosaved at {{ $lastAutosaveAt }}
                    @else
                        Autosave enabled (every {{ (int) ($autosaveMs / 1000) }}s)
                    @endif
                </span>
            </div>
        </div>

        <div x-data="{
            confirmDiscard() {
                const el = document.getElementById('discardDraftModal');
                if (!el) return;
                const modal = bootstrap.Modal.getOrCreateInstance(el);
                modal.show();
            }
        }">
            <button class="btn btn-outline-danger" @click="confirmDiscard()">
                <i class="bi bi-trash"></i> Discard draft
            </button>

            <div wire:ignore.self class="modal fade" id="discardDraftModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Discard draft?</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            This will permanently remove the auto-saved draft for this report.
                            <br><span class="text-muted small">You can’t undo this action.</span>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn btn-danger" wire:click="clearDraft" data-bs-dismiss="modal">
                                <i class="bi bi-trash"></i> Discard draft
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Progress bar & Stepper --}}
    <div class="mb-4 d-flex align-items-center column-gap-3">
        @php
            $steps = [1 => 'Header', 2 => 'Items', 3 => 'Defects', 4 => 'Preview'];
            $totalSteps = count($steps);
        @endphp

        @foreach ($steps as $s => $label)
            {{-- Step Circle --}}
            <div class="text-center">
                <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center"
                    style="width: 40px; height: 40px; font-weight: bold; transition: all 0.7s ease;"
                    :class="{
                        'bg-primary text-white border-primary': step >= {{ $s }},
                        'bg-transparent text-primary border border-primary': step <
                            {{ $s }}
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

    {{-- Errors --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <div class="fw-semibold mb-1">Please fix the following:</div>
            <ul class="mb-0">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section x-show="step === 1" x-cloak>
        <div class="card border-0">
            <div class="card-body">
                <livewire:verification.steps.header wire:model="form" wire:key="step-header" />
            </div>
        </div>
        <div class="d-flex justify-content-between gap-2 mt-3">
            <a href="{{ route('verification.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i>
                Back to List
            </a>
            <button class="btn btn-primary" wire:click="nextStep"><i class="bi bi-arrow-right"></i> Next</button>
        </div>
    </section>

    <section x-show="step === 2" x-cloak>
        <div class="card border-0">
            <div class="card-body pb-0">
                <livewire:verification.steps.items wire:model="items" :customer="$form['customer']" :default-currency="$defaultCurrency"
                    :active-item="$activeItem" wire:key="step-items" />
            </div>
        </div>
        <div class="d-flex justify-content-between">
            <button class="btn btn-outline-secondary" wire:click="prevStep">
                <i class="bi bi-arrow-left"></i> Back to
                Header
            </button>
            <div class="d-flex gap-2">
                <button class="btn btn-primary" wire:click="nextStep"><i class="bi bi-arrow-right"></i> Next</button>
            </div>
        </div>
    </section>

    <section x-show="step === 3" x-cloak>
        <div class="card border-0">
            <div class="card-body pb-0">
                <livewire:verification.steps.defects wire:model="items" :active-item="$activeItem" wire:key="step-defects" />
            </div>
        </div>
        <div class="d-flex justify-content-between gap-2 mt-3">
            <button class="btn btn-outline-secondary" wire:click="prevStep">
                <i class="bi bi-arrow-left"></i>
                Back to Items
            </button>
            <button class="btn btn-primary" wire:click="save"><i class="bi bi-save"></i> Save</button>

        </div>
    </section>

    @pushOnce('extraJs')
        <script>
            document.addEventListener('livewire:init', () => {
                Livewire.on('draft-cleared', () => {
                    window.location.href = "{{ route('verification.index') }}";
                })
            });
        </script>
    @endPushOnce
</div>
