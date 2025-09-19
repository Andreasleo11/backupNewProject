<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>QR Code</title>
</head>

<body>

  <h1>QR Code for Hardware: {{ $data->hardware_name }}</h1>
  <p>Brand: {{ $data->brand }}</p>
  <p>Hardware Type: {{ $data->hardwareType->name }}</p>

  <!-- Display the QR Code -->
  <div>
    <img src="data:image/png;base64,{{ $qrcoded }}" alt="QR Code">
  </div>

</body>

</html>
