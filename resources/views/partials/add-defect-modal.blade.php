<div class="modal" tabindex="-1" class="modal fade" id="add-defect-modal-{{ $detail->id }}" aria-labelledby="addDefectModal" aria-hidden="true">
  <div class="modal-dialog">
      <div class="modal-content">
        <form method="POST" action="{{ route('qaqc.report.postdefect') }}">
            @csrf
            <div class="modal-header">
              <h5 class="modal-title">Add Defect for <span class="fw-semibold">{{ $detail->part_name }}</span></h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="detail_id" value="{{ $detail->id }}">
                <div class="form-group mb-3">
                    <label class="form-label">Defect type</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="checkCustomerDefect{{ $detail->id }}" name="check_customer">
                        <label class="form-check-label" for="checkCustomerDefect">Customer Defect</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="checkDaijoDefect{{ $detail->id }}" name="check_daijo">
                        <label class="form-check-label" for="checkDaijoDefect">Daijo Defect</label>
                    </div>
                </div>
                <div class="form-group mb-3" id="customerDefectGroup{{ $detail->id }}" style="display: none">
                    <label class="form-label">Customer Defect</label>
                    <div class="row justify-content-center">
                        <div class="col-4">
                            <input name="quantity_customer" class="form-control" type="number" id="quantityCustomerDefect" placeholder="Quantity">
                        </div>
                        <div class="col">
                        <select class="form-select" name="customer_defect_category" id="customerDefectCategory">
                            <option value="" selected>Select category..</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        </div>
                    </div>
                </div>
                <div class="form-group mb-3" id="daijoDefectGroup{{ $detail->id }}" style="display: none">
                    <div class="row justify-content-center">
                        <label class="form-label">Daijo Defect</label>
                        <div class="col-4">
                            <input name="quantity_daijo" class="form-control" type="number" id="quantityDaijoDefect" placeholder="Quantity">
                        </div>
                        <div class="col">
                        <select class="form-select" name="daijo_defect_category" id="daijoDefectCategory
                            <option value="" selected>Select category..</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        </div>
                    </div>
                </div>
                <div class="form-group mb-3">
                    <label for="remark" class="form-label">Remark</label>
                    <select class="form-select" name="remark" id="remark{{ $detail->id }}">
                        <option value="Can Repair">Can repair</option>
                        <option value="Can't Repair">Can't repair</option>
                        <option value="other">Other</option>
                    </select>
                    <input type="text" name="other_remark" id="other_remark{{ $detail->id }}" class="form-control mt-2" style="display: none" placeholder="Other remark">
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
