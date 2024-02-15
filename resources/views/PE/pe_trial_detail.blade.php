@extends('layouts.app')

@section('content')

<h1>Trial Details</h1>

@if (Auth::user()->department->name === 'PI' && !$trial->tonage)
    <p>Tonage is not provided. Please input tonage:</p>
    <form method="post" action="{{ route('update.tonage', $trial->id) }}">
        @csrf
        <label for="tonage">Tonage:</label>
        <input type="text" id="tonage" name="tonage" required>
        <button type="submit">Submit</button>
    </form>
@endif

@if (Auth::user()->department->name === 'PE')
<table class="detail-table">
    <tr>
        <th>Field</th>
        <th>Value</th>
    </tr>

    <tr>
        <td>Customer:</td>
        <td>{{ $trial->customer }}</td>
    </tr>

    <tr>
        <td>Part Name:</td>
        <td>{{ $trial->part_name }}</td>
    </tr>

    <tr>
        <td>Part No:</td>
        <td>{{ $trial->part_no }}</td>
    </tr>

    <tr>
        <td>Model:</td>
        <td>{{ $trial->model }}</td>
    </tr>

    <tr>
        <td>Cavity:</td>
        <td>{{ $trial->cavity }}</td>
    </tr>

    <tr>
        <td>Status Trial:</td>
        <td>{{ $trial->status_trial }}</td>
    </tr>

    <tr>
        <td>Material:</td>
        <td>{{ $trial->material }}</td>
    </tr>

    <tr>
        <td>Status Material:</td>
        <td>{{ $trial->status_material }}</td>
    </tr>

    <tr>
        <td>Color:</td>
        <td>{{ $trial->color }}</td>
    </tr>

    <tr>
        <td>Material Consump:</td>
        <td>{{ $trial->material_consump }}</td>
    </tr>

    <tr>
        <td>Dimension Tooling:</td>
        <td>{{ $trial->dimension_tooling }}</td>
    </tr>

    <tr>
        <td>Member Trial:</td>
        <td>{{ $trial->member_trial }}</td>
    </tr>

    <tr>
        <td>Request Trial:</td>
        <td>{{ $trial->request_trial }}</td>
    </tr>

    <tr>
        <td>Trial Date:</td>
        <td>{{ $trial->trial_date }}</td>
    </tr>

    <tr>
        <td>Time Set Up Tooling:</td>
        <td>{{ $trial->time_set_up_tooling }}</td>
    </tr>

    <tr>
        <td>Time Setting Tooling:</td>
        <td>{{ $trial->time_setting_tooling }}</td>
    </tr>

    <tr>
        <td>Time Finish Inject:</td>
        <td>{{ $trial->time_finish_inject }}</td>
    </tr>

    <tr>
        <td>Time Set Down Tooling:</td>
        <td>{{ $trial->time_set_down_tooling }}</td>
    </tr>

    <tr>
        <td>Trial Cost:</td>
        <td>{{ $trial->trial_cost }}</td>
    </tr>

    <tr>
        <td>Tonage:</td>
        <td>{{ $trial->tonage }}</td>
    </tr>

    <tr>
        <td>Qty:</td>
        <td>{{ $trial->qty }}</td>
    </tr>

    <tr>
        <td>Adjuster:</td>
        <td>{{ $trial->adjuster }}</td>
    </tr>
    <!-- Add more rows for other details -->
</table>
@endif

