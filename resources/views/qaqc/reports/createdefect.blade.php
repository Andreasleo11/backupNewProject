@extends('layouts.app')

@section('content')
@if ($message = Session::get('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <p>{{ $message }}</p>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@elseif ($errors->any())
    <div class="alert alert-danger alert-dismissable fade show" role="alert">
        <div class="d-flex">
            <div class="flex-grow-1">
                <ul>
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <div class="">
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    </div>
@endif
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-body p-0">
                    <div class="row justify-content-center">
                        <div class="col-3 p-5 border-end">
                            <h5>1. Select part detail name</h5>
                            <div class="list-group" id="list-tab" role="tablist">
                                @foreach ($details as $index => $detail)
                                    <a class="list-group-item list-group-item-action @if($index === 0) active @endif" id="list-detail{{ $detail->id }}-list" data-bs-toggle="list" href="#list-detail{{ $detail->id }}" role="tab" aria-controls="list-detail{{ $detail->id }}">{{ $detail->part_name }}</a>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-9 p-5">
                            <h5>2. Add defects for the selected detail</h5>
                            @foreach ($details as $index => $detail)
                                <div class="tab-content" id="nav-tabContent">
                                    <div class="tab-pane fade show @if($index === 0) active @endif" id="list-detail{{ $detail->id }}" role="tabpanel">
                                        <div class="mt-3 mb-3">
                                            <a href="" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#add-defect-modal-{{ $detail->id }}">+ Add Defect</a>
                                        </div>
                                        @include('partials.add-defect-modal')
                                        <div class="table-responsive">
                                            <table class="table table-striped table-bordered table-sm">
                                                <thead class="text-center">
                                                    <tr>
                                                        <th class="py-3">Customer Defect</th>
                                                        <th class="py-3">Daijo Defect</th>
                                                        <th class="py-3">Remarks</th>
                                                        <th class="py-3">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($detail->defects as $defect)
                                                    <tr class="text-center align-middle">
                                                        @if($defect->is_daijo)
                                                            <td></td>
                                                            <td>{{ $defect->quantity . " : " . $defect->category->name }}</td>
                                                            <td>{{ $detail->remarks ?? "-" }}</td>
                                                            <td><button class="btn btn-danger btn-sm"><i class='bx bx-trash-alt'></i></button></td>
                                                        @else
                                                            <td>{{ $defect->quantity . " : " . $defect->category->name}}</td>
                                                            <td></td>
                                                            <td>{{ $detail->remarks ?? "-" }}</td>
                                                            <td><button class="btn btn-danger btn-sm"><i class='bx bx-trash-alt'></i></button></td>
                                                        @endif
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-between">
                <div class="mt-3">

                    <a href="{{ route('qaqc.report.createdetail') }}" class="btn btn-secondary">back </a>
                </div>

                <form action="{{route('qaqc.report.index')}}" method="get">
                    <button type="submit" class="btn btn-success mt-3">Finish</button>
                </form>
            </div>
        </div>
    </div>
</div>


@endsection


@push('extraJs')
    <script>
       @foreach ($details as $detail)
            const checkCustomerDefect{{ $detail->id }} = document.getElementById('checkCustomerDefect{{ $detail->id }}');
            const checkDaijoDefect{{ $detail->id }} = document.getElementById('checkDaijoDefect{{ $detail->id }}');
            const customerDefectGroup{{ $detail->id }} = document.getElementById('customerDefectGroup{{ $detail->id }}');
            const daijoDefectGroup{{ $detail->id }} = document.getElementById('daijoDefectGroup{{ $detail->id }}');
            const remarkSelect{{ $detail->id }} = document.getElementById('remark{{ $detail->id }}');
            const otherInput{{ $detail->id }} = document.getElementById('other_remark{{ $detail->id }}');

            checkCustomerDefect{{ $detail->id }}.addEventListener('change', function() {
                if (this.checked) {
                    customerDefectGroup{{ $detail->id }}.style.display = 'block';
                } else {
                    customerDefectGroup{{ $detail->id }}.style.display = 'none';
                }
            });

            checkDaijoDefect{{ $detail->id }}.addEventListener('change', function() {
                if (this.checked) {
                    daijoDefectGroup{{ $detail->id }}.style.display = 'block';
                } else {
                    daijoDefectGroup{{ $detail->id }}.style.display = 'none';
                }
            });

            remarkSelect{{ $detail->id }}.addEventListener('change', function() {
                if (this.value === 'other') {
                    otherInput{{ $detail->id }}.style.display = 'block';
                } else {
                    otherInput{{ $detail->id }}.style.display = 'none';
                }
            });
        @endforeach

    </script>


@endpush
