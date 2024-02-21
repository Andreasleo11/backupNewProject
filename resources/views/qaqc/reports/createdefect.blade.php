@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                    
                    {{ __('Hello monseiurs') }}
                </div>
            </div>
        </div>
    </div>  
</div>



<div class="container">
    <div class="row justify-content-center">
        <a href="{{ route('purchaserequest.home') }}" class="btn btn-primary">CREATE DEFECT </a>
    </div>
</div>

@include('partials.add-defect-modal');

<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-defect-modal">+ Add Defect</button>

<script>
    const checkCustomerDefect = document.getElementById('checkCustomerDefect');
    const checkDaijoDefect = document.getElementById('checkDaijoDefect');
    const customerDefectGroup = document.getElementById('customerDefectGroup');
    const daijoDefectGroup = document.getElementById('daijoDefectGroup');
    const customerDefectCategory = document.getElementById('customerDefectCategory');
    const daijoDefectCategory = document.getElementById('daijoDefectCategory');

    checkCustomerDefect.addEventListener('change', function() {
        if (this.checked) {
            customerDefectGroup.style.display = 'block';
        } else {
            customerDefectGroup.style.display = 'none';
        }
    });

    checkDaijoDefect.addEventListener('change', function() {
        if (this.checked) {
            daijoDefectGroup.style.display = 'block';
        } else {
            daijoDefectGroup.style.display = 'none';
        }
    });

    const remarkSelect = document.getElementById('remark');
    const otherInput = document.getElementById('other');

    remarkSelect.addEventListener('change', function() {
        if (this.value === 'other') {
            otherInput.style.display = 'block';
        } else {
            otherInput.style.display = 'none';
        }
    });
</script>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h3>Defects</h3>
                    <div class="row justify-content-center">
                        <div class="col-3">
                            <div class="list-group" id="list-tab" role="tablist">
                                <a class="list-group-item list-group-item-action active" id="list-home-list" data-bs-toggle="list" href="#list-home" role="tab" aria-controls="list-home">Detail1</a>
                                <a class="list-group-item list-group-item-action" id="list-profile-list" data-bs-toggle="list" href="#list-profile" role="tab" aria-controls="list-profile">detail2</a>
                                <a class="list-group-item list-group-item-action" id="list-messages-list" data-bs-toggle="list" href="#list-messages" role="tab" aria-controls="list-messages">detail3</a>
                            </div>
                        </div>
                        <div class="col-9">
                            <div class="tab-content" id="nav-tabContent">
                                <div class="tab-pane fade show active" id="list-home" role="tabpanel">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead class="text-center">
                                                <th>Customer Defect</th>
                                                <th>Daijo Defect</th>
                                                <th>Remarks</th>
                                                <th>Action</th>
                                            </thead>
                                            <tbody>
                                                <tr class="text-center">
                                                    <td>cusdef1</td>
                                                    <td>daijodef1</td>
                                                    <td>remark1</td>
                                                    <td><button class="btn btn-danger"><i class='bx bx-trash-alt'></i></button></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="list-profile" role="tabpanel">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead class="text-center">
                                                <th>Customer Defect</th>
                                                <th>Daijo Defect</th>
                                                <th>Remarks</th>
                                                <th>Action</th>
                                            </thead>
                                            <tbody>
                                                <tr class="text-center">
                                                    <td>cusdef2</td>
                                                    <td>daijodef2</td>
                                                    <td>remark2</td>
                                                    <td><button class="btn btn-danger"><i class='bx bx-trash-alt'></i></button></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="list-messages" role="tabpanel">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead class="text-center">
                                                <th>Customer Defect</th>
                                                <th>Daijo Defect</th>
                                                <th>Remarks</th>
                                                <th>Action</th>
                                            </thead>
                                            <tbody>
                                                <tr class="text-center">
                                                    <td>cusdef3</td>
                                                    <td>daijodef3</td>
                                                    <td>remark3</td>
                                                    <td><button class="btn btn-danger"><i class='bx bx-trash-alt'></i></button></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                              </div>
                        </div>
                    </div>

                    <a href="" class="btn btn-primary mt-3">+ Add item</a>
                </div>
            </div>
        </div>
    </div>
</div>

<a href="#" class="btn btn-primary">Add row</a>

<a href="{{ route('qaqc.report.createdetail') }}" class="btn btn-primary">back </a>




<form action="{{route('qaqc.report.postdefect')}}"  method="post">
    @csrf


    <button type="submit" class="btn btn-primary mt-3">Submit</button>
                    </form>
@endsection
