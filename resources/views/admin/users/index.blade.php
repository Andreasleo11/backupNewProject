@extends('layouts.app')

@section('content')

    <section aria-label="header">
        <div class="d-flex justify-content-between align-items-center">
            <span class="fs-1">User List</span>
            <div>
                @include('partials.add-user-modal')
                <button type="submit" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-user-modal">
                    <i class="lni lni-plus"></i>
                    Add user
                </button>
            </div>
        </div>
    </section>

    <section class="breadcrumb">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('superadmin') }}">Home</a></li>
                <li class="breadcrumb-item active">Users</li>
            </ol>
        </nav>
    </section>

    @if ($message = Session::get('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <p>{{ $message }}</p>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @elseif ($errors->any())
        <div class="alert alert-danger alert-dismissable fade show" role="alert">
            <div class="d-flex">
                <div class="flex-grow-1">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <div class="">
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    <section aria-label="table">
        <div class="card ">
            <!-- Table body -->
            <div class="card-body">
                <div class="table-responsive p-2">
                    <button id="delete-selected-btn" data-delete-url="{{ route('superadmin.users.deleteSelected') }}"
                        class="btn btn-danger mb-3">Delete Selected</button>

                    {{ $dataTable->table() }}
                    @foreach ($users as $user)
                        @include('partials.edit-user-modal')
                        @include('partials.delete-user-modal')
                        @include('partials.reset-user-password-modal')
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endsection

@push('extraJs')
    {{ $dataTable->scripts() }}

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var checkInterval = setInterval(function() {
                var thElement = document.querySelector('th.check_all');
                if (thElement) {
                    // Set padding-left using inline style

                    clearInterval(checkInterval);
                    // Create an input element
                    var input = document.createElement('input');
                    input.style.marginLeft = '10px'; // Adjust the padding as needed
                    input.setAttribute('type', 'checkbox');
                    input.setAttribute('class', 'form-check-input');

                    // Append the input element to the <th> element
                    thElement.appendChild(input);

                    // Variable to track the state of checkboxes
                    var isChecked = false;

                    // Attach a click event listener to the <th> element
                    thElement.addEventListener('click', function() {
                        // Find all checkboxes in the table body
                        var checkboxes = document.querySelectorAll('tbody input[type="checkbox"]');

                        // Toggle the state of checkboxes
                        checkboxes.forEach(function(checkbox) {
                            checkbox.checked = !isChecked;
                        });

                        // Update the state of isChecked variable
                        isChecked = !isChecked;
                    });
                }
            }, 100); // Adjust the interval time as needed

            // Attach a click event listener to the delete button
            document.getElementById('delete-selected-btn').addEventListener('click', function() {
                // Find all checkboxes in the table body
                var checkboxes = document.querySelectorAll('tbody input[type="checkbox"]:checked');

                var ids = [];
                checkboxes.forEach(function(checkbox) {
                    // Extract the user ID from the checkbox ID
                    var userId = checkbox.id.replace('checkbox', '');
                    ids.push(userId);
                });


                // Send an AJAX request to delete the selected records
                if (ids.length > 0) {
                    // Get the CSRF token value from the meta tag
                    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute(
                        'content');
                    var deleteRoute = document.getElementById('delete-selected-btn').getAttribute(
                        'data-delete-url');

                    fetch(deleteRoute, {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken // Include the CSRF token in the request headers
                            },
                            body: JSON.stringify({
                                ids: ids
                            }),
                        })
                        .then(response => {
                            if (response.ok) {
                                // Handle success response
                                console.log('Selected records deleted successfully');
                                // Refresh the window
                                location.reload();
                            } else {
                                // Handle error response
                                console.error('Failed to delete selected records.');
                            }
                        })
                        .catch(error => {
                            console.error('An error occurred:', error);
                        });
                } else {
                    console.warn('No records selected for deletion.');
                }
            });

        });
    </script>
@endpush
