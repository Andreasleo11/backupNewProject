<div class="container py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Requirements</h1>
        <a href="{{ route('requirements.create') }}" class="btn btn-primary">New Requirement</a>
    </div>


    <input type="text" class="form-control mb-3" placeholder="Search..." wire:model.live="search">


    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Min</th>
                    <th>Frequency</th>
                    <th>Approval</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $r)
                    <tr>
                        <td>{{ $r->code }}</td>
                        <td>{{ $r->name }}</td>
                        <td>{{ $r->min_count }}</td>
                        <td><span class="badge text-bg-secondary">{{ $r->frequency }}</span></td>
                        <td>{!! $r->requires_approval
                            ? '<span class="badge text-bg-info">Yes</span>'
                            : '<span class="badge text-bg-light">No</span>' !!}</td>
                        <td class="text-end"><a href="{{ route('requirements.edit', $r) }}"
                                class="btn btn-sm btn-outline-secondary">Edit</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>


    {{ $items->links() }}
</div>
