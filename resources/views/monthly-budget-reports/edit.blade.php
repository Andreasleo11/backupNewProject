@extends('new.layouts.app')

@section('title', 'Edit Budget Report')
@section('page-title', 'Edit Report')
@section('page-subtitle', 'Update report details or adjust line items for the current period.')

@section('content')
    <livewire:monthly-budget.form :reportId="$report->id" />
@endsection
