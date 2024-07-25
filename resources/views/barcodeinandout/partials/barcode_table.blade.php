@foreach ($result as $item)
    <h2>Date Scan: {{ $item['dateScan'] }}</h2>
    <p>No Dokumen: {{ $item['noDokumen'] }}</p>
    <p>Tipe Barcode: {{ strtoupper($item['tipeBarcode']) }}</p>
    <p>Location: {{ strtoupper($item['location']) }}</p>    
    <table>
        <thead>
            <tr>
                <th>Part No</th>
                <th>Quantity</th>
                <th>Label</th>
                <th>Position</th>
                <th>Scan Time</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($item[$item['noDokumen']] as $detail)
                <tr>
                    <td>{{ $detail['partNo'] }}</td>
                    <td>{{ $detail['quantity']}}</td>
                    <td>{{ $detail['label'] }}</td>
                    <td>{{ $detail['position'] }}</td>
                    <td>{{ $detail['scantime'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <hr>
@endforeach
