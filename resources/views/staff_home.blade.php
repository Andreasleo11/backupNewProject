@extends('layouts.app')

@section('content')
<div class="container">
    @if (Auth::user()->department == "DIREKTUR")
        @include('partials.dasboard-direktur')
    @elseif(Auth::user()->department == "QA" || Auth::user()->department == "QC")
        @include('partials.dashboard-qaqc')

    <!-- Add more dashboard per department -->
    @endif
</div>

@endsection
