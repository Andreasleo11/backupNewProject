<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h5 mb-0">Defect Dictionary</h1>
        <a class="btn btn-primary" href="{{ route('admin.verification.defects.create') }}">New</a>
    </div>

    @if (session('ok'))
        <div class="alert alert-success">{{ session('ok') }}</div>
    @endif

    <div class="row g-2 mb-3">
        <div class="col-md-4">
            <input class="form-control" placeholder="Search code or name..." wire:model.live.debounce.300ms="search">
        </div>
        <div class="col-md-3">
            <select class="form-select" wire:model.live="active">
                <option value="">All</option>
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead class="table-light">
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Severity</th>
                    <th>Source</th>
                    <th class="text-end">Default Qty</th>
                    <th>Active</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $r)
                    <tr>
                        <td class="fw-semibold">{{ $r->code }}</td>
                        <td>{{ $r->name }}</td>
                        <td>@include('partials.severity-badge', [
                            'severity' => $r->default_severity?->value ?? $r->default_severity,
                        ])</td>
                        <td>@include('partials.source-chip', [
                            'source' => $r->default_source?->value ?? $r->default_source,
                        ])</td>
                        <td class="text-end">{{ rtrim(rtrim(number_format($r->default_quantity, 4, '.', ''), '0'), '.') }}
                        </td>
                        <td>
                            <span
                                class="badge text-bg-{{ $r->active ? 'success' : 'secondary' }}">{{ $r->active ? 'Yes' : 'No' }}</span>
                        </td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-primary"
                                href="{{ route('admin.verification.defects.edit', $r->id) }}">Edit</a>
                            <button class="btn btn-sm btn-outline-secondary"
                                wire:click="toggleActive({{ $r->id }})">Toggle</button>
                            <button class="btn btn-sm btn-outline-danger"
                                wire:click="delete({{ $r->id }})">Delete</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $rows->links() }}
</div>
