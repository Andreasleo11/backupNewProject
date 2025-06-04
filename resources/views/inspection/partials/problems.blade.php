{{-- resources/views/inspection/partials/problems.blade.php --}}
@props(['rows'])

<div class="table-responsive">
    <table class="table table-bordered table-sm align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th>Time</th>
                <th>Type</th>
                <th>Cycle&nbsp;Time</th>
                <th>Remark</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $pb)
                <tr>
                    <td>{{ $pb->time }}</td>
                    <td>{{ $pb->type }}</td>
                    <td>{{ $pb->cycle_time }}</td>
                    <td>{{ $pb->remark }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center text-muted">—</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
