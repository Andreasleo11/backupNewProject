<?php

namespace App\Http\Controllers;

use App\Models\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PreviewUploadController extends Controller
{
    public function __invoke(Upload $upload)
    {
        abort_unless(Storage::disk($upload->disk)->exists($upload->path), 494);
        return Storage::disk($upload->disk)->response($upload->path, $upload->original_name);
    }
}
