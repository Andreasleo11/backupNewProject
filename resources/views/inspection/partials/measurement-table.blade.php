{{-- resources/views/inspection/partials/measurement-table.blade.php --}}
@props(['rows'])

<div class="table-responsive">
    <table class="table table-bordered table-sm align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th>Part</th>
                <th>Lower</th>
                <th>Upper</th>
                <th>UOM</th>
                <th>Judgement</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ $row->part }}</td>
                    <td>{{ $row->lower_limit }}</td>
                    <td>{{ $row->upper_limit }}</td>
                    <td>{{ $row->limit_uom }}</td>
                    <td>
                        <span class="badge text-bg-{{ strtolower($row->judgement) === 'ok' ? 'success' : 'danger' }}">
                            {{ $row->judgement }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">—</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
