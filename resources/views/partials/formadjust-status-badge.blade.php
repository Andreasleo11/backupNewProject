@if ($report->autograph_7 !== null)
    <span class="badge text-bg-success px-3 py-2 fs-6">APPROVED</span>
@elseif(
    $report->autograph_7 === null &&
        $report->autograph_6 !== null &&
        $report->autograph_5 !== null &&
        $report->autograph_4 !== null &&
        $report->autograph_3 !== null &&
        $report->autograph_2 !== null &&
        $report->autograph_1 !== null)
    <span class="badge text-bg-warning px-3 py-2 fs-6">Waiting For Director</span>
@else
    <span class="badge text-bg-warning px-3 py-2 fs-6">Waiting Signature</span>
@endif
