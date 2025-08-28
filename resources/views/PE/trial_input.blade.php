@extends('layouts.app') <!-- Use your layout file if different -->

@section('content')
  <div class="container">
    <form method="post" action="{{ route('pe.input') }}">
      @csrf

      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label for="customer">Customer:</label>
            <input type="text" class="form-control" id="customer" name="customer" required>
          </div>

          <div class="form-group">
            <label for="part_name">Part Name:</label>
            <input type="text" class="form-control" id="part_name" name="part_name" required>
          </div>

          <div class="form-group">
            <label for="part_no">Part No:</label>
            <input type="text" class="form-control" id="part_no" name="part_no" required>
          </div>

          <div class="form-group">
            <label for="model">Model:</label>
            <input type="text" class="form-control" id="model" name="model" required>
          </div>

          <div class="form-group">
            <label for="cavity">Cavity:</label>
            <input type="text" class="form-control" id="cavity" name="cavity" required>
          </div>

          <div class="form-group">
            <label for="status_trial">Status Trial:</label>
            <input type="text" class="form-control" id="status_trial" name="status_trial" required>
          </div>
        </div>

        <div class="col-md-6">
          <div class="form-group">
            <label for="material">Material:</label>
            <input type="text" class="form-control" id="material" name="material" required>
          </div>

          <div class="form-group">
            <label for="status_material">Status Material:</label>
            <select class="form-control" id="status_material" name="status_material" required>
              <option value="Virgin">Virgin</option>
              <option value="Recycle">Recycle</option>
              <option value="Mixing">Mixing</option>
            </select>
          </div>

          <div class="form-group">
            <label for="color">Color:</label>
            <input type="text" class="form-control" id="color" name="color" required>
          </div>

          <div class="form-group">
            <label for="material_consump">Material Consump:</label>
            <input type="text" class="form-control" id="material_consump" name="material_consump"
              required>
          </div>

          <div class="form-group">
            <label for="dimension_tooling">Dimension Tooling:</label>
            <input type="text" class="form-control" id="dimension_tooling"
              name="dimension_tooling">
          </div>

          <div class="form-group">
            <label for="member_trial">Member Trial:</label>
            <input type="text" class="form-control" id="member_trial" name="member_trial" required>
          </div>
        </div>

        <div class="col-md-">
          <div class="form-group">
            <label for="request_trial">Request Trial:</label>
            <input type="date" class="form-control" id="request_trial" name="request_trial"
              required>
          </div>

          <div class="form-group">
            <label for="trial_date">Trial Date:</label>
            <input type="date" class="form-control" id="trial_date" name="trial_date" required>
          </div>

          <div class="form-group">
            <label for="time_set_up_tooling">Time Set Up Tooling:</label>
            <input type="text" class="form-control" id="time_set_up_tooling"
              name="time_set_up_tooling">
          </div>

          <div class="form-group">
            <label for="time_setting_tooling">Time Setting Tooling:</label>
            <input type="text" class="form-control" id="time_setting_tooling"
              name="time_setting_tooling">
          </div>

          <div class="form-group">
            <label for="time_finish_inject">Time Finish Inject:</label>
            <input type="text" class="form-control" id="time_finish_inject"
              name="time_finish_inject">
          </div>

          <div class="form-group">
            <label for="time_set_down_tooling">Time Set Down Tooling:</label>
            <input type="text" class="form-control" id="time_set_down_tooling"
              name="time_set_down_tooling">
          </div>

          <div class="form-group">
            <label for="trial_cost">Trial Cost:</label>
            <input type="text" class="form-control" id="trial_cost" name="trial_cost">
          </div>

          <div class="form-group">
            <label for="qty">Qty:</label>
            <input type="text" class="form-control" id="qty" name="qty" required>
          </div>

          <div class="form-group">
            <label for="adjuster">Adjuster:</label>
            <input type="text" class="form-control" id="adjuster" name="adjuster">
          </div>
        </div>
      </div>

      <button type="submit" class="btn btn-primary">Submit</button>
    </form>
  </div>
@endsection
