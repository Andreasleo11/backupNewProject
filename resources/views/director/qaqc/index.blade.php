@extends('layouts.app')

@section('content')
<section class="header">
    <div class="d-flex mb-1 row-flex">
        <div class="h2 me-auto">QA & QC Reports</div>
    </div>
</section>

<section>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-5 ">
            <li class="breadcrumb-item"><a href="{{route('director.home')}}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">QA & QC Reports</li>
        </ol>
    </nav>
</section>

@if ($message = Session::get('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ $message }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<section class="content">
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive p-4">
                <div class="mb-3">
                    <button id="approve-selected-btn" data-approve-url="{{ route('director.qaqc.approveSelected') }}" class="btn btn-primary">Approve Selected</button>
                    <button id="reject-selected-btn" data-reject-url="{{ route('director.qaqc.rejectSelected') }}" data-bs-toggle="modal" data-bs-target="#reject-selected-modal" class="btn btn-danger ">Reject Selected</button>
                    @include('partials.reject-selected-modal')
                </div>
                {{ $dataTable->table() }}
            </div>
        </div>
    </div>
</section>
@endsection

@push('extraJs')
    {{ $dataTable->scripts() }}

    <script>
        document.addEventListener('DOMContentLoaded', function(){
            var checkInterval = setInterval(function(){
                var thElement = document.querySelector('th.check_all');
                if(thElement){
                    clearInterval(checkInterval);

                    var input = document.createElement('input');
                    input.style.marginLeft= '10px';
                    input.setAttribute('type', 'checkbox');
                    input.setAttribute('class', 'form-check-input');
                    thElement.appendChild(input);

                    var isChecked = false;

                    thElement.addEventListener('click', function(){
                        var checkboxes = document.querySelectorAll('tbody input[type="checkbox"]');

                        checkboxes.forEach(function(checkbox){
                            checkbox.checked = !isChecked;
                        });

                        isChecked = !isChecked;
                    });
                }
            }, 100);

            document.getElementById('approve-selected-btn').addEventListener('click', function(){
                var checkboxes = document.querySelectorAll('tbody input[type="checkbox"]:checked');

                var ids = [];
                checkboxes.forEach(function(checkbox){
                    var userId = checkbox.id.replace('checkbox', '');
                    ids.push(userId);
                });

                if(ids.length > 0){
                    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    var approveRoute = document.getElementById('approve-selected-btn').getAttribute('data-approve-url');
                    console.log(approveRoute);

                    fetch(approveRoute, {
                        method: 'PUT',
                        headers: {
                            'Content-Type' : 'application/json',
                            'X-CSRF-TOKEN' : csrfToken
                        },
                        body: JSON.stringify({ ids: ids}),
                    }).then(response => {
                        if(response.ok){
                            console.log('Selected records approved successfully');
                            location.reload();
                        } else {
                            console.error('Failed to approve selected records.');
                        }
                    }).catch(error => {
                        console.error('An error occured:', error);
                    });
                } else {
                    console.warn('No records selected for approval');
                }
            });
            document.getElementById('confirmReject').addEventListener('click', function(){
                var checkboxes = document.querySelectorAll('tbody input[type="checkbox"]:checked');

                var ids = [];
                checkboxes.forEach(function(checkbox){
                    var userId = checkbox.id.replace('checkbox', '');
                    ids.push(userId);
                });

                var rejectionReason = document.getElementById('rejectionReason').value;

                if(ids.length > 0){
                    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    var rejectRoute = document.getElementById('reject-selected-btn').getAttribute('data-reject-url');
                    var rejectionReason = document.getElementById('rejectionReason').value;

                    fetch(rejectRoute, {
                        method: 'PUT',
                        headers: {
                            'Content-Type' : 'application/json',
                            'X-CSRF-TOKEN' : csrfToken
                        },
                        body: JSON.stringify({
                            ids: ids,
                            rejection_reason: rejectionReason
                        }),
                    }).then(response => {
                        if(response.ok){
                            console.log('Selected records rejected successfully');
                            location.reload();
                        } else {
                            console.error('Failed to reject selected records.');
                        }
                    }).catch(error => {
                        console.error('An error occured:', error);
                    });
                } else {
                    console.warn('No records selected for rejection');
                }
            });
        });
    </script>
@endpush
