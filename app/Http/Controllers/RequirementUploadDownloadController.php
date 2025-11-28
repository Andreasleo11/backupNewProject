<?php

namespace App\Http\Controllers;

use App\Models\RequirementUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class RequirementUploadDownloadController extends Controller
{
    public function show(RequirementUpload $upload)
    {
        // if(Gate::denies('view-upload', $upload)) {
        //     abort(403);
        // }
        return Storage::disk('public')->download($upload->path, $upload->original_name);
    }
}
