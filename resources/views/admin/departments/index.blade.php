@extends('new.layouts.admin-settings')

@section('title', 'Departments')

@section('page-title', 'Departments')
@section('page-subtitle', 'Manage department master data for all branches.')

@section('settings-content')
    <livewire:admin.departments.department-index />
@endsection