@if (Auth::user()->department->name === 'PI')
<table class="detail-table">
    <tr>
        <th>Field</th>
        <th>Value</th>
    </tr>

    <tr>
        <td>Customer:</td>
        <td>{{ $trial->customer }}</td>
    </tr>

    <tr>
        <td>Part Name:</td>
        <td>{{ $trial->part_name }}</td>
    </tr>

    <tr>
        <td>Part No:</td>
        <td>{{ $trial->part_no }}</td>
    </tr>

    <tr>
        <td>Model:</td>
        <td>{{ $trial->model }}</td>
    </tr>

    <tr>
        <td>Cavity:</td>
        <td>{{ $trial->cavity }}</td>
    </tr>

    <tr>
        <td>Status Trial:</td>
        <td>{{ $trial->status_trial }}</td>
    </tr>

    <tr>
        <td>Material:</td>
        <td>{{ $trial->material }}</td>
    </tr>

    <tr>
        <td>Status Material:</td>
        <td>{{ $trial->status_material }}</td>
    </tr>

    <tr>
        <td>Color:</td>
        <td>{{ $trial->color }}</td>
    </tr>

    <tr>
        <td>Material Consump:</td>
        <td>{{ $trial->material_consump }}</td>
    </tr>

    <tr>
        <td>Dimension Tooling:</td>
        <td>{{ $trial->dimension_tooling }}</td>
    </tr>

    <tr>
        <td>Member Trial:</td>
        <td>{{ $trial->member_trial }}</td>
    </tr>

    <tr>
        <td>Request Trial:</td>
        <td>{{ $trial->request_trial }}</td>
    </tr>

    <tr>
        <td>Trial Date:</td>
        <td>{{ $trial->trial_date }}</td>
    </tr>

    <tr>
        <td>Time Set Up Tooling:</td>
        <td>{{ $trial->time_set_up_tooling }}</td>
    </tr>

    <tr>
        <td>Time Setting Tooling:</td>
        <td>{{ $trial->time_setting_tooling }}</td>
    </tr>

    <tr>
        <td>Time Finish Inject:</td>
        <td>{{ $trial->time_finish_inject }}</td>
    </tr>

    <tr>
        <td>Time Set Down Tooling:</td>
        <td>{{ $trial->time_set_down_tooling }}</td>
    </tr>

    <tr>
        <td>Trial Cost:</td>
        <td>{{ $trial->trial_cost }}</td>
    </tr>

    <tr>
        <td>Tonage:</td>
        <td>{{ $trial->tonage }}</td>
    </tr>

    <tr>
        <td>Qty:</td>
        <td>{{ $trial->qty }}</td>
    </tr>

    <tr>
        <td>Adjuster:</td>
        <td>{{ $trial->adjuster }}</td>
    </tr>
    <!-- Add more rows for other details -->
</table>
@endif

@if (Auth::user()->department->name === 'PI' && !$trial->tonage)
<table class="detail-table">
    <tr>
        <th>Field</th>
        <th>Value</th>
    </tr>

    <tr>
        <td>Customer:</td>
        <td>{{ $trial->customer }}</td>
    </tr>

    <tr>
        <td>Part Name:</td>
        <td>{{ $trial->part_name }}</td>
    </tr>

    <tr>
        <td>Part No:</td>
        <td>{{ $trial->part_no }}</td>
    </tr>

    <tr>
        <td>Model:</td>
        <td>{{ $trial->model }}</td>
    </tr>

    <tr>
        <td>Cavity:</td>
        <td>{{ $trial->cavity }}</td>
    </tr>

    <tr>
        <td>Status Trial:</td>
        <td>{{ $trial->status_trial }}</td>
    </tr>

    <tr>
        <td>Material:</td>
        <td>{{ $trial->material }}</td>
    </tr>

    <tr>
        <td>Status Material:</td>
        <td>{{ $trial->status_material }}</td>
    </tr>

    <tr>
        <td>Color:</td>
        <td>{{ $trial->color }}</td>
    </tr>

    <tr>
        <td>Material Consump:</td>
        <td>{{ $trial->material_consump }}</td>
    </tr>

    <tr>
        <td>Dimension Tooling:</td>
        <td>{{ $trial->dimension_tooling }}</td>
    </tr>

    <tr>
        <td>Member Trial:</td>
        <td>{{ $trial->member_trial }}</td>
    </tr>

    <tr>
        <td>Request Trial:</td>
        <td>{{ $trial->request_trial }}</td>
    </tr>

    <tr>
        <td>Trial Date:</td>
        <td>{{ $trial->trial_date }}</td>
    </tr>

    <tr>
        <td>Time Set Up Tooling:</td>
        <td>{{ $trial->time_set_up_tooling }}</td>
    </tr>

    <tr>
        <td>Time Setting Tooling:</td>
        <td>{{ $trial->time_setting_tooling }}</td>
    </tr>

    <tr>
        <td>Time Finish Inject:</td>
        <td>{{ $trial->time_finish_inject }}</td>
    </tr>

    <tr>
        <td>Time Set Down Tooling:</td>
        <td>{{ $trial->time_set_down_tooling }}</td>
    </tr>

    <tr>
        <td>Trial Cost:</td>
        <td>{{ $trial->trial_cost }}</td>
    </tr>

    <tr>
        <td>Tonage:</td>
        <td>{{ $trial->tonage }}</td>
    </tr>

    <tr>
        <td>Qty:</td>
        <td>{{ $trial->qty }}</td>
    </tr>

    <tr>
        <td>Adjuster:</td>
        <td>{{ $trial->adjuster }}</td>
    </tr>
    <!-- Add more rows for other details -->
</table>
@endif


