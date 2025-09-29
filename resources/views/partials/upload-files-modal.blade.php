<div class="modal fade" id="upload-files-modal" tabindex="-1" role="dialog" aria-labelledby="uploadFileModal"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('file.upload') }}" method="post" enctype="multipart/form-data" id="form-upload">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadFileModal"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="container text-center py-5 rounded-2">
                        <!-- File Upload Form -->
                        <h3 class="mb-2">Upload Files</h3>
                        <div class="mb-3">
                            <span>Upload files for this report</span> <br>
                            <span>PDF, Images, Excel are are allowed</span>
                        </div>
                        <div class="mb-3">
                            <input type="hidden" name="doc_num" value="{{ $doc_id }}">
                            <button type="button" class="btn btn-outline-primary browse">Browse files</button>
                            <input type="file" name="files[]" class="form-control d-none input-files" multiple>
                        </div>
                    </div>
            </form>
        </div>
    </div>
</div>
</div>

<script>
    const browseButton = document.querySelector('.browse');
    const inputFiles = document.querySelector('.input-files')

    browseButton.onclick = () => inputFiles.click()
    inputFiles.addEventListener('change', function() {
        document.getElementById('form-upload').submit();
    })
</script>
