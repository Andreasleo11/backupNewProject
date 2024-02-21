@extends('layouts.app')

@section('content')

<section>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{route('qaqc.home')}}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{route('qaqc.report.index')}}">Reports</a></li>
            <li class="breadcrumb-item active" aria-current="page">Create</li>
        </ol>
    </nav>
</section>

    <section>
        @if ($message = Session::get('success'))
            <div class="alert alert-success">
                <p>{{ $message }}</p>
            </div>
        @endif
    </section>

    <section aria-label="content">
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <h2 class="mb-4">Verification Form</h2>
                    <form action="{{route('qaqc.report.createheader')}}" method="post">
                        @csrf

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



                        <button type="submit" class="btn btn-primary mt-3">Next</button>
                    </form>

                </div>
            </div>
        </div>
    </section>

    

@endsection

