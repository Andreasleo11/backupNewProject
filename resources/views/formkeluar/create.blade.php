@extends('layouts.app')

@section('content')
    <h2>Create Form Keluar</h2>
    <section class="breadcrumb">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('formkeluar.home') }}">Form Keluar</a></li>
                <li class="breadcrumb-item active">Create</li>
            </ol>
        </nav>
    </section>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-9">
                <h2 class="mt-2 mb-4">Form Keluar</h2>
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="{{ route('formkeluar.insert') }}" class="row gx-3 p-3">
                            @csrf

                            <div class="form-group">
                                <label class="form-label" for="name">Name</label>
                                <input class="form-control" type="text" id="name" name="name" required>
                            </div>

                            <div class="form-group mt-3 col-md-4">
                                <label class="form-label" for="no_karyawan">No. Karyawan</label>
                                <input class="form-control" type="text" id="no_karyawan" name="no_karyawan" required>
                            </div>

                            <div class="form-group mt-3 col-md-4">
                                <label class="form-label" for="jabatan">Jabatan</label>
                                <input class="form-control" type="text" id="jabatan" name="jabatan">
                            </div>

                            <div class="form-group mt-3 col-md-4">
                                <label class="form-label" for="department">Departemen</label>
                                <select name="department" id="department" class="form-control" required>
                                    <option value="" selected disabled>Pilih departemen..</option>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->name }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group mt-3 col-md-4">
                                <div class="row g-2">
                                    <label for="waktu_keluar">Waktu Keluar</label>
                                    <div class="col">
                                        <input class="form-control" type="text" id="waktu_keluar" name="waktu_keluar"
                                            required>
                                    </div>
                                    <div class="col-auto">
                                        <select class="form-select" id="satuan_waktu_keluar" name="satuan_waktu_keluar"
                                            required>
                                            <option value="jam">Jam</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mt-3 col-md-8">
                                <label class="form-label" for="pengganti">Pengganti</label>
                                <input class="form-control" type="text" id="pengganti" name="pengganti">
                            </div>

                            <div class="form-group mt-3">
                                <label class="form-label" for="alasan_izin_keluar">Alasan izin </label>
                                <textarea class="form-control" type="text" id="alasan_izin_keluar" name="alasan_izin_keluar"></textarea>
                            </div>

                            <div class="form-group mt-3">
                                <label class="form-label" for="keperluan">Keperluan</label>
                                <textarea class="form-control" id="keperluan" name="keperluan" required></textarea>
                            </div>

                            <div class="form-group mt-3 col-md-6">
                                <label class="form-label" for="tanggal_masuk">Tanggal Masuk</label>
                                <input class="form-control" type="date" id="tanggal_masuk" name="tanggal_masuk">
                            </div>

                            <div class="form-group mt-3 col-md-6">
                                <label class="form-label" for="tanggal_permohonan">Tanggal Permohonan</label>
                                <input class="form-control" type="date" id="tanggal_permohonan" name="tanggal_permohonan"
                                    required>
                            </div>

                            <div class="form-group mt-3 col-md-6">
                                <label class="form-label" for="jam_keluar">Jam Keluar</label>
                                <input class="form-control" type="time" id="jam_keluar" name="jam_keluar" required>
                            </div>

                            <div class="form-group mt-3 col-md-6">
                                <label class="form-label" for="jam_kembali">Jam Kembali</label>
                                <input class="form-control" type="time" id="jam_kembali" name="jam_kembali" required>
                            </div>

                            <div class="form-group mt-4">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="keterangan_user"
                                        name="keterangan_user" value="1" required>
                                    <label for="keterangan_user" class="form-check-label">Saya yang membuat form
                                        keluar ini dengan sebenar benarnya</label>
                                </div>
                            </div>

                            <button class="btn btn-primary mt-4" type="submit">Submit</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
