<a href="{{ route('qaqc.report.detail', $report->id) }}" class="btn btn-secondary my-1 me-1 ">
    <i class='bx bx-info-circle'></i> <span class="d-none d-sm-inline ">Detail</span>
</a>

{{-- DEV ONLY --}}
{{-- <a href="{{ route('qaqc.report.preview', $report->id) }}"
    class="btn btn-primary">preview</a> --}}

@php
    $hoursDifference = Date::now()->diffInHours($report->rejected_at);
@endphp

<form class="d-none" action="{{ route('qaqc.report.rejectAuto', $report->id) }}" method="get"
    id="form-reject-report-{{ $report->id }}"><input type="hidden" name="description"
        value="Automatically rejected after 24 hours"></form>

<script>
    @if ($hoursDifference > 24 && $report->is_approve === 2 && $report->is_locked == false)
        document.getElementById('form-reject-report-{{ $report->id }}').submit();
    @endif
</script>

<a href="{{ route('qaqc.report.edit', $report->id) }}"
    class="btn btn-primary my-1 me-1 @if (
        $report->created_by !== Auth::user()->name ||
            $hoursDifference > 24 ||
            $report->is_approve == 1 ||
            $report->is_locked) d-none @endif">
    <i class='bx bx-edit'></i> <span class="d-none d-sm-inline">Edit</span>
</a>


@include('partials.delete-report-modal')
<button class="btn btn-danger my-1 me-1 @if (
    $report->created_by !== Auth::user()->name ||
        $hoursDifference > 24 ||
        $report->autograph_3 ||
        $report->is_approve == 1 ||
        $report->is_locked) d-none @endif" data-bs-toggle="modal"
    data-bs-target="#delete-report-modal{{ $report->id }}">
    <i class='bx bx-trash-alt'></i> <span class="d-none d-sm-inline">Delete</span>
</button>

@include('partials.lock-report-confirmation-modal')

<div class="btn-group" role="group">

    <button type="button" class="btn text-success border border-success dropdown-toggle" data-bs-toggle="dropdown"
        aria-expanded="false">
        More
    </button>

    <ul class="dropdown-menu">
        <li>
            <a href="{{ route('qaqc.report.download', $report->id) }}" class="btn btn-success my-1 dropdown-item">
                <i class='bx bxs-file-pdf'></i> <span class="d-none d-sm-inline">Export PDF</span>
            </a>
        </li>
        <li>
            <a class="btn btn-success dropdown-item @if ($report->is_locked || $report->is_approve) disabled @endif"
                data-bs-toggle="modal" data-bs-target="#lock-report-modal-confirmation-{{ $report->id }}">
                <i class='bx bxs-lock'></i>
                Lock
            </a>
        </li>
    </ul>
</div>
