@extends('layouts.app')

@section('content')
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .barcode-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            page-break-inside: avoid;
            /* Prevent page breaks within a single barcode container */
        }

        .barcode-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 20px;
            height: 300px;
            /* Adjust this value as needed */
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
            margin: 0 10px;
            /* Adjust spacing */
        }

        .info {
            display: flex;
            align-items: center;
            padding-top: 10px;
            /* Adjust spacing */
        }

        .info .text {
            flex: 1;
            padding: 0 10px;
            /* Adjust spacing */
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
                grid-template-columns: repeat(2, 1fr);
                /* 2 columns per row */
                gap: 10px;
                page-break-inside: avoid;
            }

            .barcode-item {
                border: 1px solid #000;
                padding: 5px;
                margin-bottom: 5px;
                height: 190px;
                /* Adjusted height for print */
                box-sizing: border-box;
                page-break-inside: avoid;
            }

            .barcode-item h2 {
                font-size: 12px;
                /* Adjust font size for print */
            }

            .barcode-item img {
                height: 60px;
                /* Adjust image size for print */
            }

            .info .text p {
                font-size: 10px;
                /* Adjust text size for print */
            }

            .big-number {
                font-size: 16px;
                /* Adjust font size for print */
            }

            .form-container {
                display: none;
                /* Hide the form when printing */
            }
        }

        .submit-button {
            position: absolute;
            top: 120px;
            right: 60px;
            padding: 8px 20px;
            font-size: 18px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .submit-button:hover {
            background-color: #0056b3;
        }
    </style>

    <h2>No Dokumen: {{ $noDokumen }}</h2>
    <p>Tanggal Scan: {{ $tanggalScanFull }}</p>

    <h1 style="text-align: center; font-size: 2em;">{{ $HeaderScan }}</h1>

    <div class="form-container">
        <form id="barcodeForm" method="POST" action="{{ route('processbarcodeinandout') }}">
            @csrf
            <input type="hidden" name="noDokumen" value="{{ $noDokumen }}">
            <input type="hidden" name="tanggalScanFull" value="{{ $tanggalScanFull }}">
            <input type="hidden" name="position" value="{{ $position }}">

            <div class="form-group-container" id="row1">
                <div class="form-group">
                    <label for="partno1">Part No:</label>
                    <input type="text" id="partno1" name="partno1" class="barcode-input">
                </div>
                <div class="form-group">
                    <label for="quantity1">Quantity :</label>
                    <input type="text" id="quantity1" name="quantity1" class="barcode-input">
                </div>
                <div class="form-group">
                    <label for="warehouse1">Warehouse :</label>
                    <input type="text" id="warehouse1" name="warehouse1" class="barcode-input">
                </div>
                <div class="form-group">
                    <label for="label1">Label :</label>
                    <input type="text" id="label1" name="label1" class="barcode-input">
                </div>
                <div class="form-group">
                    <label for="scantime1">Scan Time:</label>
                    <input type="text" id="scantime1" name="scantime1" class="barcode-input" readonly>
                </div>

                <button type="button" class="delete-row-button" onclick="deleteRow(1)">Delete</button>
            </div>

            <button type="submit" class="submit-button">Submit</button>
        </form>
    </div>

    <script>
        let formCounter = 1;

        function updateScanTime(fieldId) {
            const scanTimeField = document.getElementById('scantime' + fieldId);
            const now = new Date();
            const formattedTime = now.toLocaleString(); // Adjust formatting as needed
            scanTimeField.value = formattedTime;
        }

        function addEventListenersToRow(fieldId) {
            document.getElementById('partno' + fieldId).addEventListener('input', () => updateScanTime(
                fieldId));
            document.getElementById('label' + fieldId).addEventListener('input', () => updateScanTime(
                fieldId));
        }

        document.getElementById('label1').addEventListener('focus', addNewRow);

        function addNewRow() {
            const partno = document.getElementById('partno' + formCounter).value;
            const label = document.getElementById('label' + formCounter).value;

            if (partno) {
                formCounter++;

                const newRow = document.createElement('div');
                newRow.className = 'form-group-container';
                newRow.id = 'row' + formCounter;
                newRow.innerHTML = `
                <div class="form-group">
                    <label for="partno${formCounter}">Part No:</label>
                    <input type="text" id="partno${formCounter}" name="partno${formCounter}" class="barcode-input">
                </div>
                <div class="form-group">
                    <label for="quantity${formCounter}">Quantity :</label>
                    <input type="text" id="quantity${formCounter}" name="quantity${formCounter}" class="barcode-input">
                </div>
                <div class="form-group">
                    <label for="warehouse${formCounter}">Warehouse :</label>
                    <input type="text" id="warehouse${formCounter}" name="warehouse${formCounter}" class="barcode-input">
                </div>
                <div class="form-group">
                    <label for="label${formCounter}">Label:</label>
                    <input type="text" id="label${formCounter}" name="label${formCounter}" class="barcode-input">
                </div>
                <div class="form-group">
                    <label for="scantime${formCounter}">Scan Time:</label>
                    <input type="text" id="scantime${formCounter}" name="scantime${formCounter}" class="barcode-input" readonly>
                </div>
                 <button type="button" class="delete-row-button" onclick="deleteRow(${formCounter})">Delete</button>
            `;

                document.getElementById('barcodeForm').appendChild(newRow);

                // Add event listeners to the new input fields
                addEventListenersToRow(formCounter);

                // Add event listener to the new label input field to add new row on focus
                document.getElementById('label' + formCounter).addEventListener('focus', addNewRow);

                // Set focus to the new partno input field after a short delay to ensure it is rendered
                setTimeout(() => {
                    document.getElementById('partno' + formCounter).focus();
                }, 500); // 1-second delay
            }
        }

        // Initialize event listeners for the first row
        addEventListenersToRow(formCounter);

        function deleteRow(rowId) {
            const row = document.getElementById('row' + rowId);
            if (row) {
                row.remove();
                resetRowIds();
            }
        }

        function resetRowIds() {
            const rows = document.querySelectorAll('.form-group-container');
            formCounter = 0;

            rows.forEach((row, index) => {
                formCounter++;
                row.id = 'row' + formCounter;
                row.querySelector('.delete-row-button').setAttribute('onclick',
                    `deleteRow(${formCounter})`);
                row.querySelector('.form-group input[id^="partno"]').id = 'partno' + formCounter;
                row.querySelector('.form-group input[id^="partno"]').name = 'partno' + formCounter;
                row.querySelector('.form-group input[id^="quantity"]').id = 'quantity' + formCounter;
                row.querySelector('.form-group input[id^="quantity"]').name = 'quantity' + formCounter;
                row.querySelector('.form-group input[id^="warehouse"]').id = 'warehouse' + formCounter;
                row.querySelector('.form-group input[id^="warehouse"]').name = 'warehouse' + formCounter;
                row.querySelector('.form-group input[id^="label"]').id = 'label' + formCounter;
                row.querySelector('.form-group input[id^="label"]').name = 'label' + formCounter;
                row.querySelector('.form-group input[id^="scantime"]').id = 'scantime' + formCounter;
                row.querySelector('.form-group input[id^="scantime"]').name = 'scantime' + formCounter;

                // Reassign event listeners
                addEventListenersToRow(formCounter);
            });
        }

        document.getElementById('barcodeForm').addEventListener('submit', function(event) {
            const lastPartNoField = document.getElementById('partno' + formCounter);
            const lastLabelField = document.getElementById('label' + formCounter);

            if (!lastPartNoField.value && !lastLabelField.value) {
                lastPartNoField.parentElement.parentElement.remove();
            }
        });
    </script>
@endsection
