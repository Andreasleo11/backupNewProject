@extends('layouts.guest')

@section('content')
    <div class="container py-4">

        {{-- Page title -------------------------------------------------------- --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0">Inspection Reports</h3>

            {{-- Quick search --}}
            <form class="d-flex" method="get">
                <input name="s" value="{{ $search }}" class="form-control me-2" type="search"
                    placeholder="Search document or customer">
                <button class="btn btn-outline-primary" type="submit">Search</button>
            </form>
        </div>

        {{-- Reports table ----------------------------------------------------- --}}
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Document&nbsp;No.</th>
                        <th>Date</th>
                        <th>Shift</th>
                        <th>Customer</th>
                        <th>Part&nbsp;Number</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $idx => $r)
                        <tr>
                            <td>{{ $reports->firstItem() + $idx }}</td>
                            <td>{{ $r->document_number }}</td>
                            <td>{{ \Carbon\Carbon::parse($r->inspection_date)->format('Y-m-d') }}</td>
                            <td>{{ $r->shift }}</td>
                            <td>{{ $r->customer }}</td>
                            <td>{{ $r->part_number }}</td>
                            <td class="text-end">
                                {{-- link to a show page if you have one --}}
                                <a href="{{ route('inspection-reports.show', $r) }}" class="btn btn-sm btn-primary">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                No inspection reports found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination links -------------------------------------------------- --}}
        <div class="mt-3">
            {{ $reports->links() }}
        </div>

    </div>
@endsection
