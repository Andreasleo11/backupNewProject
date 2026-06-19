@extends('new.layouts.admin-settings')

@section('title', 'Employees')

@section('page-title', 'Employee Master')
@section('page-subtitle', 'Manage and audit employee records and sync from JPayroll.')

@section('settings-content')
    <livewire:admin.employees.employee-index />
@endsection
