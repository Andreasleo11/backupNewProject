<!-- Warning Logs Modal -->
<div class="modal fade" id="warningLogsModal" tabindex="-1" role="dialog"
  aria-labelledby="warningLogsModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="warningLogsModalLabel">Warning Logs</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"
          aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="warningLogsContent">
          <p>Loading...</p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
          data-bs-target="#filteredEmployeesModal">Back</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

{{-- Warning logs scripts --}}
<script>
  document.addEventListener("DOMContentLoaded", function() {
    let warningLogsContent = document.getElementById("warningLogsContent");

    document.getElementById('viewFilteredEmployeesBtn').addEventListener('click', function() {
      // Event listener for buttons
      document.querySelectorAll(".view-warnings-btn").forEach(button => {
        button.addEventListener("click", function() {
          let employeeId = this.getAttribute("data-employee-id");
          // Show loading state
          warningLogsContent.innerHTML = "<p>Loading...</p>";

          // Fetch warning logs via AJAX
          fetch(`/employees/${employeeId}/warnings`)
            .then(response => response.json())
            .then(data => {
              if (data.length === 0) {
                warningLogsContent.innerHTML =
                  "<p>No warnings found for this employee.</p>";
              } else {
                let tableHtml = `
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Warning Type</th>
                                        <th>Reason</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                        `;

                data.forEach(log => {
                  tableHtml += `
                                <tr>
                                    <td>${log.warning_type}</td>
                                    <td>${log.reason}</td>
                                    <td>${new Date(log.created_at).toLocaleDateString()}</td>
                                </tr>
                            `;
                });

                tableHtml += "</tbody></table>";
                warningLogsContent.innerHTML = tableHtml;
              }
            })
            .catch(error => {
              warningLogsContent.innerHTML =
                "<p>Error loading warning logs.</p>";
              console.error("Error fetching warning logs:", error);
            });
        });
      });
    });
  });
</script>
