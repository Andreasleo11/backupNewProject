@extends('layouts.app')

@section('content')

<section>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{route('staff.home')}}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{route('qaqc.report.index')}}">Reports</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit</li>
        </ol>
    </nav>
</section>

    <section>
        @if ($message = Session::get('success'))
            <div class="alert alert-success">
                <p>{{ $message }}</p>
            </div>
        @endif
    </section>

    <section aria-label="content">
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <h2 class="mb-4">Edit Verification Form</h2>

                    <form action="{{ route('qaqc.report.update', ['id' => $report->id]) }}" method="post">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="rec_date" class="form-label">Rec'D Date:</label>
                            <input type="date" id="rec_Date" name="rec_Date" class="form-control" value="{{ $report->rec_date }}" required>
                        </div>

                        {{-- Verify Date --}}
                        <div class="mb-3">
                            <label for="verify_date" class="form-label">Verify Date:</label>
                            <input type="date" id="verify_date" name="verify_date" class="form-control" value="{{ $report->verify_date }}" required>
                        </div>

                        {{-- Customer --}}
                        <div class="mb-3">
                            <label for="customer" class="form-label">Customer:</label>
                            <input type="text" id="customer" name="customer" class="form-control" value="{{ $report->customer }}" required>
                        </div>

                        {{-- Invoice No --}}
                        <div class="mb-3">
                            <label for="invoice_no" class="form-label">Invoice No:</label>
                            <input type="text" id="invoice_no" name="invoice_no" class="form-control" value="{{ $report->invoice_no }}" required>
                        </div>

                        {{-- Number of Parts --}}
                        <div class="mb-3">
                            <label for="num_of_parts" class="form-label">Number of Parts:</label>
                            <input type="number" id="num_of_parts" name="num_of_parts" class="form-control" min="1" value="{{ $report->num_of_parts }}" required>
                        </div>

                        {{-- Part Details --}}
                        <div id="partDetails" class="mb-3 row bg-primary">
                            <!-- Add other part details fields here, populating them with data from the $report variable -->
                        </div>

                        <button type="submit" class="btn btn-primary mt-3">Update</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

