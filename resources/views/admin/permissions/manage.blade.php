@extends('layouts.app')

@section('content')
    @include('partials.alert-success-error')
    <div class="container">
        <div class="row mb-3">
            <div class="col">
                <h1>Manage Permissions</h1>
            </div>
            <div class="col text-end">
                <button class="btn btn-primary" data-bs-target="#add-permission-modal" data-bs-toggle="modal">+ Add
                    Permission</button>
            </div>
        </div>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Permission Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($permissions as $permission)
                    <tr>
                        <td>{{ $permission->name }}</td>
                        <td>
                            <a href="{{ route('superadmin.permissions.edit', $permission->id) }}"
                                class="btn btn-sm btn-primary">Edit</a>
                            <form action="{{ route('superadmin.permissions.destroy', $permission->id) }}" method="POST"
                                class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @include('partials.add-permission-modal')
@endsection
