@extends('layouts.app')

@section('content')
    <div class="container mt-3">
        <div class="row justify-content-center">
            <div class="col">
                <div class="card">
                    <div class="card-body p-0">
                        <h1>PPS WIZARD digunakan untuk membentuk production planning schedule yang ditarik
                            berdasarkan data delivery schedule yang di input pada sistem SAP.

                            PPS WIZARD akan membawa user ke dalam beberapa tahap yang harus diperhatikan sampai ke
                            proses pembentukan PPS. untuk mulai silahkan klik tombol lanjut.
                        </h1>

                        <a href="{{ route('menupps') }}" class="btn btn-secondary float-right"> Lanjut</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
