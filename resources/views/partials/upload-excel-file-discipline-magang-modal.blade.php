<div class="modal fade" id="upload-excel-file-discipline-magang-modal">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="{{ route('magang.import') }}" enctype="multipart/form-data"
        class="needs-validation" novalidate>
        @csrf
        <div class="modal-header">
          <h4 class="modal-title">Upload File</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal"
            aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p>Upload File Excel yang sudah diisi dengan point point kedisiplinan disini
            dalam
            bentuk
            EXCEL (.xlsx):</p>
          <input type="file" name="excel_files[]" id="excel_files"
            onchange="displayUploadedFiles()" multiple class="form-control" required>
          <div class="invalid-feedback">
            Please attach at least 1 file
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary" require>Upload</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  // Example starter JavaScript for disabling form submissions if there are invalid fields
  (() => {
    'use strict'

    // Fetch all the forms we want to apply custom Bootstrap validation styles to
    const forms = document.querySelectorAll('.needs-validation')

    // Loop over them and prevent submission
    Array.from(forms).forEach(form => {
      form.addEventListener('submit', event => {
        if (!form.checkValidity()) {
          event.preventDefault()
          event.stopPropagation()
        }

        form.classList.add('was-validated')
      }, false)
    })
  })()
</script>
