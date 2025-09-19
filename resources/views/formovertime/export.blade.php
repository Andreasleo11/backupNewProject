<table>
  <thead>
    <tr>
      <th>NIK</th>
      <th>Nama</th>
      <th>Tanggal Awal</th>
      <th>Tanggal Akhir</th>
      <th>Total Jam Lembur</th>
    </tr>
  </thead>
  <tbody>
    @foreach ($summary as $row)
      <tr>
        <td>{{ $row->NIK }}</td>
        <td>{{ $row->nama }}</td>
        <td>{{ \Carbon\Carbon::parse($row->start_date)->format('d-m-Y') }}</td>
        <td>{{ \Carbon\Carbon::parse($row->end_date)->format('d-m-Y') }}</td>
        <td>{{ number_format($row->total_ot, 2) }}</td>
      </tr>
    @endforeach
  </tbody>
</table>
