@extends('new.layouts.app')

@section('title', 'Users')

@section('page-title', 'Users')
@section('page-subtitle', 'Manage application users, roles, and status.')

@section('content')
    <div class="max-w-6xl mx-auto">
        <livewire:admin.users.user-index />
    </div>
@endsection
