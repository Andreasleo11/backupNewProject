@extends('layouts.app')

@section('content')

<h1>Index untuk MouldDown</h1>

@dd($datas)
<section class="header">
        <div class="row">
            <div class="col">
                <h1 class="h1">Line Down</h1>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="card mt-5">
            <div class="card-body p-0">
                <div class="table-responsive">
                {{ $dataTable->table() }}
                </div>
            </div>
        </div>

    
        @include('partials.add-new-linedown-modal')
        <a class="btn btn-secondary float-right" data-bs-target="#add-new-linedown" data-bs-toggle="modal" > add </a>
        
        
        @foreach($datas as $data)
            @include('partials.edit-line-modal')
        
            @include('partials.delete-confirmation-modal', [
                            'id' => $data->line_code,
                            'route' => 'deleteline',
                            'title' => 'Delete Line confirmation',
                            'body' => 'Are you sure want to delete ' . $data->line_code . '?',
                        ])
        @endforeach
    </section>


{{ $dataTable->scripts() }}

@endsection