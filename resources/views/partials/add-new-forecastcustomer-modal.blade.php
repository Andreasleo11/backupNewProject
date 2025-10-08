<div class="modal" tabindex="-1" class="modal fade" id="add-new-forecastcustomer" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('addnewforecastmaster') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Forecast Master Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group mt-4">
                        <div class="row">
                            <div class="col-sm-3 col-form-label">
                                <label for="forecast_code" class="form-label">Forecast Code : </label>
                            </div>
                            <div class="col-sm-9">
                                <input type="text" name="forecast_code" class="form-control" id="forecast_code">
                            </div>
                        </div>
                    </div>
                    <div class="form-group mt-4">
                        <div class="row">
                            <div class="col-sm-3 col-form-label">
                                <label for="forecast_name" class="form-label">Forecast Name:</label>
                            </div>
                            <div class="col-sm-9">
                                <input type="text" name="forecast_name" class="form-control" id="forecast_name">
                            </div>
                        </div>
                    </div>
                    <div class="form-group mt-4">
                        <div class="row">
                            <div class="col-sm-3 col-form-label">
                                <label for="customer" class="form-label">Customer :</label>
                            </div>
                            <div class="col-sm-9">
                                <input type="text" name="customer" class="form-control" id="customer">
                            </div>
                        </div>
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
