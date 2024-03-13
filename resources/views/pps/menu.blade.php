@extends('layouts.app')

@section('content')

<div class="container mt-3">
    <div class="row justify-content-center">
        <div class="col">
            <div class="card">
                <div class="card-header">SILAHKAN PILIH SCENARIO</div>
                <div class="card-body p-0">
                    <form action="{{ route('portal') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="scenario">Silahkan memilih scenario yang diinginkan:</label>
                            <select name="scenario" id="scenario" class="form-control">
                                <option value="injection">Plastic Injection</option>
                                <option value="second">Second Process</option>
                                <option value="assembly">Assembly</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Pilih</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection