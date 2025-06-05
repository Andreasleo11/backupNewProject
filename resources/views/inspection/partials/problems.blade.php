{{-- resources/views/inspection/partials/problems.blade.php --}}
@props(['rows'])

@php
    /* colour-chip helper */
    $chip = function ($type) {
        $t = strtolower($type);
        return match (true) {
            str_contains($t, 'no problem') => '<span class="badge text-bg-success">No&nbsp;Problem</span>',
            str_contains($t, 'quality') => '<span class="badge text-bg-danger">Quality</span>',
            str_contains($t, 'machine') => '<span class="badge text-bg-warning text-dark">Machine</span>',
            default => '<span class="badge text-bg-secondary">' . e($type) . '</span>',
        };
    };
@endphp
<div class="p-2">
    <div class="table-responsive">
        <table class="table table-sm table-striped table-borderless table-hover align-middle mb-0 text-center">
            <thead class="table-light">
                <tr>
                    <th style="width:15%">Time</th>
                    <th style="width:25%">Type</th>
                    <th style="width:20%">Cycle&nbsp;Time&nbsp;(s)</th>
                    <th>Remark</th>
                </tr>
            </thead>

            <tbody class="small">
                @forelse ($rows as $pb)
                    @php
                        $isOk = strtolower($pb->type) === 'no problem';
                        $rowCls = $isOk ? '' : 'table-warning';
                    @endphp
                    <tr class="{{ $rowCls }}">
                        <td>{{ $pb->time }}</td>
                        <td>{!! $chip($pb->type) !!}</td>
                        <td>{{ number_format($pb->cycle_time) }}</td>
                        <td>{{ $pb->remark ?: '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-3">
                            <i class="bi bi-info-circle me-1"></i> No problem records
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