@if (Auth::user()->department->name !='PE' && Auth::user()->department->name !='PI')
<table class="detail-table">
    <tr>
        <th>Field</th>
        <th>Value</th>
    </tr>

    <tr>
        <td>Customer:</td>
        <td>{{ $trial->customer }}</td>
    </tr>

    <tr>
        <td>Part Name:</td>
        <td>{{ $trial->part_name }}</td>
    </tr>

    <tr>
        <td>Part No:</td>
        <td>{{ $trial->part_no }}</td>
    </tr>

    <tr>
        <td>Model:</td>
        <td>{{ $trial->model }}</td>
    </tr>

    <tr>
        <td>Cavity:</td>
        <td>{{ $trial->cavity }}</td>
    </tr>

    <tr>
        <td>Status Trial:</td>
        <td>{{ $trial->status_trial }}</td>
    </tr>

    <tr>
        <td>Material:</td>
        <td>{{ $trial->material }}</td>
    </tr>

    <tr>
        <td>Status Material:</td>
        <td>{{ $trial->status_material }}</td>
    </tr>

    <tr>
        <td>Color:</td>
        <td>{{ $trial->color }}</td>
    </tr>

    <tr>
        <td>Material Consump:</td>
        <td>{{ $trial->material_consump }}</td>
    </tr>

    <tr>
        <td>Dimension Tooling:</td>
        <td>{{ $trial->dimension_tooling }}</td>
    </tr>

    <tr>
        <td>Member Trial:</td>
        <td>{{ $trial->member_trial }}</td>
    </tr>

    <tr>
        <td>Request Trial:</td>
        <td>{{ $trial->request_trial }}</td>
    </tr>

    <tr>
        <td>Trial Date:</td>
        <td>{{ $trial->trial_date }}</td>
    </tr>

    <tr>
        <td>Time Set Up Tooling:</td>
        <td>{{ $trial->time_set_up_tooling }}</td>
    </tr>

    <tr>
        <td>Time Setting Tooling:</td>
        <td>{{ $trial->time_setting_tooling }}</td>
    </tr>

    <tr>
        <td>Time Finish Inject:</td>
        <td>{{ $trial->time_finish_inject }}</td>
    </tr>

    <tr>
        <td>Time Set Down Tooling:</td>
        <td>{{ $trial->time_set_down_tooling }}</td>
    </tr>

    <tr>
        <td>Trial Cost:</td>
        <td>{{ $trial->trial_cost }}</td>
    </tr>

    <tr>
        <td>Tonage:</td>
        <td>{{ $trial->tonage }}</td>
    </tr>

    <tr>
        <td>Qty:</td>
        <td>{{ $trial->qty }}</td>
    </tr>

    <tr>
        <td>Adjuster:</td>
        <td>{{ $trial->adjuster }}</td>
    </tr>
    <!-- Add more rows for other details -->
</table>
@endif


<div class="autograph-container">
    <!-- Autograph Button 1 -->
    @if(Auth::check() && Auth::user()->department->name == 'PE')
    <button onclick="addAutograph(1, {{ $trial->id }})">Requested By </button>
    <!-- Autograph File Input 1 -->
    @endif
    <h2>Requested By PE</h2>
    <div class="autograph-box" id="autographBox1"></div>
    <div class="autograph-textbox" id="autographuser1"></div>
</div>



<div class="autograph-container">
    <!-- Autograph Button 2 -->
    @if(Auth::check() && Auth::user()->department->name == 'PE')
    <button onclick="addAutograph(2, {{ $trial->id }})">Verify By</button>
    @endif
    <h2>Verify By PE</h2>
    <div class="autograph-box" id="autographBox2"></div>
    <div class="autograph-textbox" id="autographuser2"></div>
</div>



<div class="autograph-container">
    <!-- Autograph Button 3 -->
    @if(Auth::check() && Auth::user()->department->name == 'PI')
    <button onclick="addAutograph(3, {{ $trial->id }})">Confirmed By PI 1 </button>
    @endif
    <!-- Autograph Textbox 3 -->
    <h2>Confirmed By PI 1</h2>
    <div class="autograph-box" id="autographBox3"></div>
    <div class="autograph-textbox" id="autographuser3"></div>
</div>

<div class="autograph-container">
    <!-- Autograph Button 4 -->
    @if(Auth::check() && Auth::user()->department->name == 'PI')
    <button onclick="addAutograph(4, {{ $trial->id }})">Confirmed By PI 2</button>
    @endif
    <!-- Autograph Textbox 4 -->
    <h2>Confirmed By PI 2</h2>
    <div class="autograph-box" id="autographBox4"></div>
    <div class="autograph-textbox" id="autographuser4"></div>
</div>

