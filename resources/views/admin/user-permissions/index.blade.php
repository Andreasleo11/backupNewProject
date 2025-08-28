@extends('layouts.app')

@push('extraCss')
  <style>
    .custom-first-color {
      background-color: #C33C54;
      color: white;
    }

    .custom-second-color {
      background-color: #254E70;
      color: white;
    }

    .custom-third-color {
      background-color: #d06807;
      color: white;
    }

    .custom-fourth-color {
      background-color: #C74EC3;
      color: white;
    }

    .custom-fifth-color {
      background-color: #138e69;
      color: white;
    }

    .custom-sixth-color {
      background-color: #59544B;
      color: white;
    }

    .custom-seventh-color {
      background-color: #c89933;
      color: white;
    }

    .custom-eight-color {
      background-color: #a1a1a1;
      color: white;
    }

    .table td,
    .table th {
      vertical-align: middle;
    }

    .permissions-container {
      max-height: 300px;
      overflow-y: auto;
      border: 1px solid #ddd;
      padding: 10px;
    }
  </style>
@endpush

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
        <form action="{{ route('superadmin.users.permissions.index') }}" method="GET">
          <div class="input-group">
            <input type="text" name="search" class="form-control"
              placeholder="Search by name or email" value="{{ request('search') }}">
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
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($users as $user)
                  <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                      <button class="btn btn-secondary" type="button" data-bs-toggle="collapse"
                        data-bs-target="#permissions-{{ $user->id }}" aria-expanded="false"
                        aria-controls="permissions-{{ $user->id }}">
                        Show
                      </button>
                      <button class="btn btn-primary"
                        data-bs-target="#edit-user-permission-modal-{{ $user->id }}"
                        data-bs-toggle="modal">Edit</button>

                      <!-- Edit Permissions Modal -->
                      @include('partials.edit-user-permission-modal')
                    </td>
                  </tr>
                  <tr>
                    <td colspan="3">
                      <div class="collapse" id="permissions-{{ $user->id }}">
                        <div class="permissions-container">
                          @if ($user->permissions->isEmpty())
                            <p>No permissions assigned.</p>
                          @else
                            @php
                              $groupedPermissions = $user->permissions->groupBy(function (
                                  $permission,
                              ) {
                                  return explode('-', $permission->name, 2)[0];
                              });
                            @endphp

                            @foreach ($groupedPermissions as $prefix => $permissions)
                              <div class="mb-2">
                                <strong>{{ ucfirst($prefix) }} Permissions :</strong>
                                @foreach ($permissions as $permission)
                                  @php
                                    if (strpos($permission->name, 'get-') === 0) {
                                        $badgeClass = 'custom-second-color';
                                    } elseif (strpos($permission->name, 'edit-') === 0) {
                                        $badgeClass = 'custom-third-color';
                                    } elseif (strpos($permission->name, 'delete-') === 0) {
                                        $badgeClass = 'custom-first-color';
                                    } elseif (strpos($permission->name, 'update-') === 0) {
                                        $badgeClass = 'custom-fourth-color';
                                    } elseif (strpos($permission->name, 'store-') === 0) {
                                        $badgeClass = 'custom-fifth-color';
                                    } elseif (strpos($permission->name, 'create-') === 0) {
                                        $badgeClass = 'custom-seventh-color';
                                    } else {
                                        $badgeClass = 'custom-sixth-color';
                                    }
                                  @endphp

                                  <span class="badge {{ $badgeClass }} pt-2 px-3 my-2">
                                    <h6>{{ $permission->name }}</h6>
                                  </span>
                                @endforeach
                              </div>
                            @endforeach
                          @endif
                        </div>
                      </div>
                    </td>
                  </tr>
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
