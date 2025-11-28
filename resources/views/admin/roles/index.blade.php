@extends('new.layouts.app')

@section('title', 'Roles')

@section('page-title', 'Roles')
@section('page-subtitle', 'Define and manage role-based access.')

@section('content')
    <div class="max-w-6xl mx-auto">
        <livewire:admin.roles.role-index />
    </div>
@endsection
