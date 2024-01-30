@extends('layouts.app')

@section('content')

<!-- Add User modal -->
@include('partials.add_user_modal')

<!-- Add user modal submit button -->
<button type="submit" class="btn btn-primary btn" data-bs-toggle="modal" data-bs-target="#add-user-modal">
    <i class="lni lni-plus"></i>
    Add user
</button>

<!-- Content -->
<div class="mt-3">
    <div class="card border-0">
        <div class="card mb-0">

            <!-- Table Header -->
            <div class="card-header pt-3">
                <h2>User List</h2>
            </div>

            <!-- Table body -->
            <div class="card-body">
                <div class="table-responsive-lg">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th scope="col">Name</th>
                                <th scope="col">Email</th>
                                <th scope="col">Role</th>
                                <th scope="col">Modified</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody class="table-group-divider">
                            <tr>
                                <td>raymond lay</td>
                                <td>raymondlay023@gmail.com</td>
                                <td>Admin</td>
                                <td>1 Januari 2024</td>
                                <td>

                                    <!-- Modal Edit user -->
                                    @include('partials.edit_user_modal')
                                    <button type="submit" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#edit-modal">Edit</button>
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </td>
                            </tr>
                            <tr>
                                <td>raymond lay</td>
                                <td>raymondlay023@gmail.com</td>
                                <td>Admin</td>
                                <td>1 Januari 2024</td>
                            </tr>
                            <tr>
                                <td>raymond lay</td>
                                <td>raymondlay023@gmail.com</td>
                                <td>Admin</td>
                                <td>1 Januari 2024</td>
                            </tr>
                            <tr>
                                
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/modal.js') }}"></script>
@endsection