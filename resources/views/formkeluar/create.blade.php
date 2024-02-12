@extends('layouts.app')

@section('content')
<form method="POST" action="{{ route('formkeluar.insert') }}">
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
        <label for="alasan_izin_keluar">alasan izin :</label>
        <input type="text" id="alasan_izin_keluar" name="alasan_izin_keluar">
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
        <input type="checkbox" id="keterangan_user" name="keterangan_user" value="1" required>
        <label for="keterangan_user">Saya yang membuat form keluar ini dengan sebenar benarnya</label>
    </div>

    <div>
        <label for="waktu_keluar">Waktu Keluar:</label>
        <input type="text" id="waktu_keluar" name="waktu_keluar" required>
        <select id="satuan_waktu_keluar" name="satuan_waktu_keluar" required>
            <option value="jam">Jam</option>
        </select>
    </div>

    <div>
        <label for="jam_keluar">Jam Keluar:</label>
        <input type="time" id="jam_keluar" name="jam_keluar" required>
    </div>
    <div>
        <label for="jam_kembali">Jam Kembali:</label>
        <input type="time" id="jam_kembali" name="jam_kembali" required>
    </div>


    <button type="submit">Submit</button>
</form>


@endsection