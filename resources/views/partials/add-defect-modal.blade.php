<div class="modal" tabindex="-1" class="modal fade" id="add-defect-modal" aria-labelledby="addDefectModal" aria-hidden="true">
  <div class="modal-dialog">
      <div class="modal-content">
          <div class="modal-header">
              <h5 class="modal-title">Add Defect</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <form method="POST">
                  <div class="form-group mb-3">
                      <label class="form-label">Defect type</label>
                      <div class="form-check">
                          <input class="form-check-input" type="checkbox" value="" id="checkCustomerDefect">
                          <label class="form-check-label" for="checkCustomerDefect">
                              Customer Defect
                          </label>
                      </div>
                      <div class="form-check">
                          <input class="form-check-input" type="checkbox" value="" id="checkDaijoDefect">
                          <label class="form-check-label" for="checkDaijoDefect">
                              Daijo Defect
                          </label>
                      </div>
                  </div>
                  <div class="form-group mb-3" id="customerDefectGroup" style="display: none">
                      <label class="form-label">Customer Defect</label>
                      <div class="row justify-content-center">
                          <div class="col-4">
                              <input name="quantity" class="form-control" type="number" id="quantityCustomerDefect" placeholder="Quantity">
                          </div>
                          <div class="col">
                              <select class="form-select" name="defect_category" id="customerDefectCategory">
                                  <option value="">Select category..</option>
                              </select>
                          </div>
                      </div>
                  </div>
                  <div class="form-group mb-3" id="daijoDefectGroup" style="display: none">
                      <div class="row justify-content-center">
                          <label class="form-label">Daijo Defect</label>
                          <div class="col-4">
                              <input name="quantity" class="form-control" type="number" id="quantityDaijoDefect" placeholder="Quantity">
                          </div>
                          <div class="col">
                              <select class="form-select" name="defect_category" id="daijoDefectCategory">
                                  <option value="">Select category..</option>
                              </select>
                          </div>
                      </div>
                  </div>
                  <div class="form-group mb-3">
                      <label for="remark" class="form-label">Remark</label>
                      <select class="form-select" name="defect_category" id="remark">
                          <option value="can_repair">Can repair</option>
                          <option value="cant_repair">Can't repair</option>
                          <option value="other">Other</option>
                      </select>
                      <input type="text" name="other" id="other" class="form-control mt-2" style="display: none" placeholder="Other remark">
                  </div>
              </form>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              <button type="button" class="btn btn-primary">Save changes</button>
            </div>
      </div>
  </div>
</div>