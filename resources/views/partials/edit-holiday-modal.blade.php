<div class="modal fade" tabindex="-1" id="edit-holiday-modal-{{ $item->id }}" aria-labelledby="editDepartmentModal"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('holiday.update', $item->id) }}">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Holiday</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group mt-4 row">
                        <div class="col-sm-3 col-form-label">
                            <label for="inputDate" class="form-label">Date</label>
                        </div>
                        <div class="col-sm-9">
                            <input type="date" name="date" class="form-control" id="inputDate"
                                value="{{ $item->date }}">
                        </div>
                    </div>
                    <div class="form-group mt-4 row">
                        <div class="col-sm-3 col-form-label">
                            <label for="inputName" class="form-label">Name</label>
                        </div>
                        <div class="col-sm-9">
                            <input type="text" name="name" class="form-control" id="inputName"
                                value="{{ $item->holiday_name }}">
                        </div>
                    </div>
                    <div class="form-group mt-4 row">
                        <div class="col-sm-3 col-form-label">
                            <label for="inputDescription" class="form-label">Description</label>
                        </div>
                        <div class="col-sm-9">
                            <input type="text" name="description" class="form-control" id="inputDescription"
                                value="{{ $item->description }}">
                        </div>
                    </div>
                    <div class="form-group mt-4 row">
                        <div class="col-sm-3 col-form-label">
                            <label for="inputHalfDay" class="form-label">Is Half Day?</label>
                        </div>
                        <div class="col-sm-9 text-start">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="half_day" id="half_day_yes"
                                    value="1" @if ($item->half_day) checked @endif>
                                <label class="form-check-label" for="half_day_yes">Yes</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="half_day" id="half_day_no"
                                    value="0" @if (!$item->half_day) checked @endif>
                                <label class="form-check-label" for="half_day_no">No</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
