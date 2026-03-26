@extends('new.layouts.app')

@section('title', 'Employees')

@section('page-title', 'Employees')
@section('page-subtitle', 'Showing employees data sync from Jpayroll.')

@section('content')
    <div class="max-w-6xl mx-auto">
        <livewire:admin.employees.employee-index />
    </div>
@endsection