@endsection

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('num_of_parts').addEventListener('input', updatePartDetails);
    });


    function updatePartDetails() {
        const numParts = document.getElementById('num_of_parts').value;
        const partDetails = document.getElementById('partDetails');

        // Clear existing details
        partDetails.innerHTML = '';

        // Create details for each part
        for (let i = 1; i <= numParts; i++) {
            createPartDetails(i);
        }
    }




    function createPartDetails(partNumber) {
        const partDetails = document.getElementById('partDetails');
        const detailsData = {!! json_encode($details) !!};

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


        console.log(detailsData);
        populatePartDetails(partDetailContainer, partNumber, detailsData[partNumber - 1]);

         // Add "Add Attributes" button to the button container
         createButton(buttonContainer, partNumber);

        // Append the button container to the main container
        partDetailContainer.appendChild(buttonContainer);
    }

    function populatePartDetails(container, partNumber, data) {
        // Populate part name
        createInputD(container, `Part ${partNumber} Name:`, `part_names[${partNumber}]`, 'text', container.querySelector(`[name="part_names[${partNumber}]"]`).value = data.part_name);

        // Populate other details based on your data structure
        createInputD( container, `Rec'D Quantity:`, `rec_quantity[${partNumber}]`, 'number',container.querySelector(`[name="rec_quantity[${partNumber}]"]`).value = data.rec_quantity);
        createInputD(container, `Verify Quantity:`, `verify_quantity[${partNumber}]`, 'number',container.querySelector(`[name="verify_quantity[${partNumber}]"]`).value = data.verify_quantity);
        createInputD(container, `Production Date:`, `prod_date[${partNumber}]`, 'date', container.querySelector(`[name="prod_date[${partNumber}]"]`).value = data.prod_date);
        createInputD(container, `Shift:`, `shift[${partNumber}]`, 'number',container.querySelector(`[name="shift[${partNumber}]"]`).value = data.shift);
        createInputD(container, `Can Use:`, `can_use[${partNumber}]`, 'number',container.querySelector(`[name="can_use[${partNumber}]"]`).value = data.can_use);
        createInputD(container, `Cant Use:`, `cant_use[${partNumber}]`, 'number',container.querySelector(`[name="cant_use[${partNumber}]"]`).value = data.cant_use);
        const customerDefectDetailInput = container.querySelector(`[name="customer_defect_detail[${partNumber}][]"]`);
        if (customerDefectDetailInput) {
            if (data.customer_defect_detail) {
                const customerDefectDetailArray = JSON.parse(data.customer_defect_detail);

                // Clear existing customer defect detail inputs
                customerDefectDetailInput.innerHTML = '';

                // Add input fields for each value in the array
                customerDefectDetailArray.forEach((value, index) => {
                    createInputD(container, `Customer Defect Detail ${index + 1}:`, `customer_defect_detail[${partNumber}][]`, 'text', value);
                });
            }
        }

        const daijoDefectDetailInput = container.querySelector(`[name="daijo_defect_detail[${partNumber}][]"]`);
        if (daijoDefectDetailInput) {
            if (data.daijo_defect_detail) {
                const daijoDefectDetailArray = JSON.parse(data.daijo_defect_detail);

                // Clear existing daijo defect detail inputs
                daijoDefectDetailInput.innerHTML = '';

                // Add input fields for each value in the array
                daijoDefectDetailArray.forEach((value, index) => {
                    createInputD(container, `Daijo Defect Detail ${index + 1}:`, `daijo_defect_detail[${partNumber}][]`, 'text', value);
                });
            }
        }

        const remarkInput = container.querySelector(`[name="remark[${partNumber}][]"`);
        if (remarkInput) {
            if (data.remark) {
                const remarkArray = JSON.parse(data.remark);

                // Clear existing remark inputs
                remarkInput.innerHTML = '';

                // Add input fields for each value in the array
                remarkArray.forEach((value, index) => {
                    if (["bisarepair", "tidakbisarepair"].includes(value)) {
                        // If the value is valid, create the dropdown option
                        createInputDropA(container, `Remark ${index + 1}:`, `remark[${partNumber}][]`, 'text', [value]);
                    } else {
                        // If the value is not valid, default to "other" and show the explanation input
                        console.log(`Invalid value detected: ${value}`);
                        createInputDropP(container, `Remark ${index + 1}:`, `remark[${partNumber}][]`, 'text', ['other'], value);
                    }
                });
            }
        }

            const remarkExplanationInput = container.querySelector(`[name="remark[${partNumber}][]_explanation"]`);
            if (remarkExplanationInput) {
                remarkExplanationInput.value = data.remark_explanation;
            }
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
        div.classList.add('d-none');

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


    function createInputDropI(container, labelText, name, type) {

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





    function createInputDropA(container, labelText, name, type, selectedValues) {
        console.log(`createInputDrop - Name: ${name}, Selected Values: ${selectedValues} PAKE METODE `);
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

        // Set the selected value(s)
        if (selectedValues) {
            selectedValues.forEach((value, index) => {
                if (index === 0) {
                    select.value = value;
                    select.dispatchEvent(new Event('change')); // Trigger change event to handle showing/hiding the explanation input
                }
            });
        }

        // Create input for explanation
        const explanationInput = document.createElement('input');
        explanationInput.type = 'text';
        explanationInput.name = `${name}_explanation`;
        explanationInput.classList.add('form-control', 'mt-2');
        explanationInput.placeholder = 'Please specify';
        explanationInput.style.display = select.value === 'other' ? 'block' : 'none'; // Initially hide the input if not 'other'

        // Append elements to the container
        div.appendChild(label);
        div.appendChild(select);

        container.appendChild(div);

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

    }



    function createInputDropP(container, labelText, name, type, selectedValues, explanationValue) {
        console.log(`createInputDrop - Name: ${name}, Selected Values: ${selectedValues}, Explanation Value: ${explanationValue} PAKE METODE P`);
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
        explanationInput.value = explanationValue;
        explanationInput.classList.add('form-control', 'mt-2');
        explanationInput.placeholder = 'Please specify';
        explanationInput.style.display = select.value === 'other' ? 'block' : 'none'; // Initially hide the input if not 'other'

        // Set the selected value(s)
        if (selectedValues) {
            selectedValues.forEach((value, index) => {
                if (index === 0) {
                    select.value = value;
                    select.dispatchEvent(new Event('change')); // Trigger change event to handle showing/hiding the explanation input

                }
            });
        }

        // Append elements to the container
        div.appendChild(label);
        div.appendChild(select);

        container.appendChild(div);

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


        // console.log(`Created dropdown for ${name}`);
        // return select;
    }


    function createInput(container, labelText, name, type) {
        const div = document.createElement('div');
        div.classList.add('mb-3');
        div.classList.add('d-none');

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

    function createInputD(container, labelText, name, type,  value = '') {
        const div = document.createElement('div');
        div.classList.add('mb-3');

        const label = document.createElement('label');
        label.textContent = labelText;
        label.classList.add('form-label');

        const input = document.createElement('input');
        input.type = type;
        input.name = name;
        input.value = value;
        input.classList.add('form-control');

        div.appendChild(label);
        div.appendChild(input);
        container.appendChild(div);
        console.log(`Created input for ${name}`);
        return input;
    }

    function createInputA(container, labelText, name, type) {
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
            createInputA(partDetailContainer, `Customer Defect Detail :`, `customer_defect_detail[${partNumber}][]`, 'text');
            createInputA(partDetailContainer, `Daijo Defect Detail :`, `daijo_defect_detail[${partNumber}][]`, 'text');
            createInputDropI(partDetailContainer, `Remark:`, `remark[${partNumber}][]`, 'text');
        } else {
            console.error(`Part container not found for part ${partNumber}`);
        }
    }

    function showDetails(index) {
        const detailsSection = document.getElementById(`partDetails`).children[index - 1];
        detailsSection.style.display = 'block';
    }
</script>

