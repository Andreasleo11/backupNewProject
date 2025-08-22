<?php

namespace App\Http\Controllers;

use App\Models\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DownloadUploadController extends Controller
{
    public function __invoke(Upload $upload)
    {
        abort_unless(Storage::disk($upload->disk)->exists($upload->path), 404);
        return Storage::disk($upload->disk)->download($upload->path, $upload->original_name);
    }
}
