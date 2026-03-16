@extends('new.layouts.app')

@section('title', 'Create Budget Report')
@section('page-title', 'Create Report')
@section('page-subtitle', 'Fill in the period and items or upload from Excel template.')

@section('content')
    <livewire:monthly-budget.form />
@endsection
