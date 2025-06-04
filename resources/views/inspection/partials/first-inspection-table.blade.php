{{-- resources/views/inspection/partials/first-inspection-table.blade.php --}}
@props(['rows'])

<div class="table-responsive">
    <table class="table table-bordered table-sm align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th>Appearance</th>
                <th>Weight</th>
                <th>Fitting Test</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ $row->appearance }}</td>
                    <td>{{ $row->weight }} {{ $row->weight_uom }}</td>
                    <td>{{ $row->fitting_test }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center text-muted">—</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
