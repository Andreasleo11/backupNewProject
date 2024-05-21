<!DOCTYPE html>
<html>
<head>
    <title>Barcodes</title>
</head>

<style>
        body {
            font-family: Arial, sans-serif;
        }
        .form-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 20px;
        }
        #barcodeForm {
            display: flex;
            flex-direction: column;
            gap: 20px;
            align-items: flex-start;
            width: 100%;
            max-width: 1200px;
        }
        .form-group-container {
            display: flex;
            justify-content: space-between;
            width: 100%;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            margin: 5px;
        }
        .form-group label {
            margin-bottom: 5px;
        }
        .form-group input {
            padding: 5px;
            font-size: 16px;
        }
    </style>
<body>
    <h1>Generated Barcodes</h1>
    @foreach ($barcodes as $barcode)
        <div>
            <h2>Barcode {{ $barcode['incrementNumber'] }}</h2>
            <br>
            <!-- <p>Barcode Image URL: <a href="{{ $barcode['barcodeUrl'] }}">{{ $barcode['barcodeUrl'] }}</a></p> -->
            <img src="{{ $barcode['barcodeUrl'] }}" alt="Barcode {{ $barcode['incrementNumber'] }}">
            <br>
           
        </div>
        <hr>
    @endforeach


    <h1>Generated Barcodes</h1>
    <div class="form-container">
        <form id="barcodeForm">
            <div class="form-group-container">
                <div class="form-group">
                    <label for="spkCode1">SpkCode:</label>
                    <input type="text" id="spkCode1" name="spkCode1" class="barcode-input">
                </div>
                <div class="form-group">
                    <label for="quantity1">Quantity:</label>
                    <input type="text" id="quantity1" name="quantity1" class="barcode-input">
                </div>
                <div class="form-group">
                    <label for="warehouseType1">WarehouseType:</label>
                    <input type="text" id="warehouseType1" name="warehouseType1" class="barcode-input">
                </div>
                <div class="form-group">
                    <label for="indicator1">Indicator:</label>
                    <input type="text" id="indicator1" name="indicator1" class="barcode-input">
                </div>
            </div>

        </form>
    </div>
    
</body>
</body>
    
<script>
        let formCounter = 1;

        document.getElementById('indicator'+formCounter).addEventListener('focus', addNewRow);
       
        function addNewRow() {
            const spkCode = document.getElementById('spkCode' + formCounter).value;
            const quantity = document.getElementById('quantity' + formCounter).value;
            const warehouseType = document.getElementById('warehouseType' + formCounter).value;
            const indicator = document.getElementById('indicator' + formCounter).value;

            if (spkCode && quantity && warehouseType) {
                formCounter++;

                const newRow = document.createElement('div');
                newRow.className = 'form-group-container';
                newRow.id = 'row' + formCounter;
                newRow.innerHTML = `
                    <div class="form-group">
                        <label for="spkCode${formCounter}">SpkCode:</label>
                        <input type="text" id="spkCode${formCounter}" name="spkCode${formCounter}" class="barcode-input">
                    </div>
                    <div class="form-group">
                        <label for="quantity${formCounter}">Quantity:</label>
                        <input type="text" id="quantity${formCounter}" name="quantity${formCounter}" class="barcode-input">
                    </div>
                    <div class="form-group">
                        <label for="warehouseType${formCounter}">WarehouseType:</label>
                        <input type="text" id="warehouseType${formCounter}" name="warehouseType${formCounter}" class="barcode-input">
                    </div>
                    <div class="form-group">
                        <label for="indicator${formCounter}">Indicator:</label>
                        <input type="text" id="indicator${formCounter}" name="indicator${formCounter}" class="barcode-input">
                    </div>
                `;

                document.getElementById('barcodeForm').appendChild(newRow);

                // Add event listener to the new indicator input field
                document.getElementById('indicator' + formCounter).addEventListener('focus', addNewRow);

                // Set focus to the new spkCode input field after a short delay to ensure it is rendered
                setTimeout(() => {
                    document.getElementById('spkCode' + formCounter).focus();
                }, 1000); // 1-second delay
            }
        }
    </script>
</html>