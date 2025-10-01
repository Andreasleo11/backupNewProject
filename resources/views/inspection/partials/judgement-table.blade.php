{{-- resources/views/inspection/partials/results.blade.php --}}
@props(['judgement']) {{-- each is a collection --}}

@php
    $num = fn($n, $dec = 0) => number_format($n, $dec);
@endphp

<div class="row g-3 p-3">

    {{-- ═════════ Judgement Summary ════════════════════════════════ --}}
    <div class="col">
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

</div>
