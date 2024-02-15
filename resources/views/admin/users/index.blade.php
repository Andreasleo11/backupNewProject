@extends('layouts.app')

@section('content')

@include('partials.add-user-modal')

<section aria-label="header">
    <div class="d-flex justify-content-between align-items-center">
        <span class="fs-1">User List</span>
        <div>
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
          <li class="breadcrumb-item"><a href="{{route('superadmin.home')}}">Home</a></li>
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
        <div class="card-body p-0">
            <div class="table-responsive-lg">
                <table class="table table-striped table-hover align-middle m-0 text-center">
                    <thead>
                        <tr class="fs-5">
                            <th class="py-3">No</th>
                            <th class="py-3">Name</th>
                            <th class="py-3">Email</th>
                            <th class="py-3">Role</th>
                            <th class="py-3">Department</th>
                            <th class="py-3">Specification</th>
                            <th class="py-3">Created At</th>
                            <th class="py-3">Action</th>
                        </tr>
                    </thead>
                    <tbody class="table-group-divider ">
                        @forelse ($users as $user)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{$user->name}}</td>
                                <td>{{$user->email}}</td>
                                <td>{{$user->role->name}}</td>
                                <td>{{$user->department->name}}</td>
                                <td>{{$user->specification->name}}</td>
                                <td>{{ $user->created_at }}</td>
                                <td>
                                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editUserModal{{ $user->id }}">Edit</button>
                                    @include('partials.edit_user_modal')

                                    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteConfirmationModal{{ $user->id }}">Delete</button>
                                    @include('partials.delete-user-modal')
                                </td>
                            </tr>
                        @empty
                        @endforelse

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>


<script src="{{ asset('js/modal.js') }}"></script>
@endsection
