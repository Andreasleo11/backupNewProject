{{-- resources/views/inspection/partials/results.blade.php --}}
@props(['judgement', 'quantity']) {{-- each is a collection --}}

<div class="row g-3">
    {{-- Judgement Summary --}}
    <div class="col-md-6">
        <h6 class="mb-2">Judgement Summary</h6>
        <table class="table table-bordered table-sm mb-0">
            <thead class="table-light">
                <tr>
                    <th>Pass</th>
                    <th>Reject</th>
                </tr>
            </thead>
            <tbody>
                @forelse($judgement as $j)
                    <tr>
                        <td>{{ $j->pass_quantity }}</td>
                        <td>{{ $j->reject_quantity }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="text-center text-muted">—</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Quantity Summary --}}
    <div class="col-md-6">
        <h6 class="mb-2">Output Quantity</h6>
        <table class="table table-bordered table-sm mb-0">
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
                @forelse($quantity as $q)
                    <tr>
                        <td>{{ $q->output_quantity }}</td>
                        <td>{{ $q->pass_quantity }}</td>
                        <td>{{ $q->reject_quantity }}</td>
                        <td>{{ $q->sampling_quantity }}</td>
                        <td>{{ number_format($q->reject_rate, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">—</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
