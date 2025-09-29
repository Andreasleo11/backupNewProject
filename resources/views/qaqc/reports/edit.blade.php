@extends('layouts.app')

@section('content')
    <style>
        #itemDropdown {
            max-height: 200px;
            /* Set maximum height for the dropdown */
            overflow-y: auto;
            /* Enable vertical scrolling */
            border: 1px solid #ccc;
            /* Optional: Add border for visual clarity */
            position: absolute;
            /* Position the dropdown absolutely */
            z-index: 999;
            /* Ensure dropdown is above other elements */
            background-color: #fff;
            /* Set background color to white */
            opacity: 1;
            /* Adjust opacity to ensure dropdown is not transparent */
        }

        .dropdown-item {
            padding: 5px;
            cursor: pointer;
        }

        .dropdown-item:hover {
            background-color: #f0f0f0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }

        th {
            background-color: #f2f2f2;
        }

        .add-data-btn {
            text-align: right;
            margin-bottom: 10px;
        }

        .add-data-btn button {
            padding: 8px 16px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }

        .itemNameInput {
            width: 100%;
            box-sizing: border-box;
        }
    </style>

    {{-- <section>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{route('qaqc.home')}}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{route('qaqc.report.index')}}">Reports</a></li>
            <li class="breadcrumb-item active" aria-current="page">Create</li>
        </ol>
    </nav>
</section> --}}

    <style>
        .circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #007bff;
            border: 2px solid #007bff;
            /* This creates the #007bff outline */
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: bold;
        }

        .outline {
            background-color: transparent;
            color: #007bff;
            /* Hide the text inside the circles */
        }
    </style>

    <section>
        @if ($message = Session::get('success'))
            <div class="alert alert-success">
                <p>{{ $message }}</p>
            </div>
        @endif
    </section>

    <section aria-label="content">
        <div class="container mt-3">
            <div class="row justify-content-center">
                <div class="col-md-9">
                    <div class="card">
                        <div class="card-body">
                            <div class="row g-3 align-items-center">
                                <div class="col-auto">
                                    <div class="circle">1</div>
                                </div>
                                <div class="col">
                                    <div class="progress" role="progressbar" aria-valuenow="50" aria-valuemin="0"
                                        aria-valuemax="100" style="height: 12px">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated"
                                            style="width: 50%"></div>
                                    </div>
                                </div>

                                <!-- Circle 2 -->
                                <div class="col-auto">
                                    <div class="circle outline">2</div>
                                </div>
                                <div class="col">
                                    <div class="progress" role="progressbar" aria-valuenow="0" aria-valuemin="0"
                                        aria-valuemax="100" style="height: 12px">
                                        <div class="progress-bar" style="width: 0%"></div>
                                    </div>
                                </div>

                                <!-- Circle 3 -->
                                <div class="col-auto">
                                    <div class="circle outline">3</div>
                                </div>
                            </div>

                            <hr>

                            <span class="h3">Edit Verification Header</span>
                            <p class="text-secondary mt-2">You need to fill the verification report header </p>

                            <form action="{{ route('qaqc.report.updateHeader', $id) }}" method="post" class="px-3 pt-3">
                                @csrf

                                <input type="hidden" value="{{ Auth::user()->name }}" name="created_by">

                                {{-- Rec'D Date --}}
                                <div class="mb-3">
                                    <label for="rec_date" class="form-label">Rec'D Date:</label>
                                    <input type="date" value="{{ $header->rec_date ?? '' }}" id="rec_date"
                                        name="rec_date" class="form-control" required>
                                </div>

                                {{-- Verify Date --}}
                                <div class="mb-3">
                                    <label for="verify_date" class="form-label">Verify Date:</label>
                                    <input type="date" value="{{ $header->verify_date ?? '' }}" id="verify_date"
                                        name="verify_date" class="form-control" required>
                                </div>

                                {{-- Customer --}}
                                <div class="mb-3">
                                    <label for="customer" class="form-label">Customer:</label>
                                    <input type="text" value="{{ $header->customer ?? '' }}" id="itemNameInput"
                                        name="customer" class="form-control" required placeholder="Enter item name"
                                        autocomplete="off">
                                    <div id="itemDropdown" class="dropdown-content"></div>
                                    </td>
                                </div>

                                {{-- Invoice No --}}
                                <div class="mb-3">
                                    <label for="invoice_no" class="form-label">Invoice No:</label>
                                    <input type="text" value="{{ $header->invoice_no ?? '' }}" id="invoice_no"
                                        name="invoice_no" class="form-control" required>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary mt-3">Next</button>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <script>
        const itemNameInput = document.getElementById('itemNameInput');
        const itemDropdown = document.getElementById('itemDropdown');

        itemNameInput.addEventListener('keyup', function() {
            const inputValue = itemNameInput.value.trim();

            // Make an AJAX request to fetch relevant items
            fetch(`/customers?customer_name=${inputValue}`) // Changed to 'customer_name'
                .then(response => response.json())
                .then(data => {
                    // Clear previous dropdown options
                    itemDropdown.innerHTML = '';

                    // Display dropdown options
                    if (data.length > 0) {
                        data.forEach(item => {
                            const option = document.createElement('div');
                            option.classList.add('dropdown-item');
                            option.textContent = item;
                            option.addEventListener('click', function() {
                                itemNameInput.value = item;
                                itemDropdown.innerHTML = ''; // Hide dropdown after selection
                            });
                            itemDropdown.appendChild(option);
                        });
                        itemDropdown.style.display = 'block'; // Show dropdown
                    } else {
                        itemDropdown.style.display = 'none'; // Hide dropdown if no options
                    }
                })
                .catch(error => console.error('Error:', error));
        });

        // Close dropdown when clicking outside the dropdown or input field
        document.addEventListener('click', function(event) {
            if (!itemNameInput.contains(event.target) && !itemDropdown.contains(event.target)) {
                itemDropdown.style.display = 'none';
                console.log(itemNameInput.value);
            }
        });
    </script>
@endsection
