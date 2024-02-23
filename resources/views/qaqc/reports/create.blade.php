@extends('layouts.app')

@section('content')

{{-- <section>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{route('qaqc.home')}}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{route('qaqc.report.index')}}">Reports</a></li>
            <li class="breadcrumb-item active" aria-current="page">Create</li>
        </ol>
    </nav>
</section> --}}

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

    <section>
        @if ($message = Session::get('success'))
            <div class="alert alert-success">
                <p>{{ $message }}</p>
            </div>
        @endif
    </section>

    <section aria-label="content">
        <div class="container mt-3">
            <div class="row justify-content-center">
                <div class="col-md-9">
                    <div class="card">
                        <div class="card-body">
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

                            <hr>


                            <span class="h3">Create Verification Header</span>
                            <p class="text-secondary mt-2">You need to fill the verification report header </p>

                            <form action="{{route('qaqc.report.createheader')}}" method="post" class="px-3 pt-3">
                                @csrf

                                <input type="hidden" value="{{ Auth::user()->name }}" name="created_by">

                                {{-- Rec'D Date --}}
                                <div class="mb-3">
                                    <label for="Rec_Date" class="form-label">Rec'D Date:</label>
                                    <input type="date"  value="{{ $header->Rec_Date ?? '' }}" id="Rec_Date" name="Rec_Date" class="form-control" required>
                                </div>

                                {{-- Verify Date --}}
                                <div class="mb-3">
                                    <label for="Verify_Date" class="form-label">Verify Date:</label>
                                    <input type="date"   value="{{ $header->Verify_Date ?? '' }}"  id="Verify_Date" name="Verify_Date" class="form-control" required>
                                </div>

                                {{-- Customer --}}
                                <div class="mb-3">
                                    <label for="Customer" class="form-label">Customer:</label>
                                    <input type="text"  value="{{ $header->Customer ?? '' }}"  id="Customer" name="Customer" class="form-control" required>
                                </div>

                                {{-- Invoice No --}}
                                <div class="mb-3">
                                    <label for="Invoice_No" class="form-label">Invoice No:</label>
                                    <input type="text"  value="{{ $header->Invoice_No ?? '' }}" id="Invoice_No" name="Invoice_No" class="form-control" required>
                                </div>

                                {{-- Number of Parts --}}
                                <div class="mb-3">
                                    <label for="num_of_parts" class="form-label">Number of Parts:</label>
                                    <input type="number"  value="{{ $header->num_of_parts ?? '' }}" id="num_of_parts" name="num_of_parts" class="form-control" required>
                                </div>



                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary mt-3">Next</button>
                                </div>
                            </form>
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </section>



@endsection

