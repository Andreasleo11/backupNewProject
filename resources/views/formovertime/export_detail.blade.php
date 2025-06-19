<table>
    <thead>
        <tr>
            <th>NIK</th>
            <th>Overtime Date</th>
            <th>Job Desc</th>
            <th>Start Date</th>
            <th>Start Time</th>
            <th>End Date</th>
            <th>End Time</th>
            <th>Break Time</th>
            <th>Remark</th>
        </tr>
    </thead>
    <tbody>
        @foreach($details as $row)
        <tr>
            <td>{{ $row->NIK }}</td>
            <td>{{ \Carbon\Carbon::parse($row->start_date)->format('d/m/Y') }}</td>
            <td>{{ $row->job_desc }}</td>
            <td>{{ $row->start_date }}</td>
            <td>{{ $row->start_time }}</td>
            <td>{{ $row->end_date }}</td>
            <td>{{ $row->end_time }}</td>
            <td>{{ $row->break }}</td>
            <td>{{ $row->remarks }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
