{{-- resources/views/inspection/partials/second-inspection.blade.php --}}
@props(['second']) {{-- $second is a collection of SecondInspection models --}}

{{-- main Second Inspection table --}}
<div class="table-responsive mb-3">
    <table class="table table-bordered table-sm align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th>Document&nbsp;No.</th>
                <th>Lot Size Qty</th>
            </tr>
        </thead>
        <tbody>
            @forelse($second as $row)
                <tr>
                    <td>{{ $row->document_number }}</td>
                    <td>{{ $row->lot_size_quantity }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="2" class="text-center text-muted">—</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Sampling Data -------------------------------------------------------- --}}
<h6 class="ms-3">Sampling Data</h6>
<div class="table-responsive mb-3">
    <table class="table table-bordered table-sm align-middle mb-0 ms-3">
        <thead class="table-light">
            <tr>
                <th>Quantity</th>
                <th>Box Label</th>
                <th>Appearance</th>
            </tr>
        </thead>
        <tbody>
            @forelse($second->flatMap->samplingData as $s)
                <tr>
                    <td>{{ $s->quantity }}</td>
                    <td>{{ $s->box_label }}</td>
                    <td>{{ $s->appearance }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center text-muted">—</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Packaging Data ------------------------------------------------------- --}}
<h6 class="ms-3">Packaging Data</h6>
<div class="table-responsive">
    <table class="table table-bordered table-sm align-middle mb-0 ms-3">
        <thead class="table-light">
            <tr>
                <th>Quantity</th>
                <th>Box Label</th>
                <th>Judgement</th>
            </tr>
        </thead>
        <tbody>
            @forelse($second->flatMap->packagingData as $p)
                <tr>
                    <td>{{ $p->quantity }}</td>
                    <td>{{ $p->box_label }}</td>
                    <td>
                        <span class="badge text-bg-{{ strtolower($p->judgement) === 'ok' ? 'success' : 'danger' }}">
                            {{ $p->judgement }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center text-muted">—</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
