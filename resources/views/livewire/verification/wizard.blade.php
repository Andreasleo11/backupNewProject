<div class="container-fluid">
    <div class="row">

        {{-- LEFT SIDEBAR (custom stepper) --}}
        @php
            $labels = [1 => 'Header', 2 => 'Items', 3 => 'Defects', 4 => 'Preview'];
            $statuses = [
                1 => $errors->hasAny(['form.*']) ? 'error' : (filled($form['customer']) ? 'done' : 'todo'),
                2 =>
                    ($errors->hasAny(['items']) || $errors->hasAny(['items.*'])) &&
                    !$errors->hasAny(['items.*.defects.*'])
                        ? 'error'
                        : (count($items)
                            ? 'done'
                            : 'todo'),
                3 => $errors->hasAny(['items.*.defects', 'items.*.defects.*']) ? 'error' : 'done',
                4 => 'todo',
            ];
        @endphp

        <aside class="d-none d-lg-block col-lg-2 border-end bg-body position-sticky stepper-aside"
            style="top: 0; height: 100vh; overflow-y: auto;">
            <div class="p-2">
                <div class="stepper-title">Steps</div>
                <nav class="stepper" aria-label="Wizard steps">
                    <ol class="stepper-list">
                        @foreach ($labels as $s => $label)
                            @php
                                $state = $statuses[$s]; // 'todo' | 'done' | 'error'
                                $isActive = $step === $s;
                            @endphp

                            <li class="stepper-item {{ $state }} {{ $isActive ? 'active' : '' }}">
                                <div class="stepper-node">
                                    <span class="stepper-bullet" aria-hidden="true">
                                        {{-- State glyphs: check / ! / number --}}
                                        @if ($state === 'done')
                                            <i class="bi bi-check-lg"></i>
                                        @elseif($state === 'error')
                                            <i class="bi bi-exclamation-lg"></i>
                                        @else
                                            <span class="stepper-number">{{ $s }}</span>
                                        @endif
                                    </span>
                                    <button type="button" class="stepper-label"
                                        wire:click="goToStep({{ $s }})"
                                        aria-current="{{ $isActive ? 'step' : 'false' }}"
                                        aria-label="Go to step {{ $s }}: {{ $label }}">
                                        <span class="stepper-label-main">{{ $label }}</span>
                                        <span class="stepper-label-sub">
                                            @if ($state === 'done')
                                                Ready
                                            @elseif($state === 'error')
                                                Needs fixing
                                            @else
                                                To-do
                                            @endif
                                        </span>
                                    </button>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </nav>
            </div>
        </aside>

        @pushOnce('extraCss')
            <style>
                /* Tokens */
                .stepper-aside {
                    --sp-1: .25rem;
                    --sp-2: .5rem;
                    --sp-3: .75rem;
                    --sp-4: 1rem;
                    --fg: var(--bs-body-color);
                    --muted: var(--bs-secondary-color, #6c757d);
                    --ok: #0ea5e9;
                    /* cyan-ish primary */
                    --ok-weak: color-mix(in srgb, var(--ok) 16%, transparent);
                    --err: #ef4444;
                    --ring: color-mix(in srgb, var(--ok) 24%, transparent);
                    --bg-alt: color-mix(in srgb, var(--bs-body-bg) 92%, black);
                }

                .stepper-title {
                    font-size: .75rem;
                    text-transform: uppercase;
                    letter-spacing: .08em;
                    color: var(--muted);
                    margin-bottom: var(--sp-2);
                    font-weight: 600;
                }

                .stepper {
                    position: relative;
                }

                .stepper-list {
                    list-style: none;
                    margin: 0;
                    padding: 0;
                }

                .stepper-item {
                    position: relative;
                }

                .stepper-item+.stepper-item {
                    margin-top: .5rem;
                }

                .stepper-node {
                    display: grid;
                    grid-template-columns: 1.75rem 1fr;
                    gap: var(--sp-2);
                    align-items: center;
                }

                .stepper-bullet {
                    width: 1.25rem;
                    height: 1.25rem;
                    border-radius: 999px;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    background: var(--bg-alt);
                    color: var(--muted);
                    border: 1px solid color-mix(in srgb, var(--fg) 8%, transparent);
                    margin-left: .25rem;
                    font-size: .85rem;
                }

                .stepper-number {
                    font-size: .75rem;
                    line-height: 1;
                    font-weight: 600;
                    transform: translateY(-.5px);
                }

                .stepper-label {
                    appearance: none;
                    background: transparent;
                    border: 0;
                    padding: .35rem .5rem;
                    border-radius: .5rem;
                    width: 100%;
                    text-align: left;
                    cursor: pointer;
                }

                .stepper-label:hover {
                    background: color-mix(in srgb, var(--fg) 6%, transparent);
                }

                .stepper-label:focus-visible {
                    outline: 2px solid var(--ring);
                    outline-offset: 2px;
                }

                .stepper-label-main {
                    display: block;
                    font-weight: 600;
                    color: var(--fg);
                }

                .stepper-label-sub {
                    display: block;
                    font-size: .75rem;
                    color: var(--muted);
                    margin-top: 2px;
                }

                /* States */
                .stepper-item.done .stepper-bullet {
                    background: var(--ok-weak);
                    color: var(--ok);
                    border-color: var(--ok-weak);
                }

                .stepper-item.error .stepper-bullet {
                    background: color-mix(in srgb, var(--err) 12%, transparent);
                    color: var(--err);
                    border-color: color-mix(in srgb, var(--err) 22%, transparent);
                }

                .stepper-item.active .stepper-label {
                    background: color-mix(in srgb, var(--ok) 8%, transparent);
                    box-shadow: inset 0 0 0 1px var(--ring);
                }

                .stepper-item.active .stepper-bullet {
                    background: var(--ok);
                    color: #fff;
                    border-color: var(--ok);
                }

                .stepper-item.active .stepper-label-main {
                    color: var(--ok);
                }

                /* High-contrast/dark tweaks */
                @media (prefers-color-scheme: dark) {
                    .stepper-aside {
                        --ok-weak: color-mix(in srgb, var(--ok) 26%, transparent);
                        --bg-alt: color-mix(in srgb, var(--bs-body-bg) 70%, white);
                    }
                }
            </style>
        @endPushOnce

        {{-- RIGHT CONTENT --}}
        <main class="col-12 col-lg-10">
            <div class="container py-4" x-data="{ step: @entangle('step') }" x-init="$root.addEventListener('input', () => $wire.set('isDirty', true), { capture: true });
            $root.addEventListener('change', () => $wire.set('isDirty', true), { capture: true });"
                x-on:livewire:navigated.window="$wire.set('isDirty', true)">

                <span class="badge bg-warning-subtle text-warning-emphasis d-none" wire:dirty.class.remove="d-none"
                    wire:target="form,items,defaultCurrency">
                    Editing…
                </span>


                {{-- Top toolbar --}}
                <div class="d-flex justify-content-between mb-3">
                    <div>
                        <h3 class="mb-0 fw-bold">
                            {{ $report?->id ? "Edit Verification Report #{$report->id}" : 'New Verification Report' }}
                        </h3>
                        @if (!$report?->id)
                            <p class="text-muted">This report will be auto-saved as a draft. You can close the form and
                                continue editing it later.</p>
                        @else
                            @if ($report?->document_number)
                                <div class="small text-muted">Doc#: <span
                                        class="fw-semibold">{{ $report->document_number }}</span></div>
                            @endif
                        @endif
                        <div class="d-flex justify-content-between align-items-center small text-muted mb-3">
                            @if ($autosaveEnabled)
                                <div wire:poll.keep-alive.{{ (int) $autosaveMs }}ms="autosaveDraft" class="d-none">
                                </div>
                            @endif
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

                        <div wire:ignore.self class="modal fade" id="discardDraftModal" tabindex="-1"
                            aria-hidden="true">
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
                                        <button class="btn btn-outline-secondary"
                                            data-bs-dismiss="modal">Cancel</button>
                                        <button class="btn btn-danger" wire:click="clearDraft" data-bs-dismiss="modal">
                                            <i class="bi bi-trash"></i> Discard draft
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- MOBILE/HYBRID progress bar --}}
                <div class="mb-4 d-flex align-items-center column-gap-3 d-lg-none">
                    @php
                        $stepsBar = [1 => 'Header', 2 => 'Items', 3 => 'Defects', 4 => 'Preview'];
                        $totalSteps = count($stepsBar);
                    @endphp
                    @foreach ($stepsBar as $s => $label)
                        <div class="text-center">
                            <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center"
                                style="width: 40px; height: 40px; font-weight: bold; transition: all 0.7s ease;"
                                :class="{
                                    'bg-primary text-white border-primary': step >= {{ $s }},
                                    'bg-transparent text-primary border border-primary': step < {{ $s }}
                                }">
                                {{ $s }}</div>
                            <small class="d-block mt-1">{{ $label }}</small>
                        </div>
                        @if ($s < $totalSteps)
                            <div class="flex-grow-1 mx-2">
                                <div class="progress" style="height:6px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated"
                                        :class="step > {{ $s }} ? 'bg-primary' : 'bg-light'"
                                        :style="'width: ' + (step > {{ $s }} ? '100%' : '0%') +
                                        '; transition: width .7s ease;'">
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>

                {{-- Errors (unchanged) --}}
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <div class="fw-semibold mb-1">Please fix the following:</div>
                        <ul class="mb-0">
                            @foreach ($errors->toArray() as $field => $messages)
                                @php $anchor = 'fld-' . preg_replace('/[^a-z0-9\-]+/i', '-', $field); @endphp
                                <li>
                                    <a href="#{{ $anchor }}" class="alert-link"
                                        onclick="const el=document.getElementById('{{ $anchor }}'); if(el){ el.scrollIntoView({behavior:'smooth', block:'center'}); el.focus(); } return false;">
                                        {{ $messages[0] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Sections (unchanged) --}}
                <section x-show="step === 1" x-cloak>
                    <div class="card border-0">
                        <div class="card-body">
                            <livewire:verification.steps.header wire:model="form" wire:key="step-header" />
                        </div>
                    </div>
                </section>

                <section x-show="step === 2" x-cloak>
                    <div class="card border-0">
                        <div class="card-body pb-0">
                            <livewire:verification.steps.items wire:model="items" :customer="$form['customer']" :default-currency="$defaultCurrency"
                                :active-item="$activeItem" wire:key="step-items" />
                        </div>
                    </div>
                </section>

                <section x-show="step === 3" x-cloak>
                    <div class="card border-0">
                        <div class="card-body pb-0">
                            <livewire:verification.steps.defects wire:model="items" :active-item="$activeItem"
                                wire:key="step-defects" />
                        </div>
                    </div>
                </section>

                <section x-show="step === 4" x-cloak>
                    <livewire:verification.steps.preview :form="$form" :items="$items"
                        wire:key="preview-{{ $previewVersion }}" />
                </section>

                {{-- Sticky action bar (unchanged) --}}
                <div class="position-sticky bottom-0 bg-body border-top py-2 mt-3" style="z-index:10">
                    <div class="d-flex justify-content-end gap-2 align-items-center">
                        @if ($step > 1)
                            <button class="btn btn-outline-secondary" wire:click="prevStep"><i
                                    class="bi bi-arrow-left"></i> Back</button>
                        @else
                            <a href="{{ route('verification.index') }}" class="btn btn-outline-secondary"><i
                                    class="bi bi-list"></i> List</a>
                        @endif

                        @if ($step < 4)
                            <button class="btn btn-primary" wire:click="nextStep">
                                Save & Next <i class="bi bi-arrow-right ms-1"></i>
                            </button>
                        @else
                            <button class="btn btn-success btn-lg"
                                onclick="if (window.__stepInvalid) { if (!confirm('There are validation errors. Are you sure you want to save anyway?')) return false; }"
                                wire:click="save">
                                <i class="bi bi-check2-circle me-1"></i> Submit
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </main>
    </div>

    @pushOnce('extraJs')
        <script>
            document.addEventListener('livewire:init', () => {
                Livewire.on('draft-cleared', () => {
                    window.location.href = "{{ route('verification.index') }}";
                })
            });

            function reinitTooltips() {
                document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el =>
                    bootstrap.Tooltip.getOrCreateInstance(el)
                );
            }

            document.addEventListener('livewire:initialized', () => {
                reinitTooltips();
                // Re-init on any DOM patch
                Livewire.hook('morph.updated', () => reinitTooltips());
                Livewire.hook('morph.added', () => reinitTooltips());
                Livewire.hook('morph.removed', () => reinitTooltips());
            });

            window.scrollToFirstError = () => {
                const first = document.querySelector('.is-invalid, [aria-invalid="true"]');
                if (first) {
                    first.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    first.focus({
                        preventScroll: true
                    });
                }
            };

            // Wire to Livewire events from your step validators
            document.addEventListener('livewire:initialized', () => {
                Livewire.on('step-invalid', () => {
                    window.__stepInvalid = true;
                    setTimeout(window.scrollToFirstError, 0);
                });
                Livewire.on('step-valid', () => {
                    window.__stepInvalid = false;
                });
            });

            // Simple guard that mirrors server dirtiness
            let _unsaved = false;

            // mark unsaved when user edits (the x-init above already sets isDirty server-side)
            document.addEventListener('input', () => {
                _unsaved = true;
            }, {
                capture: true
            });
            document.addEventListener('change', () => {
                _unsaved = true;
            }, {
                capture: true
            });

            // when server finishes autosave, clear the guard
            document.addEventListener('livewire:initialized', () => {
                Livewire.on('saved-clean', () => {
                    _unsaved = false;
                });
            });

            window.addEventListener('beforeunload', (e) => {
                if (!_unsaved) return;
                e.preventDefault();
                e.returnValue = '';
            });
        </script>
    @endPushOnce
</div>
