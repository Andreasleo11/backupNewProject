@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row align-items-center">
            <div class="col">
                <h1>Purchase Orders</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('po.index') }}">Purchase Orders</a></li>
                        <li class="breadcrumb-item active">List</li>
                    </ol>
                </nav>
            </div>
            <div class="col text-end">
                <a href="{{ route('po.create') }}" class="btn btn-primary">+ Create</a>
            </div>
        </div>

        <div class="mt-2">
            <div class="row">
                <div class="col">
                    @if (auth()->user()->department->name === 'DIRECTOR')
                        <button id="sign-selected-btn" class="btn btn-outline-success my-1">Sign Selected</button>
                        <button id="reject-selected-btn" class="btn btn-outline-danger my-1">Reject Selected</button>
                    @endif
                </div>
                <div class="col-md-3 text-end">
                    <input type="text" id="search-input" class="form-control mb-3" placeholder="Search...">
                </div>
            </div>
            <div class="table-responsive mt-3">
                <table class="table table-hover">
                    <thead>
                        <tr class="text-center">
                            @if (auth()->user()->department->name === 'DIRECTOR')
                                <th><input type="checkbox" id="select-all"></th>
                            @endif
                            <th>PO Number</th>
                            <th>Status</th>
                            <th>Upload Date</th>
                            <th>Uploaded By</th>
                            <th>Approved Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data as $datum)
                            <tr class="text-center">
                                @if (auth()->user()->department->name === 'DIRECTOR')
                                    <td><input type="checkbox" name="po-select[]" value="{{ $datum->id }}"></td>
                                @endif
                                <td>{{ $datum->po_number }}</td>
                                <td>@include('partials.po-status', ['po' => $datum])</td>
                                <td>{{ \Carbon\Carbon::parse($datum->created_at)->setTimezone('Asia/Jakarta')->format('d-m-Y (H:i)') }}
                                </td>
                                <td>{{ $datum->user->name }}</td>
                                <td>{{ $datum->approved_date? \Carbon\Carbon::parse($datum->approved_date)->setTimezone('Asia/Jakarta')->format('d-m-Y (H:i)'): '-' }}
                                </td>
                                <td>
                                    <a href="{{ route('po.view', $datum->id) }}" class="btn btn-outline-primary">View</a>
                                    @if (auth()->user()->role->name === 'SUPERADMIN')
                                        @include('partials.delete-confirmation-modal', [
                                            'id' => $datum->id,
                                            'route' => 'po.destroy',
                                            'title' => 'Delete PO confirmation',
                                            'body' => "Are you sure want to delete this PO with id <strong>$datum->id</strong>?",
                                        ])

                                        <button class="btn btn-outline-danger my-1" data-bs-toggle="modal"
                                            data-bs-target="#delete-confirmation-modal-{{ $datum->id }}"><i
                                                class='bx bx-trash-alt'></i> <span
                                                class="d-none d-sm-inline">Delete</span></button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="100" class="text-center">
                                    No data
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>


    <script>
        // Search function
        document.getElementById('search-input').addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        // JavaScript for select all functionality
        document.getElementById('select-all').addEventListener('change', function() {
            document.querySelectorAll('input[name="po-select[]"]').forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        // Sign All functionality
        document.getElementById('sign-selected-btn').addEventListener('click', function() {
            const selectedIds = Array.from(document.querySelectorAll('input[name="po-select[]"]:checked'))
                .map(checkbox => checkbox.value);

            if (selectedIds.length > 0) {
                fetch("{{ route('po.signAll') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            ids: selectedIds
                        })
                    }).then(response => response.json())
                    .then(data => alert(data.message));
            } else {
                alert('Please select at least one PO to sign.');
            }
        });

        // Reject All functionality
        document.getElementById('reject-selected-btn').addEventListener('click', function() {
            const selectedIds = Array.from(document.querySelectorAll('input[name="po-select[]"]:checked'))
                .map(checkbox => checkbox.value);

            if (selectedIds.length > 0) {
                const reason = prompt("Enter rejection reason");
                if (reason) {
                    fetch("{{ route('po.rejectAll') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                ids: selectedIds,
                                reason: reason
                            })
                        }).then(response => response.json())
                        .then(data => alert(data.message));
                }
            } else {
                alert('Please select at least one PO to reject.');
            }
        });
    </script>
@endsection
