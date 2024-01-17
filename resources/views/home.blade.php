@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            {{-- <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    {{ __('You are logged in!') }}
                </div>
            </div>


            <!-- Assign SuperAdmin Role Button -->
            <form action="{{ url('/assign-superadmin-role') }}" method="post">
                @csrf
                <button type="submit">Assign SuperAdmin Role</button>
            </form>

            <!-- Remove SuperAdmin Role Button -->
            <form action="{{ url('/remove-superadmin-role') }}" method="post">
                @csrf
                <button type="submit">Remove SuperAdmin Role</button>
            </form> --}}
        </div>
    </div>
    {{-- <a href="{{ route('assignRoleManually') }}" class="btn btn-primary">PressMEE</a> --}}
</div>

@endsection
