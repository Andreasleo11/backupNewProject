@extends('layouts.app')

@section('content')

<form action="{{ route('processInjectionForm') }}" method="POST">
    @csrf
    <div class="form-group">
        <label for="start_date">Tanggal Awal:</label>
        <input type="date" name="start_date" id="start_date" class="form-control" required>
    </div>
    <div class="form-group">
        <label for="end_date">Tanggal Akhir:</label>
        <input type="date" name="end_date" id="end_date" class="form-control" required>
    </div>
    <div class="form-group">
        <label for="hm_fg">Jarak H-Min FG (hari):</label>
        <input type="number" name="hm_fg" id="hm_fg" class="form-control" required>
    </div>
    <div class="form-group">
        <label for="hm_wip">Jarak H-Min WIP (hari):</label>
        <input type="number" name="hm_wip" id="hm_wip" class="form-control" required>
    </div>
    <div class="form-group">
        <label for="jarak_gudang">Jarak simpan Gudang (hari):</label>
        <input type="number" name="jarak_gudang" id="jarak_gudang" class="form-control" required>
    </div>
    <div class="form-group">
        <label for="max_manpower">Batas Maksimal Man Power Per Hari:</label>
        <input type="number" name="max_manpower" id="max_manpower" class="form-control" required>
    </div>
    <div class="form-group">
        <label for="max_mould_change">Batas Maksimal Pergantian Mould Per Mesin Per Hari:</label>
        <input type="number" name="max_mould_change" id="max_mould_change" class="form-control" required>
    </div>
    <div class="form-group">
        <label for="forecast">Termasuk forecast:</label>
        <select name="forecast" id="forecast" class="form-control" required>
            <option value="ya">Ya</option>
            <option value="tidak">Tidak</option>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Submit</button>
</form>


@endsection