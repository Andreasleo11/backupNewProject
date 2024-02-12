@extends('layouts.app')

@section('content')
<form method="POST" action="{{ route('formcuti.insert') }}">
    @csrf

    <div>
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required>
    </div>

    <div>
        <label for="jabatan">Jabatan:</label>
        <input type="text" id="jabatan" name="jabatan">
    </div>

    <div>
        <label for="department">Department:</label>
        <input type="text" id="department" name="department" required>
    </div>

    <div>
        <label for="jenis_cuti">Jenis Cuti:</label>
        <select id="jenis_cuti" name="jenis_cuti" required>
            <option value="sakit">Sakit</option>
            <option value="izin">Izin</option>
            <option value="tahun">Tahun</option>
        </select>
    </div>

    <div>
        <label for="pengganti">Pengganti:</label>
        <input type="text" id="pengganti" name="pengganti">
    </div>

    <div>
        <label for="keperluan">Keperluan:</label>
        <textarea id="keperluan" name="keperluan" required></textarea>
    </div>

    <div>
        <label for="tanggal_masuk">Tanggal Masuk:</label>
        <input type="date" id="tanggal_masuk" name="tanggal_masuk">
    </div>

    <div>
        <label for="no_karyawan">No. Karyawan:</label>
        <input type="text" id="no_karyawan" name="no_karyawan" required>
    </div>

    <div>
        <label for="tanggal_permohonan">Tanggal Permohonan:</label>
        <input type="date" id="tanggal_permohonan" name="tanggal_permohonan" required>
    </div>

    <div>
        <label for="mulai_tanggal">Mulai Tanggal:</label>
        <input type="date" id="mulai_tanggal" name="mulai_tanggal" required>
    </div>

    <div>
        <label for="sampai_tanggal">Sampai Tanggal:</label>
        <input type="date" id="sampai_tanggal" name="sampai_tanggal" required>
    </div>

    <div>
        <input type="checkbox" id="keterangan_user" name="keterangan_user" value="1" required>
        <label for="keterangan_user">Saya yang membuat form cuti ini dengan sebenar benarnya</label>
    </div>

    <div>
        <label for="waktu_cuti">Waktu Cuti:</label>
        <input type="text" id="waktu_cuti" name="waktu_cuti" required>
        <select id="satuan_waktu_cuti" name="satuan_waktu_cuti" required>
            <option value="jam">Jam</option>
            <option value="hari">Hari</option>
        </select>
    </div>



    <button type="submit">Submit</button>
</form>


@endsection