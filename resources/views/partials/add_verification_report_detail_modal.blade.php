<div class="modal fade modal-lg" tabindex="-1" id="addVerificationReportDetailModal"
  aria-labelledby="addVerificationReportDetailModal" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add Verification Report Detail Form</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form method="POST" action="#">
          <div class="mb-3">
            <label for="inputPartName" class="form-label">Part name</label>
            <input type="text" class="form-control" id="inputPartName">
          </div>
          <div class="mb-3">
            <label for="inputRecQuantity" class="form-label">Rec Quantity</label>
            <input type="number" class="form-control" id="inputRecQuantity">
          </div>
          <div class="mb-3">
            <label for="inputVerifyQuantity" class="form-label">Verify Quantity</label>
            <input type="number" class="form-control" id="inputVerifyQuantity">
          </div>
          <div class="mb-3">
            <label for="inputProductionDate" class="form-label">Production Date</label>
            <input type="date" class="form-control" id="inputProductionDate">
          </div>
          <div class="mb-3">
            <label for="inputShift" class="form-label">Shift</label>
            <input type="number" class="form-control" id="inputVerifyQuantity">
          </div>
          <div class="mb-3">
            <label for="inputCanUse" class="form-label">Can Use</label>
            <input type="number" class="form-control" id="inputCanUse">
          </div>
          <div class="mb-3">
            <label for="inputCantUse" class="form-label">Can't Use</label>
            <input type="number" class="form-control" id="inputVerifyQuantity">
          </div>
          <span id="defectInputs">
            <div class="row">
              <div class="col-5">
                <div class="mb-3">
                  <label for="inputCustomerDefectDetail" class="form-label">Customer Defect
                    Detail</label>
                  <input type="text" name="customer_defect_detail[]" class="form-control"
                    id="inputCustomerDefectDetail">
                </div>
              </div>
              <div class="col-4">
                <div class="mb-3">
                  <label for="inputDaijoDefectDetail" class="form-label">Daijo Defect Detail</label>
                  <input type="text" name="daijo_defect_detail[]" class="form-control"
                    id="inputDaijoDefectDetail">
                </div>
              </div>
              <div class="col-2">
                <div class="mb-3">
                  <label for="inputRemark" class="form-label">Remark</label>
                  <select name="remark" id="remarkSelect" class="form-control">
                    <option value="bisaRepair">Bisa Repair</option>
                    <option value="tidakBisaRepair">Tidak Bisa Repair</option>
                    <option value="other">Other</option>
                  </select>
                  <input type="text" name="other" class="form-control mt-2" id="otherInput"
                    style="display: none">
                </div>
              </div>

              <script></script>
              <div class="col-1">
                <label for=""></label>
                <a href="#" class="btn btn-sm">
                  <box-icon name='x-circle' size="sm"></box-icon>
                </a>
              </div>
            </div>
          </span>
          <button type="button" id="addRowBtn" class="btn btn-primary">+ Add</button>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Save changes</button>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    addRow();
  });

  document.getElementById('addRowBtn').addEventListener('click', addRow);

  function addRow() {
    const newRow = document.createElement('div');
    newRow.classList.add('row');
    newRow.innerHTML = `
            <div class="col-5">
                <div class="mb-3">
                    <label for="inputCustomerDefectDetail" class="form-label">Customer Defect Detail</label>
                    <input type="text" name="customer_defect_detail[]" class="form-control" id="inputCustomerDefectDetail">
                </div>
            </div>
            <div class="col-4">
                <div class="mb-3">
                    <label for="inputDaijoDefectDetail" class="form-label">Daijo Defect Detail</label>
                    <input type="text" name="daijo_defect_detail[]" class="form-control" id="inputDaijoDefectDetail">
                </div>
            </div>
            <div class="col-2">
                <div class="mb-3">
                    <label for="inputRemark" class="form-label">Remark</label>
                    <input type="text" name="remark[]" class="form-control" id="inputRemark">
                </div>
            </div>
            <div class="col-1">
                <label for=""></label>
                <a href="#" class="btn btn-sm pt-2">
                    <box-icon name='x-circle' size="sm"></box-icon>
                </a>
            </div>
        `;
    document.getElementById('defectInputs').appendChild(newRow);
  }

  const otherInput = document.getElementById('otherInput');
  const remarkSelect = document.getElementById('remarkSelect');

  remarkSelect.addEventListener('change', function() {
    console.log(remarkSelect.value);
    if (remarkSelect.value === "other") {
      otherInput.style.display = 'block'
    } else {
      otherInput.style.display = 'none'
    }
  });
</script>
