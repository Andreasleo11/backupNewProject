<?php

namespace App\Http\Controllers;

use App\Models\ImportJob;
use Illuminate\Support\Facades\Storage;

class ImportJobController extends Controller
{
    public function downloadLog(ImportJob $job)
    {
        abort_unless($job->error_log_path, 404);
        abort_unless(Storage::disk('local')->exists($job->error_log_path), 404);

        return Storage::disk('local')->download($job->error_log_path, "import-job-{$job->id}.csv");
    }
}
