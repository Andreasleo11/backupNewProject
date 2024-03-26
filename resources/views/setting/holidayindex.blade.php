@extends('layouts.app')

@section('content')
    
<section class="header">
        <div class="row">
            <div class="col">
                <h1 class="h1"> Holiday List </h1>

                <a href="{{ route('createholiday') }}" class="btn btn-secondary float-right"> Tambahkan Hari Libur</a>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="card mt-5">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped text-center mb-0">
                        <thead>
                            <tr class="align-middle fw-semibold fs-5">
                                <th>Tanggal</th>
                                <th>Nama Libur</th>
                                <th>Deskripsi</th>
                                <th>Half Day</th>
                                </tr>
                        </thead>
                            <tbody>
                            @if($datas->isEmpty())
                            <tr>
                                <td colspan="8">DATA UNAVAILABLE</td>
                            </tr>
                            @else
                            <!-- Loop through $data and display the rows -->
                            @foreach($datas as $item)
                            <tr>
                                <td>{{ $item->date }}</td>
                                <td>{{ $item->holiday_name }}</td>
                                <td>{{ $item->description }}</td>
                                <td>{{ $item->half_day }}</td>
                            </tr>
                            @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection