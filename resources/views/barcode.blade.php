<!DOCTYPE html>
<html>
<head>
    <title>Barcodes</title>
</head>
<body>
    <h1>Generated Barcodes</h1>
    @foreach ($barcodes as $barcode)
        <div>
            <h2>Barcode {{ $barcode['incrementNumber'] }}</h2>
            <div>{!! $barcode['barcodeHtml'] !!}</div>
            <p>Barcode Image URL: <a href="{{ $barcode['barcodeUrl'] }}">{{ $barcode['barcodeUrl'] }}</a></p>
            <img src="{{ $barcode['barcodeUrl'] }}" alt="Barcode {{ $barcode['incrementNumber'] }}">
            <br>
           
        </div>
        <hr>
    @endforeach
</body>
</html>