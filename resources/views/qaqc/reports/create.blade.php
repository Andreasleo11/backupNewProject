@extends('layouts.app')

@section('content')

<style>
    #itemDropdown {
        max-height: 200px; /* Set maximum height for the dropdown */
        overflow-y: auto; /* Enable vertical scrolling */
        border: 1px solid #ccc; /* Optional: Add border for visual clarity */
        position: absolute; /* Position the dropdown absolutely */
        z-index: 999; /* Ensure dropdown is above other elements */
        background-color: #fff; /* Set background color to white */
        opacity: 1; /* Adjust opacity to ensure dropdown is not transparent */

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

    th, td {
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

<section>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{route('qaqc.home')}}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{route('qaqc.report.index')}}">Reports</a></li>
            <li class="breadcrumb-item active" aria-current="page">Create</li>
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
                    <h2 class="mb-4">Verification Form</h2>
                    <div class="card">
                        <div class="card-body">
                            <form action="{{route('qaqc.report.createheader')}}" method="post">
                                @csrf

                                {{-- Rec'D Date --}}
                                <div class="mb-3">
                                    <label for="Rec_Date" class="form-label">Rec'D Date:</label>
                                    <input type="date"  value="{{ $header->Rec_Date ?? '' }}" id="Rec_Date" name="Rec_Date" class="form-control" required>
                                </div>

                                {{-- Verify Date --}}
                                <div class="mb-3">
                                    <label for="Verify_Date" class="form-label">Verify Date:</label>
                                    <input type="date"   value="{{ $header->Verify_Date ?? '' }}"  id="Verify_Date" name="Verify_Date" class="form-control" required>
                                </div>

                                {{-- Customer --}}
                                <div class="mb-3">
                                    <label for="Customer" class="form-label">Customer:</label>
                                    <input type="text"  value="{{ $header->Customer ?? '' }}"  id="itemNameInput" name="Customer" class="form-control" required placeholder="Enter item name" autocomplete="off">
                                    <div id="itemDropdown" class="dropdown-content"></div></td>
                                </div>

                                {{-- Invoice No --}}
                                <div class="mb-3">
                                    <label for="Invoice_No" class="form-label">Invoice No:</label>
                                    <input type="text"  value="{{ $header->Invoice_No ?? '' }}" id="Invoice_No" name="Invoice_No" class="form-control" required>
                                </div>

                                {{-- Number of Parts --}}
                                <div class="mb-3">
                                    <label for="num_of_parts" class="form-label">Number of Parts:</label>
                                    <input type="number"  value="{{ $header->num_of_parts ?? '' }}" id="num_of_parts" name="num_of_parts" class="form-control" required>
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

