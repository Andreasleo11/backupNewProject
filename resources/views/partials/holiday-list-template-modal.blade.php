<div class="modal fade" id="holiday-list-template">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="home-tab" data-bs-toggle="tab"
                            data-bs-target="#home-tab-pane" type="button" role="tab" aria-controls="home-tab-pane"
                            aria-selected="true">Download</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="profile-tab" data-bs-toggle="tab"
                            data-bs-target="#profile-tab-pane" type="button" role="tab"
                            aria-controls="profile-tab-pane" aria-selected="false">Upload</button>
                    </li>
                </ul>
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade show active" id="home-tab-pane" role="tabpanel" aria-labelledby="home-tab"
                        tabindex="0">
                        <div class="p-3 d-flex justify-content-center">
                            <form action="{{ route('download.holiday.template') }}" method="GET">
                                <button type="submit" class="btn btn-outline-primary mt-3">Download Template</button>
                            </form>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="profile-tab-pane" role="tabpanel" aria-labelledby="profile-tab"
                        tabindex="0">
                        <div class="d-flex justify-content-center p-3">
                            <form action="{{ route('upload.holiday.template') }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                <input type="file" name="holiday_file" class="form-control my-3">
                                <button type="submit" class="btn btn-outline-primary ">Upload Filled Template</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
