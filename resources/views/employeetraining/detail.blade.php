@extends('layouts.app')
@push('extraCss')
    <style>
        .autograph-box {
            width: 200px; /* Adjust the width as needed */
            height: 100px; /* Adjust the height as needed */
            background-size: contain;
            background-repeat: no-repeat;
            border: 1px solid #ccc; /* Add border for better visibility */
        }
    </style>
@endpush


@section('content')

<section aria-label="header" class="container">
        
</section>




<section aria-label="table-report" class="container mt-5">
        <div class="card">
            <div class="mt-4 text-center">
                <span class="h1 fw-semibold">Employee Training Report</span> <br>
            </div>
            <hr>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-borderlesss">
                        <tbody>
                            <tr>
                                <th>Doc num</th>
                                
                                <td>: {{ $data->doc_num }}</td>
                                <th>Name</th>
                                <td>: {{ $data->name }}</td>
                            </tr>
                            <tr>
                                <th>Nik</th>
                                <td>: {{ $data->nik }}</td>
                                <th>Department</th>
                                <td>: {{ $data->department }}</td>
                            </tr>
                            <tr>
                            <th>Mulai Bekerja</th>
                                <td>: {{ $data->mulai_bekerja }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="table-responsive mt-4">
                    <table class="table table-bordered table-hover text-center table-striped mb-0">
                        <thead>
                            <tr>
                                <th rowspan="2" class="align-middle">No</th>
                                <th rowspan="2" class="align-middle">Training Name</th>
                                <th rowspan="2" class="align-middle">Training Date</th>
                                <th colspan="2" class="align-middle">Penyelenggara</th>
                                <th rowspan="2" class="align-middle">Hasil Pelatihan</th>
                                <th rowspan="2" class="align-middle">Keterangan</th>

                            </tr>
                            <tr>
                                <th>Internal</th>
                                <th>External</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data->trainingDetail as $detail)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $detail->training_name}}</td>
                                <td>{{ $detail->training_date}}</td>
                                <td> @if($detail->is_internal) check @endif</td>
                                <td> @if($detail->is_external) check @endif</td>
                                <td>{{ $detail->result}}</td>
                                <td>{{ $detail->information}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>


@endsection