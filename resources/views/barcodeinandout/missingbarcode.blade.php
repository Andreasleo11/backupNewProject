<!DOCTYPE html>
<html>
<head>
    <title>Barcodes</title>
</head>

<style>
        body {
            font-family: Arial, sans-serif;
        }
        .barcode-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            page-break-inside: avoid; /* Prevent page breaks within a single barcode container */
        }
        .barcode-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 20px;
            height: 300px; /* Adjust this value as needed */
            box-sizing: border-box;
        }
        .barcode-item h2 {
            margin: 5px 0;
            font-size: 16px;
        }
        .barcode-item img {
            max-width: 100%;
            height: auto;
            margin-bottom: 10px;
        }
        .separator {
            width: 100%;
            border-top: 1px solid #000;
            margin: 10px 0;
        }
        .vertical-separator {
            border-left: 1px solid #000;
            height: 100%;
            margin: 0 10px; /* Adjust spacing */
        }
        .info {
            display: flex;
            align-items: center;
            padding-top: 10px; /* Adjust spacing */
        }
        .info .text {
            flex: 1;
            padding: 0 10px; /* Adjust spacing */
        }
        .info .text p {
            margin: 5px 0;
            font-size: 14px;
        }
        .big-number {
            font-size: 24px;
            font-weight: bold;
            margin-left: 20px;
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
        @media print {
            body {
                margin: 0;
                font-size: 10pt;
            }
            .barcode-container {
                display: grid;
                grid-template-columns: repeat(2, 1fr); /* 2 columns per row */
                gap: 10px;
                page-break-inside: avoid;
            }
            .barcode-item {
                border: 1px solid #000;
                padding: 5px;
                margin-bottom: 5px;
                height: 190px; /* Adjusted height for print */
                box-sizing: border-box;
                page-break-inside: avoid;
            }
            .barcode-item h2 {
                font-size: 12px; /* Adjust font size for print */
            }
            .barcode-item img {
                height: 60px; /* Adjust image size for print */
            }
            .info .text p {
                font-size: 10px; /* Adjust text size for print */
            }
            .big-number {
                font-size: 16px; /* Adjust font size for print */
            }
            .form-container {
                display: none; /* Hide the form when printing */
            }
        }
</style>
<body>

    <div class="barcode-container">
        @foreach ($barcodes as $barcode)
            <div class="barcode-item">
                <h2>SCAN HERE</h2>
                <img src="{{ $barcode['barcodeUrl'] }}">
                <div class="separator"></div>
                <div class="info">
                    <div class="text">
                        <p>Part No: {{ $barcode['partno'] }}</p>
                        <p>Description: {{ $barcode['partname']}}</p>
                    </div>
                    <div class="vertical-separator"></div>
                    <div class="big-number">Label <br>{{ $barcode['missingNumber']}}</div>
                </div>
                
            </div>
        @endforeach
    </div>


   
    <!-- <div class="form-container">
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
    </div> -->
    
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