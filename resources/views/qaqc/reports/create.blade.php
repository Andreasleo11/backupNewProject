@extends('layouts.app')

@section('content')
    <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{route('qaqc.report.index')}}">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Create</li>
    </ol>
    </nav>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h2 class="mb-4">Verification Form</h2>

                <form action="/report/store" method="post">
                    @csrf

                    {{-- Rec'D Date --}}
                    <div class="mb-3">
                        <label for="Rec_Date" class="form-label">Rec'D Date:</label>
                        <input type="date" id="Rec_Date" name="Rec_Date" class="form-control" required>
                    </div>

                    {{-- Verify Date --}}
                    <div class="mb-3">
                        <label for="Verify_Date" class="form-label">Verify Date:</label>
                        <input type="date" id="Verify_Date" name="Verify_Date" class="form-control" required>
                    </div>

                    {{-- Customer --}}
                    <div class="mb-3">
                        <label for="Customer" class="form-label">Customer:</label>
                        <input type="text" id="Customer" name="Customer" class="form-control" required>
                    </div>

                    {{-- Invoice No --}}
                    <div class="mb-3">
                        <label for="Invoice_No" class="form-label">Invoice No:</label>
                        <input type="text" id="Invoice_No" name="Invoice_No" class="form-control" required>
                    </div>

                    {{-- Number of Parts --}}
                    <div class="mb-3">
                        <label for="num_of_parts" class="form-label">Number of Parts:</label>
                        <input type="number" id="num_of_parts" name="num_of_parts" class="form-control" min="1" required>
                    </div>

                    {{-- Part Details --}}
                    <div id="partDetails" class="mb-3 row">
                    </div>

                    <button type="submit" class="btn btn-primary mt-3">Submit</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('num_of_parts').addEventListener('input', updatePartDetails);
            // document.getElementById('customer_defect_details').addEventListener('input', updateDefectDetailFields);
            // document.getElementById('daijo_defect_details').addEventListener('input', updateDefectDetailFields);
        });

        function updatePartDetails() {
            const numParts = document.getElementById('num_of_parts').value;
            const partDetails = document.getElementById('partDetails');

            // Clear existing details
            partDetails.innerHTML = '';

            if(numParts<=10){
                // Create details for each part
                for (let i = 1; i <= numParts; i++) {
                    createPartDetails(i);
                }
            } else {
                location.reload();
                alert("Maksimal 10!");
            }
        }




        function createPartDetails(partNumber) {
            const partDetails = document.getElementById('partDetails');

            // Create container for part details
            const partDetailContainer = document.createElement('div');
            partDetailContainer.id = `partDetails${partNumber}`;
            partDetailContainer.classList.add('col-md-4', 'mb-3');

            // Add part number label
            const partNumberLabel = document.createElement('label');
            partNumberLabel.textContent = `Part ${partNumber} Details:`;
            partNumberLabel.classList.add('form-label', 'mb-2', 'text-primary', 'fw-bold');
            partDetailContainer.appendChild(partNumberLabel);

            // Add part name input
            createInput(partDetailContainer, `Part ${partNumber} Name:`, `part_names[${partNumber}]`, 'text');

            // Add details for the new part
            addPartDetails(partDetailContainer, partNumber);

            // Append to the main container
            partDetails.appendChild(partDetailContainer);

            // Show the details for the first part by default
            if (partNumber === 1) {
                showDetails(partNumber);
            }

                // Create container for the button
            const buttonContainer = document.createElement('div');
            buttonContainer.classList.add('mt-2', 'col-lg-4');

            // Add "Add Attributes" button to the button container
            createButton(buttonContainer, partNumber);

            // Append the button container to the main container
            partDetailContainer.appendChild(buttonContainer);
        }

        function createButton(container, partNumber) {

                const button = document.createElement('button');
                button.type = 'button';
                button.classList.add('btn', 'btn-secondary', 'mt-2');
                button.textContent = 'Add Attributes';
                button.addEventListener('click', function () {
                    addAttributesToPart(partNumber);
                });
                container.appendChild(button);
            }

        function addPartDetails(container, partNumber) {
            // Add detail inputs
            createInput(container, `Rec'D Quantity:`, `rec_quantity[${partNumber}]`, 'number');
            createInput(container, `Verify Quantity:`, `verify_quantity[${partNumber}]`, 'number');
            createInput(container, `Production Date:`, `prod_date[${partNumber}]`, 'date');
            createInput(container, `Shift:`, `shift[${partNumber}]`, 'number');
            createInput(container, `Can Use:`, `can_use[${partNumber}]`, 'number');
            createInput(container, `Cant Use:`, `cant_use[${partNumber}]`, 'number');
            createInput(container, `Customer Defect Detail :`, `customer_defect_detail[${partNumber}][]`, 'text');
            createInput(container, `Daijo Defect Detail :`, `daijo_defect_detail[${partNumber}][]`, 'text');
            createInputDrop(container, `Remark:`, `remark[${partNumber}][]`,'text');

        }



        function createInputDrop(container, labelText, name, type) {
        const div = document.createElement('div');
        div.classList.add('mb-3');

        const label = document.createElement('label');
        label.textContent = labelText;
        label.classList.add('form-label');

        const select = document.createElement('select');
        select.name = name;
        select.classList.add('form-select');

        // Create and add specific options
        const options = ["bisarepair", "tidakbisarepair", "other"];
        options.forEach(optionValue => {
            const option = document.createElement('option');
            option.value = option.textContent = optionValue;
            select.appendChild(option);
        });

        // Create input for explanation
        const explanationInput = document.createElement('input');
        explanationInput.type = 'text';
        explanationInput.name = `${name}_explanation`;
        explanationInput.classList.add('form-control', 'mt-2');
        explanationInput.placeholder = 'Please specify';
        explanationInput.style.display = 'none'; // Initially hide the input

            // Append elements to the container
            div.appendChild(label);
            div.appendChild(select);
            container.appendChild(div);

        // Add event listener to show/hide explanation input based on dropdown selection
            select.addEventListener('change', function () {
                if (this.value === 'other') {
                    // If 'other' is selected, create and show the explanation input
                    if (!explanationInput.parentNode) {
                        // If the explanation input is not already added, add it
                        div.appendChild(explanationInput);

                    }
                    explanationInput.style.display = 'block';

                } else {
                    // If 'other' is not selected, remove the explanation input (if it exists)
                    if (explanationInput.parentNode) {
                        div.removeChild(explanationInput);
                    }
                }
            });

            console.log(`Created dropdown for ${name}`);
            return select;
        }


        function createInput(container, labelText, name, type) {
            const div = document.createElement('div');
            div.classList.add('mb-3');

            const label = document.createElement('label');
            label.textContent = labelText;
            label.classList.add('form-label');

            const input = document.createElement('input');
            input.type = type;
            input.name = name;
            input.classList.add('form-control');

            div.appendChild(label);
            div.appendChild(input);
            container.appendChild(div);
            console.log(`Created input for ${name}`);
            return input;
        }


        function addAttributesToPart(partNumber) {
        console.log(`Adding attributes to part ${partNumber}`);
        const partDetailContainer = document.getElementById(`partDetails${partNumber}`);

            if (partDetailContainer) {
                console.log(`Part container found for part ${partNumber}`);
                createInput(partDetailContainer, `Customer Defect Detail :`, `customer_defect_detail[${partNumber}][]`, 'text');
                createInput(partDetailContainer, `Daijo Defect Detail :`, `daijo_defect_detail[${partNumber}][]`, 'text');
                createInputDrop(partDetailContainer, `Remark:`, `remark[${partNumber}][]`, 'text');
            } else {
                console.error(`Part container not found for part ${partNumber}`);
            }
        }

        function showDetails(index) {
            const detailsSection = document.getElementById(`partDetails`).children[index - 1];
            detailsSection.style.display = 'block';
        }
    </script>
@endsection
