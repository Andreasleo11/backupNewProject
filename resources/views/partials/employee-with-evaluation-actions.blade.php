<!-- Button to trigger warning logs modal -->
<button type="button" class="btn btn-info view-warnings-btn" data-bs-toggle="modal"
  data-bs-target="#warningLogsModal" data-employee-id="{{ $employee->NIK }}">
  View Warnings
</button>

<!-- Button to trigger modal -->
<button type="button" class="btn btn-primary add-warnings-btn" data-bs-toggle="modal"
  data-bs-target="#warningModal" data-employee-id="{{ $employee->NIK }}">
  Add Warning
</button>
