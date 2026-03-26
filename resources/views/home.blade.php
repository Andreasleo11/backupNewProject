@extends('new.layouts.app')

@section('page-title', 'Overview')

@section('content')
    <div class="max-w-7xl mx-auto">
        @livewire('dashboard.global-dashboard')
    </div>
@endsection
