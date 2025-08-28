<!-- Add Warning Modal -->
<div class="modal fade" id="warningModal" tabindex="-1" role="dialog"
  aria-labelledby="warningModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="warningModalLabel">Warning Form</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"
          aria-label="Close"></button>
      </div>
      <form method="POST" action="{{ route('director.warning-log.store') }}">
        @csrf
        <div class="modal-body">
          <div class="form-group mb-3">
            <label for="nik" class="form-label">NIK</label>
            <input type="text" class="form-control bg-secondary-subtle" id="nik" readonly
              name="NIK" value="">
          </div>
          <div class="form-group mb-3">
            <label class="form-label" for="warningType">Warning Type</label>
            <select class="form-select" id="warningType" name="warning_type" required>
              <option value="SP 1">SP 1</option>
              <option value="SP 2">SP 2</option>
              <option value="SP 3">SP 3</option>
              <option value="Terminate">Terminate</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label" for="reason">Reason</label>
            <textarea class="form-control" id="reason" name="reason" rows="3" required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-toggle="modal"
            data-bs-target="#filteredEmployeesModal">Back</button>
          <button type="submit" class="btn btn-primary">Submit</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Add warning logs scripts --}}
<script>
  document.addEventListener("DOMContentLoaded", function() {
    document.getElementById('viewFilteredEmployeesBtn').addEventListener('click', function() {
      document.querySelectorAll(".add-warnings-btn").forEach(button => {
        button.addEventListener("click", function() {
          let employeeId = this.getAttribute("data-employee-id");
          document.getElementById('nik').value = employeeId;
        });
      });
    });
  });
</script>
