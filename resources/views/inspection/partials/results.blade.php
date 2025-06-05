{{-- resources/views/inspection/partials/results.blade.php --}}
@props(['judgement', 'quantity']) {{-- each is a collection --}}

@php
    $num = fn($n, $dec = 0) => number_format($n, $dec);
@endphp

<div class="row g-3 p-3">

    {{-- ═════════ Judgement Summary ════════════════════════════════ --}}
    <div class="col-md-6">
        <h6 class="mb-2"><i class="bi bi-clipboard-check me-1"></i> Judgement Summary</h6>

        <table class="table table-sm table-striped table-hover text-center mb-0">
            <thead class="table-light">
                <tr>
                    <th>Pass</th>
                    <th>Reject</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($judgement as $j)
                    @php $rowDanger = $j->reject_quantity > 0 ? 'table-danger' : ''; @endphp
                    <tr class="{{ $rowDanger }}">
                        <td>{{ $num($j->pass_quantity) }}</td>
                        <td>{{ $num($j->reject_quantity) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="text-center text-muted py-2">
                            <i class="bi bi-info-circle me-1"></i> No judgement data
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ═════════ Output Quantity ══════════════════════════════════ --}}
    <div class="col-md-6">
        <h6 class="mb-2"><i class="bi bi-graph-up me-1"></i> Output Quantity</h6>

        <table class="table table-sm table-bordered table-hover text-center mb-0">
            <thead class="table-light">
                <tr>
                    <th>Output</th>
                    <th>Pass</th>
                    <th>Reject</th>
                    <th>Sampling</th>
                    <th>Reject&nbsp;%</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($quantity as $q)
                    @php
                        $rate = (float) $q->reject_rate;
                        $rowClass = $rate > 0 ? 'table-danger' : '';
                    @endphp
                    <tr class="{{ $rowClass }}">
                        <td>{{ $num($q->output_quantity) }}</td>
                        <td>{{ $num($q->pass_quantity) }}</td>
                        <td>{{ $num($q->reject_quantity) }}</td>
                        <td>{{ $num($q->sampling_quantity) }}</td>
                        <td>
                            <span class="badge {{ $rate > 0 ? 'text-bg-danger' : 'text-bg-success' }}">
                                {{ $num($rate, 2) }} %
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-2">
                            <i class="bi bi-info-circle me-1"></i> No quantity data
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
