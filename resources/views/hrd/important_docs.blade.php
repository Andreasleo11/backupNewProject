@extends('layouts.app')

@section('content')
<section class="header">
    <!-- Modal Add Report -->
    @include('partials.add_important_doc')

    <div class="d-flex mb-3 row-flex">
        <div class="h2 p-2 me-auto">Important Documents</div>
        <div>
            <div class="btn btn-primary" type="submit" data-bs-toggle="modal" data-bs-target="#add-important-doc-modal"> + Add Report</div>
        </div>
    </div>
</section>

<section class="content">
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover table-bordered mb-0">
                <thead>
                    <tr>
                      <th scope="col">No</th>
                      <th scope="col">Name</th>
                      <th scope="col">Type</th>
                      <th scope="col">Expired Date</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <th scope="row">1</th>
                      <td>Mark</td>
                      <td>Otto</td>
                      <td>@mdo</td>
                    </tr>
                    <tr>
                      <th scope="row">2</th>
                      <td>Jacob</td>
                      <td>Thornton</td>
                      <td>@fat</td>
                    </tr>
                    <tr>
                      <th scope="row">3</th>
                      <td colspan="2">Larry the Bird</td>
                      <td>@twitter</td>
                    </tr>
                  </tbody>
            </table>
        </div>
    </div>
</section>
@endsection
