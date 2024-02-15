<div class="modal" tabindex="-1" class="modal fade" id="add-important-doc-modal" aria-labelledby="addImportantDocModal" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Important Doc</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <form action="{{ route('hrd.importantDocs.store') }}" method="POST" enctype="multipart/form-data">

                            @csrf

                            <div class="form-group">
                                <label class="font-weight-bold">Name</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" placeholder="Insert name of the document">

                                <!-- error message untuk title -->
                                @error('name')
                                    <div class="alert alert-danger mt-2">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="form-group mt-3">
                                <label class="font-weight-bold">Type</label>

                                <input type="number" class="form-control @error('type') is-invalid @enderror" name="type" value="{{ old('type') }}" placeholder="Insert document type">

                                <!-- error message untuk type -->
                                @error('type')
                                    <div class="alert alert-danger mt-2">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="form-group mt-3">
                                <label class="font-weight-bold">Expired Date</label>
                                <input type="date" name="expired_date" value="{{ old('expired_date') }}" >

                                <!-- error message untuk expired_date -->
                                @error('expired_date')
                                    <div class="alert alert-danger mt-2">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <hr class="mt-4"/>

                            <div class="mt-2 d-flex flex-row-reverse">
                                <button type="button" data-bs-dismiss="modal" class="btn btn-secondary">Close</button>
                                <button type="submit" class="btn btn-primary me-2">Simpan</button>
                            </div>
                        </form>
              </div>
        </div>
    </div>
</div>
