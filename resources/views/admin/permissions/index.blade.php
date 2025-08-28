@extends('layouts.app')

@section('content')
  @include('partials.alert-success-error')
  <div class="container">
    <div class="row mb-3">
      <div class="col">
        <h1>Manage Permissions</h1>
      </div>
      <div class="col text-end">
        <button class="btn btn-primary btn-sm" data-bs-target="#add-permission-modal"
          data-bs-toggle="modal">+ Add
          Permission</button>
      </div>
    </div>

    <div class="row mb-3">
      <div class="col">
        <form action="{{ route('superadmin.permissions.index') }}" method="GET">
          <div class="input-group">
            <input type="text" name="search" class="form-control"
              placeholder="Search by name or description" value="{{ request('search') }}">
            <button class="btn btn-outline-secondary" type="submit">Search</button>
          </div>
        </form>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>ID</th>
            <th>Permission Name</th>
            <th>Description</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($permissions as $permission)
            <tr>
              <td>{{ $permission->id }}</td>
              <td>{{ $permission->name }}</td>
              <td>{{ $permission->description }}</td>
              <td>
                @include('partials.edit-permission-modal')
                <button data-bs-target="#edit-permission-modal-{{ $permission->id }}"
                  data-bs-toggle="modal" class="btn btn-outline-primary btn-sm">Edit</button>
                @include('partials.delete-confirmation-modal', [
                    'id' => $permission->id,
                    'route' => 'superadmin.permissions.destroy',
                    'title' => 'Delete permission confirmation',
                    'body' =>
                        'Are you sure want to delete <strong>' .
                        $permission->name .
                        '</strong>?',
                ])
                <button data-bs-target="#delete-confirmation-modal-{{ $permission->id }}"
                  data-bs-toggle="modal" class="btn btn-danger btn-sm">Delete</button>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <div class="mt-3">
      {{ $permissions->links() }}
    </div>
  </div>

  @include('partials.add-permission-modal')
@endsection
