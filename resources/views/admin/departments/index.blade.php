@extends('new.layouts.app')

@section('title', 'Departments')

@section('page-title', 'Departments')
@section('page-subtitle', 'Manage department master data for all branches.')

@section('content')
    <div class="max-w-6xl mx-auto">
        <livewire:admin.departments.department-index />
    </div>
@endsection
