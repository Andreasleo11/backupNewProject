<div class="container py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h5 mb-0">{{ $department->name }} — Compliance</h1>
        <div class="w-50">
            <div class="progress" role="progressbar" aria-valuenow="{{ $percent }}" aria-valuemin="0"
                aria-valuemax="100">
                <div class="progress-bar {{ $percent == 100 ? 'bg-success' : '' }}" style="width: {{ $percent }}%">
                    {{ $percent }}%</div>
            </div>
        </div>
    </div>


    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Requirement</th>
                    <th>Status</th>
                    <th>Valid/Min</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $r)
                    <tr>
                        <td class="text-muted small">{{ $r['code'] }}</td>
                        <td>{{ $r['name'] }}</td>
                        <td>
                            @if ($r['status'] === 'OK')
                                <span class="badge text-bg-success">OK</span>
                            @else
                                <span class="badge text-bg-danger">Missing</span>
                            @endif
                            @if ($r['requires_approval'])
                                <span class="badge text-bg-secondary ms-1">needs approval</span>
                            @endif
                        </td>
                        <td>{{ $r['valid_count'] }} / {{ $r['min'] }}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary"
                                wire:click="openUpload({{ $r['id'] }})">Upload</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">No assigned requirements.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @livewire('requirements.upload', ['department' => $department], key('uploader-' . $department->id))
</div>
