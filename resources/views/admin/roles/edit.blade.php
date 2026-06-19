@extends('new.layouts.admin-settings')

@section('title', 'Edit Role')

@section('page-title', 'Edit Role')
@section('page-subtitle', 'Modify role definition and assign its system permissions.')

@section('settings-content')
    <livewire:admin.roles.role-edit :roleId="$id" />
@endsection
