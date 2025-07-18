@extends('layouts.app')
@section('content')
    @livewire('DeliveryNote.Form', ['deliveryNote' => $deliveryNote ?? new \App\Models\DeliveryNote()])
@endsection
