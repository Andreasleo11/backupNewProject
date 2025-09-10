@extends('layouts.app')

@section('content')
  <style>
    .animated-button {
      animation: rainbow 2s infinite;
      /* Rainbow animation */
      background-color: #ff0000;
      /* Red color */
      color: #000;
      /* White text color */
      width: 200px;
      /* Set the width */
      height: 60px;
      /* Set the height */
      font-size: 20px;
      /* Set the font size */
    }

    .animated-button:hover {
      background-color: #009900;
      /* Darker shade of green */
    }

    .button-container {
      text-align: center;
      /* Center the button horizontally */
    }

    @keyframes rainbow {
      0% {
        background-color: #ff0000;
      }

      /* Red */
      20% {
        background-color: #ff7f00;
      }

      /* Orange */
      40% {
        background-color: #ffff00;
      }

      /* Yellow */
      60% {
        background-color: #00ff00;
      }

      /* Green */
      80% {
        background-color: #0000ff;
      }

      /* Blue */
      100% {
        background-color: #8b00ff;
      }

      /* Purple */
    }
  </style>

  <form method="POST" action="{{ route('discipline.import') }}" enctype="multipart/form-data">
    @csrf
    <label for="excel_files">Upload File Excel yang sudah diisi dengan point point kedisiplinan disini
      dalam bentuk EXCEL (.xlsx):</label>
    <input type="file" name="excel_files[]" id="excel_files" onchange="displayUploadedFiles()"
      multiple>
    <br>
    <br>
    <div class="button-container">
      <button type="submit" class="animated-button">SUBMIT EXCEL FILE</button>
    </div>
  </form>
@endsection
