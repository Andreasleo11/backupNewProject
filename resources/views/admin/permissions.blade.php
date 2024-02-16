@extends('layouts.app')

@section('content')
<h3> Permission </h3>
<div class="card mt-4">
    <div class="card-body">
        <!-- Table of roles -->
        <table class="table">
            <thead>
              <tr>
                <th scope="col">ID User</th>
                <th scope="col">Name</th>
                <th scope="col">Department</th>
                <th class="col">Add</th>
                <th class="col">Edit</th>
                <th class="col">View</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <th scope="row">1</th>
                <td>Asep</td>
                <td>Production</td>
                <td>
                    <div class="row">test</div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="addCheck">
                        <label class="form-check-label" for="selectAll"></label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="editCheck">
                        <label class="form-check-label" for="selectAll"></label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="viewCheck">
                        <label class="form-check-label" for="selectAll"></label>
                    </div>
                </td>
                <td>
                </td>
                <td>
                </td>
              </tr>
             
              <tr>
                <th scope="row">1</th>
                <td>Asep</td>
                <td>Production</td>
                <td>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="addCheck">
                        <label class="form-check-label" for="addCheck"></label>
                    </div>
                </td>
                <td>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="editCheck">
                        <label class="form-check-label" for="editCheck"></label>
                    </div>
                </td>
                <td>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="viewCheck">
                        <label class="form-check-label" for="viewCheck"></label>
                    </div>
                </td>
              </tr>
            </tbody>
        </table>

        <!-- Table of permission -->
        <table class="table">
            <thead>
              <tr>
                <th scope="col">No</th>
                <th scope="col">Add</th>
                <th scope="col">Edit</th>
                <th scope="col">View</th>
                <th>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="selectAll">
                        <label class="form-check-label" for="selectAll">Select All</label>
                    </div>
                </th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <th scope="row">1</th>
                <td>Super Admin</td>
                <td>
                    <div class="form-check">
                        <input type="checkbox" id="add">
                    </div>
                </td>
              </tr>
              <tr>
                <th scope="row">2</th>
                <td>Staff</td>
                <td>
                    <div class="btn-group" role="group" aria-label="button group 2">
                        <input type="checkbox" class="btn-check" id="btncheck1" autocomplete="off">
                        <label class="btn btn-outline-secondary" for="btncheck1">Production</label>
                      
                        <input type="checkbox" class="btn-check" id="btncheck2" autocomplete="off">
                        <label class="btn btn-outline-secondary" for="btncheck2">Business</label>
                      
                        <input type="checkbox" class="btn-check" id="btncheck3" autocomplete="off">
                        <label class="btn btn-outline-secondary" for="btncheck3">QA/QC</label>
                      </div>
                </td>
              </tr>
              <tr>
                <th scope="row">3</th>
                <td>User</td>
                <td>User</td>
              </tr> 
            </tbody>
          </table>
    </div>      
</div>
@endsection