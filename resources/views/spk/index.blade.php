@extends('layouts.app')
@section('content')
    @include('partials.alert-success-error')
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">SPK</a>
                </li>
                <li class="breadcrumb-item active">List</li>
            </ol>
        </nav>

        <div class="row">
            <div class="col">
                <h2 class="fw-bold">SPK List</h2>
            </div>
            <div class="col text-end">
                @php
                    $showCreateButton = false;
                    if (!$authUser->is_head && !$authUser->is_gm && $authUser->department->name !== 'DIRECTOR') {
                        $showCreateButton = true;
                    }
                @endphp
                @if ($showCreateButton)
                    <a href="#" class="btn btn-primary">New Report</a>
                @endif
            </div>
        </div>

        <div class="card mt-5">
            <div class=card-body>
                <table class="table table-border text-center mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>test</th>
                        </tr>
                    </thead>
                    <tbody>
                        <td>1</td>
                        <td>test</td>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
