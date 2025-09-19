@extends('layouts.app')

@section('content')
  <h2>Create Form Cuti</h2>
  <section class="breadcrumb">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('formcuti.home') }}">Form Cuti</a></li>
        <li class="breadcrumb-item active">Create</li>
      </ol>
    </nav>
  </section>

  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-8">
        <h2 class="mt-2 mb-4">Form Cuti</h2>
        <div class="card mt-4">
          <div class="card-body">
            <form method="POST" action="{{ route('formcuti.insert') }}" class="row g-3">
              @csrf

              <div class="form-group">
                <label class="form-label" for="name">Nama</label>
                <input class="form-control" type="text" id="name" name="name" required>
              </div>

              <div class="form-group mt-3 col-md-4">
                <label class="form-label" for="no_karyawan">No. Karyawan</label>
                <input class="form-control" type="text" id="no_karyawan" name="no_karyawan"
                  required>
              </div>

              <div class="form-group mt-3 col-md-4">
                <label class="form-label" for="jabatan">Jabatan</label>
                <input class="form-control" type="text" id="jabatan" name="jabatan">
              </div>

              <div class="form-group mt-3 col-md-4">
                <label class="form-label" for="department">Department</label>
                <select name="department" id="department" class="form-select" required>
                  <option value="" disabled selected>Select department..</option>
                  @foreach ($deparments as $deparment)
                    <option value="{{ $deparment->name }}">{{ $deparment->name }}</option>
                  @endforeach
                </select>
              </div>

              <div class="form-group mt-3 col-md-6">
                <label class="form-label" for="jenis_cuti">Jenis Cuti</label>
                <select class="form-select" id="jenis_cuti" name="jenis_cuti" required>
                  <option value="sakit">Sakit</option>
                  <option value="izin">Izin</option>
                  <option value="tahun">Tahun</option>
                </select>
              </div>
              <div class="form-group mt-3 col-md-6">
                <div class="row g-2">
                  <label for="waktu_cuti">Waktu Cuti</label>
                  <div class="col">
                    <input class="form-control" type="text" id="waktu_cuti" name="waktu_cuti"
                      required>
                  </div>
                  <div class="col-auto">
                    <select class="form-select" id="satuan_waktu_cuti" name="satuan_waktu_cuti"
                      required>
                      <option value="jam">Jam</option>
                      <option value="hari">Hari</option>
                    </select>
                  </div>
                </div>
              </div>

              <div class="form-group mt-3">
                <label class="form-label" for="pengganti">Pengganti</label>
                <input class="form-control" type="text" id="pengganti" name="pengganti">
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
                <input class="form-control" type="date" id="tanggal_permohonan"
                  name="tanggal_permohonan" required>
              </div>

              <div class="form-group mt-3 col-md-6">
                <label class="form-label" for="mulai_tanggal">Mulai Tanggal</label>
                <input class="form-control" type="date" id="mulai_tanggal" name="mulai_tanggal"
                  required>
              </div>

              <div class="form-group mt-3 col-md-6">
                <label class="form-label" for="sampai_tanggal">Sampai Tanggal</label>
                <input class="form-control" type="date" id="sampai_tanggal"
                  name="sampai_tanggal" required>
              </div>

              <div class="form-group mt-4">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="keterangan_user"
                    name="keterangan_user" value="1" required class="form-group mt-3">
                  <label class="form-check-label" for="keterangan_user">
                    Saya yang membuat form cuti ini dengan sebenar benarnya
                  </label>
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
