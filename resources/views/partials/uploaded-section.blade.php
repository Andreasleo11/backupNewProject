<div class="container mt-5">
    <h4>Files</h4>
    <div class="row">
        @forelse ($files as $file)
            @if (!function_exists('formatFileSize'))
                @php
                    function formatFileSize($bytes)
                    {
                        if ($bytes < 1024) {
                            return $bytes . ' bytes';
                        } elseif ($bytes < 1024 * 1024) {
                            return number_format($bytes / 1024, 2) . ' KB';
                        } elseif ($bytes < 1024 * 1024 * 1024) {
                            return number_format($bytes / (1024 * 1024), 2) . ' MB';
                        } else {
                            return number_format($bytes / (1024 * 1024 * 1024), 2) . ' GB';
                        }
                    }
                @endphp
            @endif
            @php
                $filename = basename($file->name);
                $filenameWithoutExtension = pathinfo($filename, PATHINFO_FILENAME);
                $extension = pathinfo($filename, PATHINFO_EXTENSION);
            @endphp
            <div class="col d-flex col-xl-3 col-md-4 my-2">
                <a href="{{ asset('storage/files/' . $filename) }}" download="{{ $filename }}">
                    <div class="card">
                        <div class="card-body btn btn-light" style="max-width: 250px">
                            <div class="col d-flex align-items-center p-0 text-center" style="min-height:100px">
                                @if ($extension == 'pdf')
                                    <img src="{{ asset('image/ic-pdf.png') }}" alt="ext-logo" width="50px"
                                        class="me-2">
                                @elseif(in_array($extension, ['xls', 'xlsx', 'csv']))
                                    <img src="{{ asset('image/ic-xls.png') }}" alt="ext-logo" width="50px"
                                        class="me-2">
                                @elseif(in_array($extension, ['png', 'jpeg', 'jpg']))
                                    <img src="{{ asset('image/ic-image.png') }}" alt="ext-logo" width="50px"
                                        class="me-2">
                                @elseif(in_array($extension, ['docx', 'doc']))
                                    <img src="{{ asset('image/ic-doc.png') }}" alt="ext-logo" width="50px"
                                        class="me-2">
                                @endif
                                <div class="text-secondary text-start fw-semibold"
                                    style="overflow: hidden; text-overflow: ellipsis; max-height: 4.5em; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical;">
                                    {{ $filenameWithoutExtension }}
                                </div>
                            </div>
                            <div class="mb-2 text-secondary">
                                {{ formatFileSize($file->size) }}
                            </div>
                        </div>
                    </div>
                </a>
                @if ($showDeleteButton)
                    <div class="col d-flex">
                        <!-- Button to trigger the modal -->
                        <a class="btn btn-outline-danger d-flex align-items-center"
                            data-bs-toggle="modal" data-bs-target="#confirmDeleteModal{{ $file->id }}">
                            <i class='bx bxs-trash-alt bx-xs bx-tada-hover'></i>
                        </a>

                        <!-- Modal for delete confirmation -->
                        <div class="modal fade" id="confirmDeleteModal{{ $file->id }}" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        Are you sure you want to delete this file?
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                                        <button type="button" class="btn btn-danger" onclick="document.getElementById('deleteForm{{ $file->id }}').submit();">Yes, Delete</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Delete form (hidden) -->
                        <form id="deleteForm{{ $file->id }}" action="{{ route('file.delete', $file->id) }}" method="post" style="display: none;">
                            @csrf
                            @method('DELETE')
                        </form>
                    </div>
                @endif

            </div>
        @empty
            <p>No Files Were Uploaded</p>
        @endforelse
    </div>
</div>
