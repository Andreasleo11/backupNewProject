@extends('layouts.app')

@push('extraCss')
    <style>
        .circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #007bff;
            border: 2px solid #007bff; /* This creates the #007bff outline */
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: bold;
        }

        .outline {
            background-color: transparent;
            color: #007bff; /* Hide the text inside the circles */
        }
    </style>
@endpush
@section('content')


<div class="container mt-3">
    <div class="row justify-content-center">
        <div class="col">
            <div class="card">
                <div class="card-body p-0">
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-flex-grow-1 border-bottom p-0">
                                <div class="p-4">
                                    <div class="row g-3 align-items-center">
                                        <div class="col-auto">
                                            <div class="circle">1</div>
                                    </div>
                                    <div class="col">
                                        <div class="progress" role="progressbar" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100" style="height: 12px">
                                            <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 50%"></div>
                                        </div>
                                    </div>

                                    <!-- Circle 2 -->
                                    <div class="col-auto">
                                        <div class="circle outline">2</div>
                                    </div>
                                    <div class="col">
                                        <div class="progress" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="height: 12px">
                                            <div class="progress-bar" style="width: 0%"></div>
                                        </div>
                                    </div>

                                    <!-- Circle 3 -->
                                    <div class="col-auto">
                                        <div class="circle outline">3</div>
                                    </div>
                                </div>
                                    </div>
                                </div>
                            </div>
                        <form action = "{{ route('step1') }}">
                            <div class="mb-3">
                                    <label for="start_date" class="form-label">Pilih Tanggal:</label>
                                    <input type="date"  id="start_date" name="start_date" class="form-control" required>
                            </div>
                            <div class="d-flex justify-content-between border-top p-3">
                                <div class="">
                                <button class="btn btn-secondary float-right"> Mulai Proses 1 </button>
                                </div>
                        </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection