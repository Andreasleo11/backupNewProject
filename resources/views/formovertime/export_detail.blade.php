@php
  use Carbon\Carbon;

  $totalJam = 0;
@endphp
<table>
  <thead>
    <tr>
      <th>NIK</th>
      <th>ID Header Overtime</th>
      <th>ID ROW</th>
      <th>Overtime Date</th>
      <th>Job Desc</th>
      <th>Start Date</th>
      <th>Start Time</th>
      <th>End Date</th>
      <th>End Time</th>
      <th>Break Time</th>
      <th>Remark</th>
      <th>Total Jam</th>
      <th>Voucher </th>
      <th>Jpayroll In Date</th>
      <th>Jpayroll In Time</th>
      <th>Jpayroll Out Date</th>
      <th>Jpayroll Out Time</th>
      <th>Nett Overtime</th>
    </tr>
  </thead>
  <tbody>
    @foreach ($details as $row)
      @php
        $start = Carbon::parse($row->start_date . ' ' . $row->start_time);
        $end = Carbon::parse($row->end_date . ' ' . $row->end_time);

        $durationMinutes = $end->diffInMinutes($start) - (int) $row->break;
        $jamLembur = round($durationMinutes / 60, 2);
      @endphp
      <tr>
        <td>{{ $row->NIK }}</td>
        <td>{{ $row->header_id }}</td>
        <td>{{ $row->id }}</td>
        <td>{{ \Carbon\Carbon::parse($row->start_date)->format('d/m/Y') }}</td>
        <td>{{ $row->job_desc }}</td>
        <td>{{ $row->start_date }}</td>
        <td>{{ $row->start_time }}</td>
        <td>{{ $row->end_date }}</td>
        <td>{{ $row->end_time }}</td>
        <td>{{ $row->break }}</td>
        <td>{{ $row->remarks }}</td>
        <td><strong>{{ number_format($jamLembur, 2) }} </strong></td>
      </tr>
    @endforeach
  </tbody>
</table>
