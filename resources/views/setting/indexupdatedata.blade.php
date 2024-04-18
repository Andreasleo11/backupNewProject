@extends('layouts.app')

@section('content')

<H1> Page </H1>

<div id="uploaded-files">
        <h3>Uploaded files:</h3>
        <ul id="file-list"></ul>
    </div>

    <a href="/storage/AutomateFile/databomwip.xlsx"> DOWNLOAD BOMWIP </a><br>
    <a href="/storage/AutomateFile/delactual.xlsx"> DOWNLOAD Delactual </a>

<form method="POST" action="{{ route('updatedata') }}" enctype="multipart/form-data">
        @csrf
        <label for="select_option">Select an option:</label>
        <select name="selected_option" id="select_option">
            <option value="sap_bom_wip">sap_bom_wip</option>
            <option value="sap_delactual">sap_delactual</option>
            <option value="sap_delsched">sap_delsched</option>
            <option value="sap_delso">sap_delso</option>
            <option value="sap_inventoryfg">sap_inventoryfg</option>
            <option value="sap_inventorymtr">sap_inventorymtr</option>
            <option value="sap_lineproduction">sap_lineproduction</option>
        </select>
        <br>
        <label for="excel_files">Upload Excel files:</label>
        <input type="file" name="excel_files[]" id="excel_files" onchange="displayUploadedFiles()" multiple>
        <br>
        <button type="submit">Submit</button>
    </form>

    <script>
        function displayUploadedFiles() {
            const files = document.getElementById('excel_files').files;
            const fileList = document.getElementById('file-list');
            for (let i = 0; i < files.length; i++) {
                const li = document.createElement('li');
                li.textContent = files[i].name;
                console.log(li.textContent);
                fileList.appendChild(li);
            }
        }
    </script>

@endsection