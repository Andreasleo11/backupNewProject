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
            <div class="col d-flex col-xl-3 col-md-4">
                <a href="{{ asset('storage/files/' . $filename) }}" download="{{ $filename }}">
                    <div class="card m-4 ms-0 ">
                        <div class="card-body btn btn-light" style="max-width: 250px">
                            <div class="text-end">
                                @if (Auth::user()->name == $report->autograph_name_1)
                                    <a class="btn btn-outline-danger"
                                        onclick="document.getElementById('deleteForm{{ $file->id }}').submit();">
                                        <i class='bx bxs-trash-alt bx-xs bx-tada-hover'></i>
                                        <form id="deleteForm{{ $file->id }}"
                                            action="{{ route('file.delete', $file->id) }}" method="post">
                                            @csrf @method('DELETE')</form>
                                    </a>
                                @endif
                            </div>
                            <div class="col text-center">
                                <div class="col d-flex align-items-center p-0" style="min-height:100px">
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
                                    <span class="text-secondary text-start fw-semibold">
                                        {{ $filenameWithoutExtension }}
                                    </span>
                                </div>
                                <div class="mb-2 text-secondary">
                                    {{ formatFileSize($file->size) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @empty
            <p>No Files Were Uploaded</p>
        @endforelse
    </div>
</div>
