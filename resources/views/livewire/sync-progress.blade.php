@php
    $phase = $data['phase'] ?? 'idle';
    $isDone = $phase === 'done';
    $isError = $phase === 'error';
    $isRunning = $data['is_running'] ?? !in_array($phase, ['idle', 'done', 'error'], true);
    $percent = (int) ($data['percent'] ?? 0);
    $indeterminate = is_null($data['total'] ?? null) || is_null($data['percent'] ?? null);

    $isSuperAdmin = optional(auth()->user()->role)->name === 'SUPERADMIN';
    //! Still determine this
    $isSuperAdmin = true;

    // Stepper
    $steps = [
        ['key' => 'employees', 'label' => 'Employees'],
        ['key' => 'annual_leave', 'label' => 'Annual Leave'],
        ['key' => 'attendance', 'label' => 'Attendance'],
    ];
    $phaseOrder = [
        'starting' => 0,
        'employees' => 1,
        'annual_leave' => 2,
        'attendance' => 3,
        'done' => 4,
        'error' => 4,
        'idle' => -1,
    ];
    $currentIdx = $phaseOrder[$phase] ?? -1;

    // Events
    $maxEvents = 20;
    $eventsShown = array_slice(array_reverse($events ?? []), 0, $maxEvents);
@endphp

<div @if ($isRunning) wire:poll.200ms="refreshProgress" @endif x-data
    @sync-done.window="
    $wire.refreshProgress();
    setTimeout(() => $wire.refreshProgress(), 120);
    setTimeout(() => $wire.refreshProgress(), 300);
  "
    class="card shadow-sm mb-3" aria-live="polite">
    <div class="card-body">
        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Employee Sync ({{ $companyArea }})</h6>

            <div class="d-flex align-items-center gap-2">
                <span class="badge text-bg-{{ $isDone ? 'success' : ($isError ? 'danger' : 'info') }}">
                    {{ ucfirst($phase) }}
                </span>

                @if ($isSuperAdmin)
                    <button type="button"
                        class="btn btn-sm {{ $compact ? 'btn-outline-secondary' : 'btn-outline-primary' }}"
                        wire:click="toggleDetail" title="{{ $compact ? 'Show details' : 'Show compact' }}">
                        {{ $compact ? 'Details' : 'Compact' }}
                    </button>
                @endif
            </div>
        </div>

        {{-- Status banners --}}
        @if ($isDone)
            <div class="alert alert-success my-2 py-2" role="status">
                ✅ Finished at {{ $data['updated'] ?? '-' }}.
            </div>
        @elseif ($isError)
            <div class="alert alert-danger my-2 py-2" role="alert">
                ❌ {{ $data['message'] ?? 'Sync failed' }}
            </div>
        @endif

        {{-- Progress bar --}}
        @if ($data)
            <div class="mt-2 progress" role="progressbar" aria-label="Sync progress"
                aria-valuenow="{{ $indeterminate ? 100 : $percent }}" aria-valuemin="0" aria-valuemax="100">
                <div class="progress-bar {{ $indeterminate && !$isDone ? 'progress-bar-striped progress-bar-animated' : '' }}"
                    style="width: {{ $indeterminate ? '100%' : $percent . '%' }}">
                    {{ $indeterminate && !$isDone ? 'Working…' : $percent . '%' }}
                </div>
            </div>
        @else
            <span class="small text-muted">-- no process triggered --</span>
        @endif

        {{-- Compact stats (always visible; simple & friendly) --}}
        <div class="mt-2 small text-muted d-flex flex-wrap gap-3">
            <div><strong>Processed:</strong> {{ $data['processed'] ?? 0 }}</div>
            <div><strong>Total:</strong> {{ $data['total'] ?? '—' }}</div>
            @if (!empty($data['last_range']))
                <div class="text-truncate" style="max-width: 50ch;">
                    <strong>Range:</strong> {{ $data['last_range'] }}
                </div>
            @endif
            @if (!empty($data['updated']))
                <div><strong>Updated:</strong> {{ $data['updated'] }}</div>
            @endif
        </div>

        {{-- DETAILED MODE (SUPERADMIN only) --}}
        @if ($isSuperAdmin && !$compact)
            {{-- Stepper --}}
            <div class="d-flex gap-2 align-items-center mt-3 small">
                @foreach ($steps as $i => $s)
                    @php
                        $status =
                            $currentIdx >= $i + 1 || $isDone
                                ? 'done'
                                : ($currentIdx === $i && !$isDone && !$isError
                                    ? 'active'
                                    : 'todo');
                    @endphp
                    <div class="d-flex align-items-center">
                        <div
                            class="rounded-pill px-2 py-1
                 {{ $status === 'done' ? 'bg-success text-white' : ($status === 'active' ? 'bg-primary text-white' : 'bg-light text-muted') }}">
                            {{ $s['label'] }} @if ($status === 'done')
                                <span class="ms-1">✔</span>
                            @endif
                        </div>
                        @if ($i < count($steps) - 1)
                            <div class="mx-2 text-muted">→</div>
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- Message (informational, not error) --}}
            @if (!empty($data['message']) && !$isError)
                <div class="mt-2 alert alert-info py-2 px-3 small mb-0">{{ $data['message'] }}</div>
            @endif

            {{-- Event log (collapsible) --}}
            <div class="mt-3">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse"
                    data-bs-target="#syncEvents">
                    {{ empty($eventsShown) ? 'Show events' : 'Show recent events' }}
                </button>

                <div id="syncEvents" class="collapse mt-2">
                    @if (empty($eventsShown))
                        <div class="text-muted small">No events yet.</div>
                    @else
                        <ul class="list-unstyled mb-0">
                            @foreach ($eventsShown as $ev)
                                @php $changes = $ev['changes'] ?? []; @endphp
                                <li class="border rounded p-2 mb-2">
                                    <div class="d-flex justify-content-between">
                                        <strong>{{ ucfirst($ev['phase'] ?? '—') }}</strong>
                                        <span class="text-muted">{{ $ev['ts'] ?? '' }}</span>
                                    </div>

                                    @if (empty($changes))
                                        <div class="small text-muted">No visible changes.</div>
                                    @else
                                        <ul class="small mt-2 mb-0">
                                            @foreach ($changes as $key => $chg)
                                                <li>
                                                    <code>{{ $key }}</code>:
                                                    <span class="text-muted">
                                                        {{ $chg['from'] === null || $chg['from'] === '' ? '—' : (is_bool($chg['from']) ? ($chg['from'] ? 'true' : 'false') : $chg['from']) }}
                                                        →
                                                        {{ $chg['to'] === null || $chg['to'] === '' ? '—' : (is_bool($chg['to']) ? ($chg['to'] ? 'true' : 'false') : $chg['to']) }}
                                                    </span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </li>
                            @endforeach
                        </ul>

                        @if (count($events ?? []) > $maxEvents)
                            <div class="text-muted small mt-1">Showing last {{ $maxEvents }} events.</div>
                        @endif
                    @endif
                </div>
            </div>

            {{-- Debug payloads (collapsible) --}}
            <details class="mt-3">
                <summary class="small text-muted">Debug: current payload</summary>
                <pre class="bg-light p-2 border rounded mt-2">{{ json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
            </details>
            <details class="mt-2">
                <summary class="small text-muted">Debug: events payload</summary>
                <pre class="bg-light p-2 border rounded mt-2">{{ json_encode($events ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
            </details>
        @endif
    </div>
</div>
