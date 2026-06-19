@extends('new.layouts.admin-settings')

@section('title', 'Roles')

@section('page-title', 'Roles & Permissions')
@section('page-subtitle', 'Define system roles and their associated permission matrices.')

@section('settings-content')
    <livewire:admin.roles.role-index />
@endsection
