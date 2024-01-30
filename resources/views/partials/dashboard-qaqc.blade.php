<div class="row justify-content-center">

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card shadow h-100 py-2" style="border-left: 3px solid blue;    ">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Approved</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $approvedDoc}}</div>
                    </div>
                    <div class="col-auto">
                        <box-icon name='check' color="gray" size="lg"></box-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card shadow h-100 py-2" style="border-left: 3px solid green;    ">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Waiting</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{$waitingDoc}}</div>
                    </div>
                    <div class="col-auto">
                        <box-icon name='time' color="gray" size="lg"></box-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card shadow h-100 py-2" style="border-left: 3px solid red; ">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                            Rejected</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{$rejectedDoc}}</div>
                    </div>
                    <div class="col-auto">
                        <box-icon name='x-circle' color="gray" size="lg"></box-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
