<div class="modal" tabindex="-1" class="modal fade" id="add-new-holiday" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('holidays.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="date">Date of Holiday:</label>
                        <input type="date" id="date" name="date" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="holiday_name">Holiday Name:</label>
                        <input type="text" id="holiday_name" name="holiday_name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description:</label>
                        <input type="text" id="description" name="description" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="halfday">Halfday:</label>
                        <select id="halfday" name="halfday" class="form-control" required>
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add</button>
                </div>
            </form>
        </div>
    </div>
</div>
