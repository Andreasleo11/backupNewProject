<table>
    <thead>
        <tr>
            <th>PO Number</th>
            <th>Vendor Name</th>
            <th>PO Date</th>
            <th>Invoice Date</th>
            <th>Total</th>
            <th>Upload Date</th>
            <th>Uploaded By</th>
            <th>Approved Date</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $datum)
            <tr>
                <td>{{ $datum->po_number }}</td>
                <td>{{ $datum->vendor_name }}</td>
                <td>{{ $datum->invoice_date }}</td>
                <td>{{ $datum->tanggal_pembayaran }}</td>
                <td>{{ $datum->total }}</td>
                <td>{{ $datum->created_at }}</td>
                <td>{{ $datum->user->name }}</td>
                <td>{{ $datum->approved_date }}</td>
                <td>{{ $datum->status }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