<div class="autograph-container">
    <!-- Autograph Button 5 -->
    @if(Auth::check() && Auth::user()->department->name == 'PI')
    <button onclick="addAutograph(5, {{ $trial->id }})">Confirmed By PI 3</button>
    @endif
    <!-- Autograph Textbox 5 -->
    <h2>Confirmed By PI 3</h2>
    <div class="autograph-box" id="autographBox5"></div>
    <div class="autograph-textbox" id="autographuser5"></div>
</div>


<div class="autograph-container">
    <!-- Autograph Button 6 -->
    @if(Auth::check() && Auth::user()->department->name == 'PI')
    <button onclick="addAutograph(6, {{ $trial->id }})">Approve</button>
    @endif
    <!-- Autograph Textbox 6 -->
    <h2>Approved By</h2>
    <div class="autograph-box" id="autographBox6"></div>
    <div class="autograph-textbox" id="autographuser6"></div>
</div>


@endsection

<style>
    .detail-table {
        border-collapse: collapse;
        width: 100%;
    }

    .detail-table th,
    .detail-table td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }

    .detail-table td:nth-child(2) {
        padding-left: 20px; /* Adjust the padding-left value as needed for indentation */
    }

    .autograph-container {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
    }

    .autograph-container button {
        margin-right: 10px; /* Adjust the spacing between buttons */
    }

    .autograph-box {
        width: 200px; /* Adjust the width as needed */
        height: 100px; /* Adjust the height as needed */
        background-size: contain;
        background-repeat: no-repeat;
        border: 1px solid #ccc; /* Add border for better visibility */
    }
    .autograph-textbox {
    position: relative;
    width: 200px; /* Set the width based on your preference */
    margin-top: 10px; /* Adjust the margin based on your layout */
    text-align: center;
    border: 1px solid black;
    /* Hide initially */
    }
</style>

<script>
    // Function to add autograph to the specified box
    function addAutograph(section, trialId) {
        // Get the div element
        var autographBox = document.getElementById('autographBox' + section);

        console.log('Section:', section);
        console.log('Report ID:', trialId);
        var username = '{{ Auth::check() ? Auth::user()->name : '' }}';
        console.log('username :', username);
        var imageUrl = '{{ asset(':path') }}'.replace(':path', username + '.png');
        console.log('image path :', imageUrl);

        autographBox.style.backgroundImage = "url('" +imageUrl + "')";

         // Make an AJAX request to save the image path
        fetch('/save-signature/' + trialId + '/' + section, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify({
                imagePath: imageUrl,
            }),
        })
        .then(response => response.json())
        .then(data => {
            console.log(data.message);
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }





    function checkAutographStatus(trialId) {
    // Assume you have a variable from the server side indicating the autograph status
    var autographs = {
        autograph_1: '{{ $trial->autograph_1 ?? null }}',
        autograph_2: '{{ $trial->autograph_2 ?? null }}',
        autograph_3: '{{ $trial->autograph_3 ?? null }}',
        autograph_4: '{{ $trial->autograph_4 ?? null }}',
        autograph_5: '{{ $trial->autograph_5 ?? null }}',
        autograph_6: '{{ $trial->autograph_6 ?? null }}',
    };

    var autographNames = {
        autograph_user_1: '{{ $trial->autograph_user_1 ?? null }}',
        autograph_user_2: '{{ $trial->autograph_user_2 ?? null }}',
        autograph_user_3: '{{ $trial->autograph_user_3 ?? null }}',
        autograph_user_4: '{{ $trial->autograph_user_4 ?? null }}',
        autograph_user_5: '{{ $trial->autograph_user_5 ?? null }}',
        autograph_user_6: '{{ $trial->autograph_user_6 ?? null }}',
    };

    // Loop through each autograph status and update the UI accordingly
    for (var i = 1; i <= 6; i++) {
        var autographBox = document.getElementById('autographBox' + i);
        var autographInput = document.getElementById('autographInput' + i);
        var autographNameBox = document.getElementById('autographuser' + i);

        // Check if autograph status is present in the database
        if (autographs['autograph_' + i]) {
            autographBox.style.display = 'block';

           // Construct URL based on the current location
           var url = '/' + autographs['autograph_' + i];

            // Update the background image using the URL
            autographBox.style.backgroundImage = "url('" + url + "')";

            var autographName = autographNames['autograph_user_' + i];
            autographNameBox.textContent = autographName;
            autographNameBox.style.display = 'block';

        }
    }
}


// Call the function to check autograph status on page load
window.onload = function () {
    checkAutographStatus({{ $trial->id }});
};
</script>
