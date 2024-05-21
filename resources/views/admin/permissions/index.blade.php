@extends('layouts.app')

@section('content')
    @include('partials.alert-success-error')
    <div class="container">
        <div class="row mb-3">
            <div class="col">
                <h1>Manage User Permissions</h1>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col">
                <form action="{{ route('superadmin.permissions.index') }}" method="GET">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Search by name or email"
                            value="{{ request('search') }}">
                        <button class="btn btn-outline-secondary" type="submit">Search</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Permissions</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $user)
                                    <tr>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            @forelse ($user->permissions as $permission)
                                                {{ $permission->name }} <br>
                                            @empty
                                                -
                                            @endforelse
                                        </td>
                                        <td>
                                            <button class="btn btn-primary btn-sm"
                                                data-bs-target="#edit-permission-modal-{{ $user->id }}"
                                                data-bs-toggle="modal">Edit</button>
                                        </td>
                                    </tr>

                                    <!-- Edit Permissions Modal -->
                                    @include('partials.edit-permission-modal')
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-4">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
